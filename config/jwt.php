<?php
// config/jwt.php
// Minimal JWT helper (HS256). No external libraries required.

// SECRET: set a long random string and keep it safe (env/config)
if (!defined('JWT_SECRET')) define('JWT_SECRET', 'replace_with_long_random_secret_here_please_change');

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64url_decode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) $data .= str_repeat('=', 4 - $remainder);
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Create JWT
 * @param array $payload
 * @param int $ttl seconds (default 300 = 5min)
 * @return string
 */
function jwt_create(array $payload, int $ttl = 300): string {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $now = time();
    $payload = array_merge([
        'iat' => $now,
        'exp' => $now + $ttl
    ], $payload);

    $b64header = base64url_encode(json_encode($header));
    $b64payload = base64url_encode(json_encode($payload));
    $signing_input = $b64header . '.' . $b64payload;
    $sig = hash_hmac('sha256', $signing_input, JWT_SECRET, true);
    $b64sig = base64url_encode($sig);

    return $signing_input . '.' . $b64sig;
}

/**
 * Verify and decode JWT
 * @param string $token
 * @return array|false payload array on success or false on failure
 */
function jwt_verify(string $token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$b64header, $b64payload, $b64sig] = $parts;

    $header = json_decode(base64url_decode($b64header), true);
    if (!$header || ($header['alg'] ?? '') !== 'HS256') return false;

    $signing_input = $b64header . '.' . $b64payload;
    $raw_sig = base64url_decode($b64sig);
    $expected_sig = hash_hmac('sha256', $signing_input, JWT_SECRET, true);

    // timing-safe compare
    if (!hash_equals($expected_sig, $raw_sig)) return false;

    $payload = json_decode(base64url_decode($b64payload), true);
    if (!$payload) return false;

    // check exp
    if (isset($payload['exp']) && time() > (int)$payload['exp']) return false;

    return $payload;
}
