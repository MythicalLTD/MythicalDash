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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Hooks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class LicenseSystem
{
    private const API_BASE_URL = 'https://mymythicalid.mythical.systems/api/system';
    private const ERROR_CODES = [
        'LICENSE_KEY_DOES_NOT_EXIST',
        'LICENSE_KEY_EXPIRED',
        'LICENSE_KEY_INACTIVE',
        'LICENSE_KEY_DELETED',
        'LICENSE_KEY_LOCKED',
    ];
    private const CACHE_DIR = __DIR__ . '/../../storage/caches/licenses/';
    private const CACHE_TTL = 3600; // 1 hour cache

    public function __construct()
    {
        // Ensure cache directory exists
        if (!is_dir(self::CACHE_DIR) && !file_exists(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }
    }

    /**
     * Validates a license key by making an API call or retrieving from cache.
     *
     * @param string $licenseKey The license key to validate
     * @param string $instanceUrl The instance URL to validate against
     *
     * @throws \Exception If the API call fails or returns an error
     *
     * @return array Returns an array containing validation status and data
     */
    public function validateLicense(string $licenseKey, string $instanceUrl): array
    {
        $cacheFile = self::CACHE_DIR . md5($licenseKey) . '.json';

        // Try to get from cache first
        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);

            // Check if cache is still valid
            if (isset($cacheData['expires_at']) && $cacheData['expires_at'] > time()) {
                $data = $cacheData['data'];

                // Validate cached data against instance URL
                $instanceInfo = $data['data']['instance'] ?? [];
                $instanceUrlLicense = $instanceInfo['instanceUrl'];
                // Check if instance URL has protocol, if not add https://
                if (!preg_match('~^(?:f|ht)tps?://~i', $instanceUrl)) {
                    $instanceUrl = 'https://' . $instanceUrl;
                }

                if ($instanceUrlLicense !== $instanceUrl) {
                    throw new \Exception('LICENSE_KEY_INSTANCE_URL_MISMATCH');
                }

                return [
                    'valid' => true,
                    'data' => $data['data'],
                    'cached' => true,
                ];
            }
        }

        try {
            $client = new Client([
                'force_ip_resolve' => 'v4',
            ]);
            $response = $client->get(self::API_BASE_URL . '/license/' . $licenseKey . '/info', [
                'headers' => [
                    'MythicalDash-Install' => 'true',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['success']) || !$data['success']) {
                throw new \Exception($data['error'] ?? 'Unknown error occurred');
            }

            // Check if the license is valid based on the response
            if ($data['code'] !== 200) {
                throw new \Exception('Invalid license response');
            }

            // Check if the license key info indicates any issues
            $licenseInfo = $data['data']['license_key_info'] ?? [];
            if ($licenseInfo['deleted'] === 'true') {
                throw new \Exception('LICENSE_KEY_DELETED');
            }
            if ($licenseInfo['locked'] === 'true') {
                throw new \Exception('LICENSE_KEY_LOCKED');
            }
            if ($licenseInfo['status'] !== 'active') {
                throw new \Exception('LICENSE_KEY_INACTIVE');
            }

            // Check if the license has expired
            $expiresAt = strtotime($licenseInfo['expires_at']);
            if ($expiresAt < time()) {
                throw new \Exception('LICENSE_KEY_EXPIRED');
            }

            $instanceInfo = $data['data']['instance'] ?? [];
            $instanceUrlLicense = $instanceInfo['instanceUrl'];
            // Check if instance URL has protocol, if not add https://
            if (!preg_match('~^(?:f|ht)tps?://~i', $instanceUrl)) {
                $instanceUrl = 'https://' . $instanceUrl;
            }
            if ($instanceUrlLicense !== $instanceUrl) {
                throw new \Exception('LICENSE_KEY_INSTANCE_URL_MISMATCH');
            }

            // Cache the valid license data
            $cacheData = [
                'data' => $data,
                'expires_at' => time() + self::CACHE_TTL,
            ];
            try {
                if (is_writable($cacheFile)) {
                    file_put_contents($cacheFile, json_encode($cacheData));
                }
            } catch (\Exception $e) {
            }

            return [
                'valid' => true,
                'data' => $data['data'],
                'cached' => false,
            ];

        } catch (GuzzleException $e) {
            throw new \Exception('Failed to validate license: ' . $e->getMessage());
        }
    }

    /**
     * Get cached license information.
     *
     * @param string $licenseKey The license key to retrieve
     *
     * @return array|null Returns the cached license data or null if not found
     */
    public function getCachedLicense(string $licenseKey): ?array
    {
        $cacheFile = self::CACHE_DIR . md5($licenseKey) . '.json';

        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if (isset($cacheData['expires_at']) && $cacheData['expires_at'] > time()) {
                return $cacheData['data'];
            }
        }

        return null;
    }

    /**
     * Clear cached license information.
     *
     * @param string $licenseKey The license key to clear from cache
     *
     * @return bool Returns true if the cache was cleared successfully
     */
    public function clearLicenseCache(string $licenseKey): bool
    {
        $cacheFile = self::CACHE_DIR . md5($licenseKey) . '.json';
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }

        return false;
    }
}
