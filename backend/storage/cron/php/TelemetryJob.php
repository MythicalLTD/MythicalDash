<?php

namespace MythicalDash\Cron;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
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
	public function run()
	{
		$cron = new Cron('telemetry-job', '1D');
		try {
			$cron->runIfDue(function () {
				$app = \MythicalDash\App::getInstance(false, true);
				$chat = new BungeeChatApi();
				$config = $app->getConfig();

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


				$telemetryData = [
					'appID' => $appID,
					'date' => $dateNow,
					'numeric_data' => $numeric_data,
					'public_settings' => $publicSettings,
					'logs' => $logs,
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
					$licenseKey = $config->getSetting(ConfigInterface::LICENSE_KEY, 'NULL');

					if ($licenseKey === 'NULL') {
						$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &cNo license key found');
						return;
					}

					$client = new Client();
					$headers = [
						'Content-Type' => 'application/json',
						'User-Agent' => 'MythicalDash/3.0'
					];
					$body = json_encode($telemetryData);
					$request = new Request('PUT', 'https://mymythicalid.mythical.systems/api/system/license/' . $licenseKey . '/telemetry', $headers, $body);
					$res = $client->sendAsync($request)->wait();
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &aTelemetry data sent successfully: ' . $res->getBody());


				} catch (\Throwable $e) {
					$chat->sendOutputWithNewLine('&8[&bTelemetry&8] &cFailed to send telemetry data: ' . $e->getMessage());
				}


			});
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}
}