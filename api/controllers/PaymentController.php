<?php
// api/controllers/PaymentController.php
// Handles M-Pesa STK Push initiation and Safaricom's inbound callback.
// Anonymous (guest) giving supported — user_id is nullable.
//
// Token caching: tokens are written to tmp/mpesa_token.json and reused
// until they have <5 minutes left on their 60-minute lifespan.

declare(strict_types=1);

class PaymentController {

    // POST /api/payments/initiate
    // Public — no auth required. user_id set if session active.
    public function initiate(): void {
        requireCsrf();
        $body = getRequestBody();
        requireFields($body, ['phone', 'amount', 'purpose']);

        $phone      = preg_replace('/\D/', '', $body['phone']);
        $amount     = (int) round((float) $body['amount']);
        $purpose    = $body['purpose'];
        $donorName  = trim($body['donor_name'] ?? '');

        if (mb_strlen($donorName) > 150) {
            $donorName = mb_substr($donorName, 0, 150);
        }

        $validPurposes = ['tithe', 'offering', 'mission_support', 'other'];
        if (!in_array($purpose, $validPurposes, true)) {
            jsonError('Invalid purpose. Choose: tithe, offering, mission_support, other.', 422);
        }
        if ($amount < 1) {
            jsonError('Amount must be at least KES 1.', 422);
        }
        if (!preg_match('/^254\d{9}$/', $phone)) {
            jsonError('Phone must be in format 254XXXXXXXXX (12 digits, starts with 254).', 422);
        }

        // Resolve optional user_id from active session
        $userId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        // Get M-Pesa access token (cached, refreshed automatically)
        $token = $this->getAccessToken();
        if (!$token) {
            jsonError('Could not connect to M-Pesa. Please try again.', 502);
        }

        $shortcode   = env('MPESA_SHORTCODE');
        $passkey     = env('MPESA_PASSKEY');
        $callbackUrl = env('MPESA_CALLBACK_URL');
        $timestamp   = date('YmdHis');
        $password    = base64_encode($shortcode . $passkey . $timestamp);

        if ($shortcode === '' || $passkey === '' || $callbackUrl === '') {
            error_log('[TUMSDA API] M-Pesa config missing: shortcode/passkey/callback URL empty.');
            jsonError('Payment service is not configured correctly. Please contact the admin.', 500);
        }

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $amount,
            'PartyA'            => $phone,
            'PartyB'            => $shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => 'TUMSDA',
            'TransactionDesc'   => ucfirst(str_replace('_', ' ', $purpose)),
        ];

        $env     = env('MPESA_ENV', 'sandbox');
        $baseUrl = $env === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        $response = $this->curlPost(
            "$baseUrl/mpesa/stkpush/v1/processrequest",
            $payload,
            $token
        );

        if (empty($response['CheckoutRequestID'])) {
            error_log('[TUMSDA API] STK Push failed: ' . json_encode($response));
            jsonError('STK Push request failed: ' . ($response['errorMessage'] ?? 'unknown error'), 502);
        }

