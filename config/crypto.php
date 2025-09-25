<?php
// config/crypto.php
if (!defined('ENCRYPTION_KEY')) {
    $env = getenv('ENCRYPTION_KEY') ?: null;
    if ($env) {
        define('ENCRYPTION_KEY', $env);
    } else {
        // DEV fallback — replace before production!
        define('ENCRYPTION_KEY', 'base64:Kq8B2LqfV7zU8k2+1D0JkF1t1H8PZx9QH1R4yF2G5L0=');

    }
}

function crypto_get_key_raw() {
    $k = ENCRYPTION_KEY;
    if (strpos($k, 'base64:') === 0) $k = substr($k, 7);
    $raw = base64_decode($k, true);
    if ($raw === false) throw new Exception("Invalid ENCRYPTION_KEY format. Use base64:<data>");
    return $raw; // should be 32 bytes (for AES-256)
}
