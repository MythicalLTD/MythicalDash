<?php

namespace MythicalDash\Cron;

use MythicalDash\Chat\Announcements\Announcements;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\Earn\GyaniLinks;
use MythicalDash\Chat\Earn\Linkvertise;
use MythicalDash\Chat\Earn\ShareUS;
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\Gateways\PayPalDB;
use MythicalDash\Chat\Gateways\StripeDB;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Redeem\RedeemCoins;
use MythicalDash\Chat\Referral\ReferralCodes;
use MythicalDash\Chat\Referral\ReferralUses;
use MythicalDash\Chat\Tickets\Tickets;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Config\PublicConfig;
use MythicalDash\Cron\Cron;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Chat\Earn\LinkPays;
use MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;
use MythicalDash\Plugins\PluginConfig;
use MythicalDash\Cron\TimeTask;

class TelemetryJob implements TimeTask
{
	public static function run()
	{
		$cron = new Cron('telemetry-job', '1D');
		try {
			$cron->runIfDue(function () {
				$app = \MythicalDash\App::getInstance(false, true);
				$chat = new BungeeChatApi();
				$config = $app->getConfig();
	
				$isEnabled = $config->getSetting(ConfigInterface::TELEMETRY_ENABLED, 'true');
				if ($isEnabled === 'true') {
					$isEnabled = true;
				} else {
					$isEnabled = false;
				}
	
				$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &7Status: ' . ($isEnabled ? '&aEnabled' : '&cDisabled'));
	
				if ($isEnabled) {
					$appID = preg_replace('/^https?:\/\//', '', $config->getSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems'));
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3App ID: &f' . $appID);
					$servers = Database::getTableRowCount(Server::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Servers: &f' . $servers);
					$users = Database::getTableRowCount(User::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Users: &f' . $users);
					$locations = Database::getTableRowCount(Locations::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Locations: &f' . $locations);
					$eggs = Database::getTableRowCount(Eggs::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Eggs: &f' . $eggs);
					$nests = Database::getTableRowCount(EggCategories::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Nests: &f' . $nests);
					$tickets = Database::getTableRowCount(Tickets::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Tickets: &f' . $tickets);
					$announcements = Database::getTableRowCount(Announcements::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Announcements: &f' . $announcements);
					$userActivities = Database::getTableRowCount(UserActivities::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3User Activities: &f' . $userActivities);
					$gyaniLinks = Database::getTableRowCount(GyaniLinks::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Gyani Links: &f' . $gyaniLinks);
					$linkPays = Database::getTableRowCount(LinkPays::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Link Pays: &f' . $linkPays);
					$linkvertise = Database::getTableRowCount(Linkvertise::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Linkvertise: &f' . $linkvertise);
					$shareUs = Database::getTableRowCount(ShareUS::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Share US: &f' . $shareUs);
					$payPalPayments = Database::getTableRowCount(PayPalDB::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3PayPal Payments: &f' . $payPalPayments);
					$stripePayments = Database::getTableRowCount(StripeDB::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Stripe Payments: &f' . $stripePayments);
					$redeems = Database::getTableRowCount(RedeemCoins::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Redeems: &f' . $redeems);
					$referrals = Database::getTableRowCount(ReferralCodes::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Referrals: &f' . $referrals);
					$referralsUsers = Database::getTableRowCount(ReferralUses::getTableName());
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Referrals Users: &f' . $referralsUsers);
					$configs = Database::getTableRowCount('mythicaldash_settings', true);
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Configs: &f' . $configs);
	
					$numeric_data = [
						'servers' => $servers,
						'users' => $users,
						'locations' => $locations,
						'eggs' => $eggs,
						'nests' => $nests,
						'tickets' => $tickets,
						'announcements' => $announcements,
						'userActivities' => $userActivities,
						'gyaniLinks' => $gyaniLinks,
						'linkPays' => $linkPays,
						'linkvertise' => $linkvertise,
						'shareUs' => $shareUs,
						'payPalPayments' => $payPalPayments,
						'stripePayments' => $stripePayments,
						'redeems' => $redeems,
						'referrals' => $referrals,
						'referralsUsers' => $referralsUsers,
						'configs' => $configs,
					];
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &eFetching logs...');
					$logs = $app->getLogger()->getLogs();
					$webServerLogs = $app->getWebServerLogger()->getLogs();

					if (count($logs) > 250) {
						$logs = array_slice($logs, -250);
					}
					if (count($webServerLogs) > 250) {
						$webServerLogs = array_slice($webServerLogs, -250);
					}
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Logs: &f' . count($logs));
	
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &eFetching public settings...');
					$publicSettings = PublicConfig::getPublicSettingsWithDefaults();
					$publicSettings = $config->getSettings(array_keys($publicSettings));
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &3Public settings: &f' . count($publicSettings));
					$dateNow = date('Y-m-d H:i:s');
					
	
					$systemInfo = [];
					try {
						// Basic PHP info that's safe to collect
						$systemInfo['php_version'] = PHP_VERSION;
						$systemInfo['os'] = PHP_OS;
						$systemInfo['memory_usage'] = memory_get_usage(true);
						
						// PHP INI settings - only collect non-sensitive configs
						$safeIniSettings = [
							'memory_limit',
							'max_execution_time', 
							'max_input_time',
							'upload_max_filesize',
							'post_max_size',
							'max_input_vars',
							'default_charset',
							'date.timezone'
						];
						
						foreach ($safeIniSettings as $setting) {
							try {
								$value = ini_get($setting);
								if ($value !== false) {
									$systemInfo[str_replace('.', '_', $setting)] = $value;
								} else {
									$systemInfo[str_replace('.', '_', $setting)] = 'unknown';
								}
							} catch (\Throwable $e) {
								$systemInfo[str_replace('.', '_', $setting)] = 'unknown';
							}
						}

						// Safely get loaded extensions
						try {
							$extensions = get_loaded_extensions();
							if (is_array($extensions)) {
								$systemInfo['loaded_extensions'] = $extensions;
							} else {
								$systemInfo['loaded_extensions'] = [];
							}
						} catch (\Throwable $e) {
							$systemInfo['loaded_extensions'] = [];
						}

						// Only collect non-sensitive server variables
						$safeServerVars = [
							'SERVER_SOFTWARE',
							'SERVER_PROTOCOL',
							'REQUEST_SCHEME',
							'SERVER_PORT'
						];
						
						foreach ($safeServerVars as $var) {
							$systemInfo[strtolower($var)] = $_SERVER[$var] ?? 'unknown';
						}

						// Basic PHP info
						$systemInfo['server_api'] = PHP_SAPI;
						$systemInfo['zend_version'] = zend_version();

						// Check opcache safely
						try {
							$systemInfo['opcache_enabled'] = function_exists('opcache_get_status');
						} catch (\Throwable $e) {
							$systemInfo['opcache_enabled'] = false;
						}

						// Get MySQL version through PDO if available
						try {
							if (class_exists('PDO')) {
								$systemInfo['mysql_version'] = Database::runSQL('SELECT VERSION()');
							} else {
								$systemInfo['mysql_version'] = 'unknown';
							}
						} catch (\Throwable $e) {
							$systemInfo['mysql_version'] = 'unknown';
						}

						// Memory info
						$systemInfo['memory_limit'] = ini_get('memory_limit');
						$systemInfo['memory_usage_bytes'] = memory_get_usage(true);
						$systemInfo['memory_peak_usage_bytes'] = memory_get_peak_usage(true);

						// Safe disk space check using PHP built-in
						try {
							if (function_exists('disk_free_space')) {
								$systemInfo['disk_free_space'] = @disk_free_space(__DIR__);
							} else {
								$systemInfo['disk_free_space'] = -1;
							}
						} catch (\Throwable $e) {
							$systemInfo['disk_free_space'] = -1;
						}

						try {
							if (function_exists('disk_total_space')) {
								$systemInfo['disk_total_space'] = @disk_total_space(__DIR__);
							} else {
								$systemInfo['disk_total_space'] = -1; 
							}
						} catch (\Throwable $e) {
							$systemInfo['disk_total_space'] = -1;
						}
	
					} catch (\Throwable $e) {
						// If anything fails, log it but continue with partial data
						$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &cWarning: Some system info collection failed: ' . $e->getMessage());
					}
	
					$telemetryData = [
						'appID' => $appID,
						'date' => $dateNow,
						'numeric_data' => $numeric_data,
						'public_settings' => $publicSettings,
						'logs' => $logs,
						'system_info' => $systemInfo,
						'addons' => [],
					];
					
					global $pluginManager;

					$addons = $pluginManager->getPluginsWithoutLoader();
					
					foreach ($addons as $addon) {
						$addonConfig = PluginConfig::getConfig($addon);
						$telemetryData['addons'][] = [
							'name' => $addon,
							'config' => $addonConfig,
						];
					}
	
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &ePreparing telemetry data...');
					$fileName = 'telemetry-' . $appID . '-' . date('Y-m-d') . '.json';
					$dir = APP_CACHE_DIR . '/telemetry/';
	
					if (!file_exists($dir)) {
						mkdir($dir, 0755, true);
					}
	
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &eSaving telemetry data...');
					$jsonData = json_encode($telemetryData, JSON_PRETTY_PRINT);
					file_put_contents($dir . $fileName, $jsonData);
	
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &eSaving telemetry data...');
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &aTelemetry data has been processed and saved to: &f' . $fileName);


					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &eSending telemetry data...');
					try {
						$ch = curl_init();
						curl_setopt_array($ch, [
							CURLOPT_URL => 'https://api.mythical.systems/v2/telemetry/mythicaldash/push',
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_POST => true,
							CURLOPT_POSTFIELDS => $jsonData,
							CURLOPT_HTTPHEADER => [
								'Content-Type: application/json',
								'Accept: application/json'
							],
							CURLOPT_TIMEOUT => 10,
							CURLOPT_SSL_VERIFYPEER => true,
							CURLOPT_SSL_VERIFYHOST => 2
						]);

						$response = curl_exec($ch);
						
						if (curl_errno($ch)) {
							throw new \Exception('Curl error: ' . curl_error($ch));
						}

						$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
						curl_close($ch);

						if ($httpCode >= 200 && $httpCode < 300) {
							$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &aTelemetry data sent successfully');
						} else {
							throw new \Exception('HTTP error: ' . $httpCode . ' Response: ' . $response);
						}

					} catch (\Throwable $e) {
						$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &cFailed to send telemetry data: ' . $e->getMessage());
					}
				}

			});
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}
}