<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 */

namespace MythicalDash\Hooks\MythicalSystems\Utils;

class XChaCha20
{
    /**
     * Encrypt the data specified.
     *
     * @param string|array $data The data that should be encrypted!
     * @param string $key The key you want to encrypt the data with!
     * @param bool $isKeyHashed Is the key hashed in base64?
     *
     * @return string|array The encrypted data!
     */
    public static function encrypt(string|array $data, string $key, bool $isKeyHashed = true): string|array
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        if ($isKeyHashed) {
            $key = base64_decode($key);
        }
        $encrypted = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($data, $nonce, $nonce, $key);

        return base64_encode($nonce . $encrypted);
    }

    /**
     * Decrypt the data specified.
     *
     * @param string|array $data the data that should be decrypted!
     * @param string $key The key you want to decrypt the data with!
     * @param bool $isKeyHashed Is the key hashed in base64?
     */
    public static function decrypt(string|array $data, string $key, bool $isKeyHashed = true): string|array
    {
        $data = base64_decode($data);
        if ($isKeyHashed) {
            $key = base64_decode($key);
        }
        $nonce = mb_substr($data, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES, '8bit');
        $encrypted = mb_substr($data, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES, null, '8bit');

        return sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($encrypted, $nonce, $nonce, $key);
    }

    /**
     * Check if the encryption key is strong.
     *
     * @param string $key The key
     * @param bool $isKeyHashed Is the key hashed in base64?
     */
    public static function checkIfStrongKey(string $key, bool $isKeyHashed): bool
    {
        if ($isKeyHashed) {
            $key = base64_decode($key);
        }

        return strlen($key) >= 32;
    }

    /**
     * Generate a strong key.
     *
     * @param bool $hash Should we hash the key in order so you can use it in the config?
     *
     * @return string The encryption key!
     */
    public static function generateStrongKey(bool $hash): string
    {
        if ($hash) {
            return base64_encode(sodium_crypto_secretbox_keygen());
        }

        return sodium_crypto_secretbox_keygen();

    }
}
