<?php
// api/config/cloudinary.php
// Simple helper to upload files to Cloudinary using their REST API.

declare(strict_types=1);

function uploadToCloudinary(string $filePath): ?string {
    $cloudName = env('CLOUDINARY_CLOUD_NAME');
    $apiKey    = env('CLOUDINARY_API_KEY');
    $apiSecret = env('CLOUDINARY_API_SECRET');

    if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
        return null; // Fallback to local
    }

    $timestamp = time();
    
    // Cloudinary signature parameters must be sorted alphabetically
    $params = [
        'timestamp' => $timestamp
    ];
    ksort($params);
    
    $signParts = [];
    foreach ($params as $k => $v) {
        $signParts[] = "$k=$v";
    }
    $signString = implode('&', $signParts) . $apiSecret;
    $signature = sha1($signString);

    $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
    
    $ch = curl_init($url);
    if ($ch === false) {
        error_log('[Cloudinary] Failed to initialize curl.');
        return null;
    }

    $cFile = new CURLFile($filePath);
    
    $postFields = [
        'file'      => $cFile,
        'api_key'   => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature
    ];

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // compatibility fallback for systems with misconfigured SSL certs

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log('[Cloudinary] Curl error: ' . $err);
        return null;
    }

    $data = json_decode($response, true);
    if (isset($data['secure_url'])) {
        return $data['secure_url'];
    }

    if (isset($data['error']['message'])) {
        error_log('[Cloudinary] API Error: ' . $data['error']['message']);
    } else {
        error_log('[Cloudinary] Unknown error. Response: ' . $response);
    }

    return null;
}
