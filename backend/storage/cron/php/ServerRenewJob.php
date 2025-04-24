<?php

namespace MythicalDash\Cron;

use MythicalDash\Config\ConfigInterface;
use Exception;
use MythicalDash\Cron\TimeTask;

class ServerRenewJob implements TimeTask
{
	private const MAX_RETRIES = 3;
	private const RETRY_DELAY = 5; // seconds

	public static function run()
	{
		unlink(__DIR__.'/../../caches/cron/renew-worker.myc');
		try {
			$cron = new Cron('renew-worker', '1D');
			$cron->runIfDue(function () {
				self::processServerRenewals();
			});
		} catch (Exception $e) {
			self::logError("Critical error in ServerRenewJob: " . $e->getMessage());
		}
	}

	private static function processServerRenewals(): void
	{
		$app = \MythicalDash\App::getInstance(false, true);
		$logger = $app->getLogger();
		$config = $app->getConfig();
		$chat = new \MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;

		try {
			$chat->sendOutputWithNewLine("&aServer renewal job started");
			$enabled = self::isServerRenewalEnabled($config);
			
			if (!$enabled) {
				$chat->sendOutputWithNewLine("&eServer renewal is disabled in configuration");
				return;
			}

			$servers = \MythicalDash\Chat\Servers\Server::getList();
			if (empty($servers)) {
				$chat->sendOutputWithNewLine("&eNo servers found to process");
				return;
			}

			foreach ($servers as $server) {
				self::processServer($server, $chat, $logger);
			}
		} catch (Exception $e) {
			self::logError("Error processing server renewals: " . $e->getMessage());
			$chat->sendOutputWithNewLine("&cError processing server renewals: " . $e->getMessage());
		}
	}

	private static function isServerRenewalEnabled($config): bool
	{
		$enabled = $config->getSetting(ConfigInterface::SERVER_RENEW_ENABLED, 'false');
		return strtolower($enabled) === 'true';
	}

	private static function processServer(array $server, $chat, $logger): void
	{
		try {
			$pterodactyl_ID = (int)$server['pterodactyl_id'];
			$user = $server['user'];
			$expires_at = $server['expires_at'];

			if (!self::validateServerData($pterodactyl_ID, $user, $expires_at)) {
				$chat->sendOutputWithNewLine("&cInvalid server data for ID: $pterodactyl_ID");
				return;
			}

			if (!\MythicalDash\Hooks\Pterodactyl\Admin\Servers::serverExists($pterodactyl_ID)) {
				self::handleNonExistentServer($pterodactyl_ID, $chat);
				return;
			}

			self::logServerInfo($pterodactyl_ID, $user, $expires_at, $chat);
			self::checkServerExpiry($pterodactyl_ID, $user, $expires_at, $chat, $logger);
		} catch (Exception $e) {
			self::logError("Error processing server $pterodactyl_ID: " . $e->getMessage());
			$chat->sendOutputWithNewLine("&cError processing server $pterodactyl_ID: " . $e->getMessage());
		}
	}

	private static function validateServerData(int $pterodactyl_ID, string $user, string $expires_at): bool
	{
		return $pterodactyl_ID > 0 && !empty($user) && !empty($expires_at);
	}

	private static function handleNonExistentServer(int $pterodactyl_ID, $chat): void
	{
		try {
			\MythicalDash\Chat\Servers\Server::deleteServerByPterodactylId($pterodactyl_ID);
			$chat->sendOutputWithNewLine("&cServer $pterodactyl_ID does not exist and has been deleted");
		} catch (Exception $e) {
			self::logError("Failed to delete non-existent server $pterodactyl_ID: " . $e->getMessage());
		}
	}

	private static function logServerInfo(int $pterodactyl_ID, string $user, string $expires_at, $chat): void
	{
		$chat->sendOutputWithNewLine("&aServer exists");
		$chat->sendOutputWithNewLine("&aPterodactyl ID: " . $pterodactyl_ID);
		$chat->sendOutputWithNewLine("&aUser: " . $user);
		$chat->sendOutputWithNewLine("&aExpires at: " . $expires_at);
	}

