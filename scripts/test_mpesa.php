<?php
// scripts/test_mpesa.php
// ────────────────────────────────────────────────────────────────────────────
// Quick standalone smoke-test for the M-Pesa Daraja sandbox.
//
// HOW TO RUN (in a terminal at the project root):
//   php scripts/test_mpesa.php
//
// You can also hit it over HTTP from a browser or curl:
//   http://localhost/tum/tumsdachurch.org/scripts/test_mpesa.php
//
// REMOVE or password-protect this file before going to production!
// ────────────────────────────────────────────────────────────────────────────

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

// Load env (the .env lives one level above /scripts)
$envPath = dirname(__DIR__) . '/.env';
if (!file_exists($envPath)) {
    die("[ERROR] .env file not found at: $envPath\n");
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $k = trim($k); $v = trim($v);
    if (!isset($_ENV[$k])) { $_ENV[$k] = $v; putenv("$k=$v"); }
}

function env(string $key, string $default = ''): string {
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($v !== false && $v !== null && $v !== '') ? $v : $default;
}

// ── Security Guard for Production ─────────────────────────────────────────
if (env('MPESA_ENV', 'sandbox') === 'production') {
    http_response_code(403);
    die("[FORBIDDEN] This testing script is disabled in production for security reasons.\n");
}


// ── Config ────────────────────────────────────────────────────────────────
$mpesaEnv  = env('MPESA_ENV', 'sandbox');
$key       = env('MPESA_CONSUMER_KEY');
$secret    = env('MPESA_CONSUMER_SECRET');
$shortcode = env('MPESA_SHORTCODE');
$passkey   = env('MPESA_PASSKEY');
$callback  = env('MPESA_CALLBACK_URL');
$base = $mpesaEnv === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke';

// Parse phone number from CLI arguments: e.g. php scripts/test_mpesa.php 0797844540
$rawPhone = $argv[1] ?? '254708374149';
$testPhone = preg_replace('/\D/', '', $rawPhone);
if (str_starts_with($testPhone, '0')) {
    $testPhone = '254' . substr($testPhone, 1);
}
if ($testPhone === '') {
    $testPhone = '254708374149';
}


echo "==========================================================\n";
echo " TUMSDA – M-Pesa Daraja Sandbox Smoke Test\n";
echo "==========================================================\n";
echo "  Environment : $mpesaEnv\n";
echo "  Shortcode   : $shortcode\n";
echo "  Consumer Key: " . ($key !== '' ? substr($key, 0, 8) . '...' : '[MISSING]') . "\n";
echo "  Passkey     : " . ($passkey !== '' ? substr($passkey, 0, 8) . '...' : '[MISSING]') . "\n";
echo "  Callback URL: " . ($callback !== '' ? $callback : '[MISSING]') . "\n";
echo "----------------------------------------------------------\n\n";

// ── STEP 1: OAuth Token ───────────────────────────────────────────────────
echo "[1] Fetching OAuth access token...\n";

if ($key === '' || $secret === '') {
    die("[ERROR] MPESA_CONSUMER_KEY or MPESA_CONSUMER_SECRET is empty in .env!\n");
}

$ch = curl_init("$base/oauth/v1/generate?grant_type=client_credentials");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD        => "$key:$secret",
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => ($mpesaEnv === 'production'),
]);
$raw     = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    die("[ERROR] cURL failed: $curlErr\n");
}

$tokenData = json_decode($raw, true);
$token     = $tokenData['access_token'] ?? null;

if (!$token) {
    echo "[ERROR] Token fetch failed. Response:\n";
    echo json_encode($tokenData, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

echo "  ✔ Token obtained: " . substr($token, 0, 20) . "...\n";

// ── Cache it ─────────────────────────────────────────────────────────────
$cacheFile = dirname(__DIR__) . '/tmp/mpesa_token.json';
$cacheDir  = dirname($cacheFile);
if (is_writable($cacheDir)) {
    file_put_contents($cacheFile, json_encode([
        'access_token' => $token,
        'fetched_at'   => time(),
    ]), LOCK_EX);
    echo "  ✔ Token cached to tmp/mpesa_token.json\n";
} else {
    echo "  ⚠ tmp/ not writable — token not cached (will refetch each request)\n";
}

echo "\n";

// ── STEP 2: Password & Timestamp ─────────────────────────────────────────
echo "[2] Generating STK Push password...\n";

if ($shortcode === '' || $passkey === '') {
    die("[ERROR] MPESA_SHORTCODE or MPESA_PASSKEY is empty in .env!\n");
}

$timestamp = date('YmdHis');
$password  = base64_encode($shortcode . $passkey . $timestamp);

echo "  Timestamp : $timestamp\n";
echo "  Password  : " . substr($password, 0, 30) . "...\n\n";

// ── STEP 3: STK Push ─────────────────────────────────────────────────────
echo "[3] Sending STK Push to $testPhone...\n";

if ($callback === '' || strpos($callback, '<your-ngrok') !== false) {
    echo "  ⚠ MPESA_CALLBACK_URL is still a placeholder!\n";
    echo "    Start ngrok and update MPESA_CALLBACK_URL in .env before testing.\n";
    echo "    Continuing anyway — sandbox may still accept the request...\n\n";
}

$payload = [
    'BusinessShortCode' => $shortcode,
    'Password'          => $password,
    'Timestamp'         => $timestamp,
    'TransactionType'   => 'CustomerPayBillOnline',
    'Amount'            => 1,                       // Minimum sandbox amount
    'PartyA'            => $testPhone,
    'PartyB'            => $shortcode,
    'PhoneNumber'       => $testPhone,
    'CallBackURL'       => $callback ?: 'https://example.com/callback',
    'AccountReference'  => 'TUMSDA-TEST',
    'TransactionDesc'   => 'Sandbox smoke test',
];

$ch = curl_init("$base/mpesa/stkpush/v1/processrequest");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        "Authorization: Bearer $token",
    ],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => ($mpesaEnv === 'production'),
]);
$stkRaw  = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
    die("[ERROR] STK Push cURL failed: $curlErr\n");
}

$stkRes = json_decode($stkRaw, true);

echo "  HTTP Status : $httpCode\n";
echo "  Response    :\n";
echo json_encode($stkRes, JSON_PRETTY_PRINT) . "\n\n";

if (isset($stkRes['CheckoutRequestID'])) {
    echo "  ✔ STK Push accepted!\n";
    echo "  CheckoutRequestID : " . $stkRes['CheckoutRequestID'] . "\n";
    echo "  MerchantRequestID : " . ($stkRes['MerchantRequestID'] ?? 'n/a') . "\n";
    echo "\n  ➤ Next: Check the ngrok inspector at http://127.0.0.1:4040\n";
    echo "    Safaricom will POST the callback result to:\n";
    echo "    $callback\n";
} else {
    echo "  ✖ STK Push FAILED.\n";
    echo "    errorCode   : " . ($stkRes['errorCode'] ?? 'n/a') . "\n";
    echo "    errorMessage: " . ($stkRes['errorMessage'] ?? 'n/a') . "\n";
}

echo "\n==========================================================\n";
echo " Done.\n";
echo "==========================================================\n";