        // Persist pending payment row
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO payments
             (user_id, phone_number, amount, purpose, status,
              checkout_request_id, merchant_request_id, donor_name)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $phone,
            $amount,
            $purpose,
            'pending',
            $response['CheckoutRequestID'],
            $response['MerchantRequestID'] ?? null,
            $donorName !== '' ? $donorName : null,
        ]);

        jsonResponse([
            'message'             => 'Check your phone for the M-Pesa payment prompt.',
            'checkout_request_id' => $response['CheckoutRequestID'],
        ]);
    }

    // POST /api/payments/callback  — also routed from /api/mpesa/callback
    // Called by Safaricom — NOT a user session request.
    public function callback(): void {
        $raw  = file_get_contents('php://input');

        // ── Log raw payload for debugging during sandbox testing ─────────
        $logDir  = dirname(__DIR__, 2) . '/tmp';
        $logFile = $logDir . '/mpesa_callbacks.log';
        if (is_writable($logDir)) {
            $logEntry = '[' . date('Y-m-d H:i:s') . '] ' . $raw . PHP_EOL;
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }
        error_log('[TUMSDA MPESA CALLBACK] ' . $raw);
        // ─────────────────────────────────────────────────────────────────

        $data = json_decode($raw, true);

        $stkCallback = $data['Body']['stkCallback'] ?? null;
        if (!$stkCallback) {
            http_response_code(200);
            exit; // Always return 200 to Safaricom to acknowledge receipt
        }

        $checkoutId = $stkCallback['CheckoutRequestID'] ?? '';
        $resultCode = (int)($stkCallback['ResultCode'] ?? -1);

        $db   = getDB();
        $stmt = $db->prepare('SELECT id, status FROM payments WHERE checkout_request_id = ? LIMIT 1');
        $stmt->execute([$checkoutId]);
        $payment = $stmt->fetch();

        // Reject callbacks for unknown or already-processed transactions
        if (!$payment || $payment['status'] !== 'pending') {
            http_response_code(200);
            exit;
        }

        $status  = ($resultCode === 0) ? 'completed' : ($resultCode === 1032 ? 'cancelled' : 'failed');
        $receipt = null;

        if ($resultCode === 0) {
            $items = $stkCallback['CallbackMetadata']['Item'] ?? [];
            foreach ($items as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $receipt = $item['Value'] ?? null;
                }
            }
        }

        $update = $db->prepare(
            'UPDATE payments
             SET status = ?, mpesa_receipt_number = ?, raw_callback_payload = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $update->execute([$status, $receipt, $raw, $payment['id']]);

        http_response_code(200);
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        exit;
    }

    // GET /api/payments — admin only: giving history
    public function list(): void {
        requireAdmin();
        $db   = getDB();
        $rows = $db->query(
            'SELECT p.id, p.phone_number, p.amount, p.purpose, p.status,
                    p.mpesa_receipt_number, p.created_at,
                    COALESCE(p.donor_name, u.name) AS donor_name,
                    u.email AS donor_email
             FROM payments p
             LEFT JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC
             LIMIT 500'
        )->fetchAll();
        jsonResponse($rows);
    }

    // ── Private Helpers ───────────────────────────────────────────────────

    /**
     * Returns a valid M-Pesa OAuth access token.
     *
     * Tokens are cached in tmp/mpesa_token.json and reused until there are
     * fewer than 5 minutes left on the 60-minute lifespan — prevents
     * hitting the token endpoint on every STK Push request.
     */
    private function getAccessToken(): ?string {
        $key    = env('MPESA_CONSUMER_KEY');
        $secret = env('MPESA_CONSUMER_SECRET');
        $env    = env('MPESA_ENV', 'sandbox');
        $base   = $env === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        if ($key === '' || $secret === '') {
            error_log('[TUMSDA API] M-Pesa consumer key/secret not set.');
            return null;
        }

        // ── Try cache first ──────────────────────────────────────────────
        $cacheFile = dirname(__DIR__, 2) . '/tmp/mpesa_token.json';
        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            // Reuse if more than 5 minutes remain on the token (3600 - 300 = 3300s)
            if (
                !empty($cached['access_token']) &&
                !empty($cached['fetched_at']) &&
                (time() - $cached['fetched_at']) < 3300
            ) {
                return $cached['access_token'];
            }
        }

        // ── Fetch fresh token ────────────────────────────────────────────
        $ch = curl_init("$base/oauth/v1/generate?grant_type=client_credentials");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "$key:$secret",
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => ($env === 'production'),
        ]);
        $raw      = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('[TUMSDA API] M-Pesa token cURL error: ' . $curlErr);
            return null;
        }

        $response = json_decode($raw, true);
        $token    = $response['access_token'] ?? null;

        if (!$token) {
            error_log('[TUMSDA API] M-Pesa token response: ' . $raw);
            return null;
        }

        // ── Cache to file ────────────────────────────────────────────────
        $cacheDir = dirname($cacheFile);
        if (is_writable($cacheDir)) {
            file_put_contents(
                $cacheFile,
                json_encode(['access_token' => $token, 'fetched_at' => time()]),
                LOCK_EX
            );
        }

        return $token;
    }

    private function curlPost(string $url, array $payload, string $token): array {
        $env = env('MPESA_ENV', 'sandbox');
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => ($env === 'production'),
        ]);
        $raw     = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('[TUMSDA API] STK Push cURL error: ' . $curlErr);
            return [];
        }

        return json_decode($raw, true) ?? [];
    }
}
