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

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;

$router->add('/api/system/license/branding-removal', function (): void {
	App::init();
	$appInstance = App::getInstance(true);
	$config = $appInstance->getConfig();

	$licenseSystem = $appInstance->getLicenseSystem();
	try {
		$keyData = $licenseSystem->validateLicense($config->getSetting(ConfigInterface::LICENSE_KEY, 'NULL'), $config->getSetting(ConfigInterface::APP_URL, 'true'));
	} catch (\Exception $e) {
		App::OK('License is invalid!', ['valid' => false]);
	}
	if ($keyData['valid']) {
		$licenseData = $keyData['data'];
		$project_info = $licenseData['project_info'];
		$instance_info = $licenseData['instance_info'];
		$license_key_info = $licenseData['license_key_info'];

		if (isset($project_info['features']) && in_array('Branding Removal', $project_info['features'])) {
			App::OK('License is valid!', ['valid' => true]);
		} else {
			App::OK('License is invalid! Branding removal feature not found.', ['valid' => false]);
		}
	} else {
		App::OK('License is invalid!', ['valid' => false]);
	}
});
$router->add('/api/system/license', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    
    $licenseSystem = $appInstance->getLicenseSystem();
    try {
        $keyData = $licenseSystem->validateLicense($config->getSetting(ConfigInterface::LICENSE_KEY, 'NULL'), $config->getSetting(ConfigInterface::APP_URL, 'true'));
        if ($keyData['valid']) {
            $licenseData = $keyData['data'];
            $project_info = $licenseData['project_info'];
            $instance_info = $licenseData['instance'];
            $license_key_info = $licenseData['license_key_info'];

            App::OK('License is valid!', [
                'valid' => true,
                'data' => [
                    'project_info' => [
                        'name' => $project_info['name'],
                        'description' => $project_info['description'],
                        'features' => $project_info['features'],
                        'expires_at' => $license_key_info['expires_at']
                    ],
                    'instance_info' => [
                        'companyName' => $instance_info['companyName'],
                        'companyWebsite' => $instance_info['companyWebsite'],
                        'instanceUrl' => $instance_info['instanceUrl'],
						'abuseEmail' => $instance_info['abuseEmail'],
						'supportEmail' => $instance_info['supportEmail'],
					],
					'license_key_info' => [
						'expires_at' => $license_key_info['expires_at'],
						'status' => $license_key_info['status'],
						'locked' => $license_key_info['locked'],
						'deleted' => $license_key_info['deleted'],
						
					],
				],
            ]);
        } else {
            App::OK('License is invalid!', ['valid' => false]);
        }
    } catch (\Exception $e) {
        App::OK('License is invalid!', ['valid' => false]);
    }
});