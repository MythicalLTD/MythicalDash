<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
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