	private static function checkServerExpiry(int $pterodactyl_ID, string $user, string $expires_at, $chat, $logger): void
	{
		$expires_timestamp = strtotime($expires_at);
		$current_time = time();
		$days_until_expiry = floor(($expires_timestamp - $current_time) / 86400);

		if ($days_until_expiry <= 7 && $days_until_expiry > 0) {
			self::handleExpiringServer($pterodactyl_ID, $user, $days_until_expiry, $chat);
		}

		if ($days_until_expiry <= 1 && $days_until_expiry > 0) {
			self::handleFinalDayServer($pterodactyl_ID, $user, $chat, $logger);
		}
	}

	private static function handleExpiringServer(int $pterodactyl_ID, string $user, int $days_until_expiry, $chat): void
	{
		try {
			$chat->sendOutputWithNewLine("&eServer will expire in $days_until_expiry days");
			\MythicalDash\Mail\templates\ServerRenewReminder::sendMail($user, $pterodactyl_ID);
		} catch (Exception $e) {
			self::logError("Failed to send renewal reminder for server $pterodactyl_ID: " . $e->getMessage());
		}
	}

	private static function handleFinalDayServer(int $pterodactyl_ID, string $user, $chat, $logger): void
	{
		try {
			$suspension_reason = 'You didn\'t renew your server in time.';
			\MythicalDash\Mail\templates\ServerSuspended::sendMail($user, $pterodactyl_ID, $suspension_reason);

			$serverInfo = self::getServerInfoWithRetry($pterodactyl_ID);
			if (!$serverInfo) {
				throw new Exception("Failed to get server info after retries");
			}

			$suspended = $serverInfo['attributes']['suspended'];
			if (!$suspended) {
				self::suspendServer($pterodactyl_ID, $chat);
			} else {
				self::deleteExpiredServer($pterodactyl_ID, $user, $chat, $logger);
			}
		} catch (Exception $e) {
			self::logError("Failed to handle final day server $pterodactyl_ID: " . $e->getMessage());
			$chat->sendOutputWithNewLine("&cFailed to handle final day server: " . $e->getMessage());
		}
	}

	private static function getServerInfoWithRetry(int $pterodactyl_ID): ?array
	{
		$retries = 0;
		while ($retries < self::MAX_RETRIES) {
			try {
				return \MythicalDash\Hooks\Pterodactyl\Admin\Servers::getServerPterodactylDetails($pterodactyl_ID);
			} catch (Exception $e) {
				$retries++;
				if ($retries < self::MAX_RETRIES) {
					sleep(self::RETRY_DELAY);
				}
			}
		}
		return null;
	}

	private static function suspendServer(int $pterodactyl_ID, $chat): void
	{
		try {
			$chat->sendOutputWithNewLine("&cServer is in final day before expiry, suspending...");
			\MythicalDash\Hooks\Pterodactyl\Admin\Servers::performSuspendServer($pterodactyl_ID);
			$chat->sendOutputWithNewLine("&aServer suspended successfully");
		} catch (Exception $e) {
			throw new Exception("Failed to suspend server: " . $e->getMessage());
		}
	}

	private static function deleteExpiredServer(int $pterodactyl_ID, string $user, $chat, $logger): void
	{
		try {
			$chat->sendOutputWithNewLine("&cServer is already suspended");
			\MythicalDash\Mail\templates\ServerDeleted::sendMail($user);
			\MythicalDash\Hooks\Pterodactyl\Admin\Servers::deletePterodactylServer($pterodactyl_ID);
			$chat->sendOutputWithNewLine("&aServer deleted successfully");
		} catch (Exception $e) {
			$logger->error("Failed to delete expired server $pterodactyl_ID: " . $e->getMessage());
			$chat->sendOutputWithNewLine("&cFailed to delete server: " . $e->getMessage());
		}
	}

	private static function logError(string $message): void
	{
		try {
			$app = \MythicalDash\App::getInstance(false, true);
			$logger = $app->getLogger();
			$logger->error($message);
		} catch (Exception $e) {
			// If logging fails, at least write to PHP error log
			error_log("MythicalDash ServerRenewJob Error: " . $message);
		}
	}
}