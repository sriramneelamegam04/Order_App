<?php
// payments/crypto_helpers.php
require_once __DIR__ . '/../config/crypto.php';

function encrypt_secret_base64($plaintext) {
    $key = crypto_get_key_raw();
    $ivlen = openssl_cipher_iv_length('aes-256-cbc');
    $iv = random_bytes($ivlen);
    $cipher_raw = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    // store base64 so it's easy to insert into TEXT column
    return [
        'ciphertext_b64' => base64_encode($cipher_raw),
        'iv_b64' => base64_encode($iv)
    ];
}

function decrypt_secret_base64($ciphertext_b64, $iv_b64) {
    $key = crypto_get_key_raw();
    $cipher_raw = base64_decode($ciphertext_b64);
    $iv = base64_decode($iv_b64);
    $plain = openssl_decrypt($cipher_raw, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $plain;
}
