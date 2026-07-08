<?php
// api/controllers/PaymentController.php
// Handles M-Pesa STK Push initiation and Safaricom's inbound callback.
// Anonymous (guest) giving supported — user_id is nullable.

declare(strict_types=1);

class PaymentController {

    // POST /api/payments/initiate
    // Public — no auth required. user_id set if session active.
    public function initiate(): void {
        requireCsrf();
        $body = getRequestBody();
        requireFields($body, ['phone', 'amount', 'purpose']);

        $phone   = preg_replace('/\D/', '', $body['phone']);
        $amount  = (int) round((float) $body['amount']);
        $purpose = $body['purpose'];

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

        // Get M-Pesa access token
        $token = $this->getAccessToken();
        if (!$token) {
            jsonError('Could not connect to M-Pesa. Please try again.', 502);
        }

        $shortcode   = $_ENV['MPESA_SHORTCODE']    ?? '';
        $passkey     = $_ENV['MPESA_PASSKEY']       ?? '';
        $callbackUrl = $_ENV['MPESA_CALLBACK_URL']  ?? '';
        $timestamp   = date('YmdHis');
        $password    = base64_encode($shortcode . $passkey . $timestamp);

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

        $env     = $_ENV['MPESA_ENV'] ?? 'sandbox';
        $baseUrl = $env === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        $response = $this->curlPost(
            "$baseUrl/mpesa/stkpush/v1/processrequest",
            $payload,
            $token
        );

        if (empty($response['CheckoutRequestID'])) {
            jsonError('STK Push request failed: ' . ($response['errorMessage'] ?? 'unknown error'), 502);
        }

        // Persist pending payment row
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO payments
             (user_id, phone_number, amount, purpose, status, checkout_request_id, merchant_request_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $phone,
            $amount,
            $purpose,
            'pending',
            $response['CheckoutRequestID'],
            $response['MerchantRequestID'] ?? null,
        ]);

        jsonResponse([
            'message'            => 'Check your phone for the M-Pesa payment prompt.',
            'checkout_request_id' => $response['CheckoutRequestID'],
        ]);
    }

    // POST /api/payments/callback
    // Called by Safaricom — NOT a user session request.
    public function callback(): void {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $stkCallback = $data['Body']['stkCallback'] ?? null;
        if (!$stkCallback) {
            http_response_code(200);
            exit; // Always return 200 to Safaricom to acknowledge receipt
        }

        $checkoutId  = $stkCallback['CheckoutRequestID'] ?? '';
        $resultCode  = (int)($stkCallback['ResultCode'] ?? -1);

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
                    u.name AS donor_name, u.email AS donor_email
             FROM payments p
             LEFT JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC
             LIMIT 500'
        )->fetchAll();
        jsonResponse($rows);
    }

    // ── Private Helpers ───────────────────────────────────────────────────
    private function getAccessToken(): ?string {
        $key    = $_ENV['MPESA_CONSUMER_KEY']    ?? '';
        $secret = $_ENV['MPESA_CONSUMER_SECRET'] ?? '';
        $env    = $_ENV['MPESA_ENV']             ?? 'sandbox';
        $base   = $env === 'production' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';

        $ch = curl_init("$base/oauth/v1/generate?grant_type=client_credentials");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "$key:$secret",
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $response['access_token'] ?? null;
    }

    private function curlPost(string $url, array $payload, string $token): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true) ?? [];
    }
}
