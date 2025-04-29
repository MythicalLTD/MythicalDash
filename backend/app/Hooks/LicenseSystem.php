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

namespace MythicalDash\Hooks;

use MythicalDash\App;
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
		'LICENSE_KEY_LOCKED'
	];

	/**
	 * Validates a license key by making an API call
	 *
	 * @param string $licenseKey The license key to validate
	 * @return array Returns an array containing validation status and data
	 * @throws \Exception If the API call fails or returns an error
	 */
	public function validateLicense(string $licenseKey, string $instanceUrl): array
	{
		try {
			$client = new Client();
			$response = $client->get(self::API_BASE_URL . '/license/' . $licenseKey . '/info');
			
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

			if ($instanceUrlLicense !== $instanceUrl) {
				throw new \Exception('LICENSE_KEY_INSTANCE_URL_MISMATCH');
			}

			return [
				'valid' => true,
				'data' => $data['data'],
			];

		} catch (GuzzleException $e) {
			throw new \Exception('Failed to validate license: ' . $e->getMessage());
		}
	}
}
