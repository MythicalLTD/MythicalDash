<?php
namespace MythicalDash\Cron;

use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Chat\Servers\ServerQueue;
use MythicalDash\Chat\User\User;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\Pterodactyl\Admin\Resources\NestsResource;
use MythicalDash\Services\Pterodactyl\Admin\Resources\ServersResource;

class ServerCreationJob
{
	public static function run()
	{
		$cron = new Cron('service-worker', '1M');
		$cron->runIfDue(function () {
			$app = \MythicalDash\App::getInstance(false, true);
			$logger = $app->getLogger();
			$chat = new \MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;
			$chat->sendOutputWithNewLine("&aServer creation job started");

			// Get all pending servers from the queue
			$serversQ = ServerQueue::getAll();
			$pendingServers = array_filter($serversQ, function ($server) {
				return $server['status'] === 'pending';
			});

			if (empty($pendingServers)) {
				return;
			}

			foreach ($pendingServers as $server) {
				self::processServer($server, $app, $logger, $chat);
			}
		});
	}

	private static function processServer($server, $app, $logger, $chat)
	{
		$id = $server['id'];
		$name = $server['name'];
		$description = $server['description'];
		$ram = $server['ram'];
		$disk = $server['disk'];
		$cpu = $server['cpu'];
		$ports = $server['ports'];
		$databases = $server['databases'];
		$backups = $server['backups'];
		$location = $server['location'];
		$userUUID = $server['user'];
		$nest = $server['nest'];
		$egg = $server['egg'];

		ServerQueue::updateStatus($id, 'building');
		$servePrefix = "&7[&bServer&f/&d" . $name . "&f/&5" . $id . "&7] ";
		$chat->sendOutputWithNewLine("--------------------------------");

		// Validate all required resources exist
		if (!self::validateResources($id, $servePrefix, $userUUID, $nest, $egg, $location, $logger, $chat)) {
			return;
		}

		// Get resource details
		$locationData = Locations::get($location);
		$eggData = Eggs::getById($egg);
		$category = EggCategories::get($nest);

		try {
			$locationId = $locationData['pterodactyl_location_id'];
			$eggId = $eggData['pterodactyl_egg_id'];
			$nestId = $category['pterodactyl_nest_id'];
			$serverOwnerToken = User::getTokenFromUUID($userUUID);
			$pterodactylUserId = User::getInfo($serverOwnerToken, UserColumns::PTERODACTYL_USER_ID, false);

			// Validate Pterodactyl resources
			if (!self::validatePterodactylResources($id, $servePrefix, $locationId, $eggId, $nestId, $pterodactylUserId, $logger, $chat)) {
				return;
			}

			self::createPterodactylServer(
				$app,
				$id,
				$servePrefix,
				$name,
				$pterodactylUserId,
				$eggId,
				$nestId,
				$ram,
				$disk,
				$cpu,
				$ports,
				$databases,
				$backups,
				$locationId,
				$description,
				$logger,
				$chat,
				$userUUID
			);

		} catch (\Exception $e) {
			$logger->error("Error creating server {$id}: " . $e->getMessage());
			$chat->sendOutputWithNewLine($servePrefix . "&cError creating server: " . $e->getMessage());
			ServerQueue::updateStatus($id, 'failed');
		}
	}

	private static function validateResources($id, $servePrefix, $userUUID, $nest, $egg, $location, $logger, $chat)
	{
		// Check if user exists
		if (!User::exists(UserColumns::UUID, $userUUID)) {
			$logger->error("User no longer exists: " . $userUUID);
			$chat->sendOutputWithNewLine($servePrefix . "&cUser no longer exists: " . $userUUID);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		// Check if nest exists
		if (!EggCategories::exists($nest)) {
			$logger->error("Nest no longer exists: " . $nest);
			$chat->sendOutputWithNewLine($servePrefix . "&cNest no longer exists: " . $nest);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		// Check if egg exists
		if (!Eggs::exists($egg)) {
			$logger->error("Egg no longer exists: " . $egg);
			$chat->sendOutputWithNewLine($servePrefix . "&cEgg no longer exists: " . $egg);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		// Check if location exists
		if (!Locations::exists($location)) {
			$logger->error("Location no longer exists: " . $location);
			$chat->sendOutputWithNewLine($servePrefix . "&cLocation no longer exists: " . $location);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		return true;
	}

	private static function validatePterodactylResources($id, $servePrefix, $locationId, $eggId, $nestId, $pterodactylUserId, $logger, $chat)
	{
		// Check if egg exists in Pterodactyl
		if (!\MythicalDash\Hooks\Pterodactyl\Admin\Eggs::doesEggExist($eggId)) {
			$logger->error("Egg no longer exists in Pterodactyl: " . $eggId);
			$chat->sendOutputWithNewLine($servePrefix . "&cEgg no longer exists in Pterodactyl: " . $eggId);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		// Check if location exists in Pterodactyl
		if (!\MythicalDash\Hooks\Pterodactyl\Admin\Locations::doesLocationExist($locationId)) {
			$logger->error("Location no longer exists in Pterodactyl: " . $locationId);
			$chat->sendOutputWithNewLine($servePrefix . "&cLocation no longer exists in Pterodactyl: " . $locationId);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		// Check if nest exists in Pterodactyl
		if (!\MythicalDash\Hooks\Pterodactyl\Admin\Nests::doesNestExist($nestId)) {
			$logger->error("Nest no longer exists in Pterodactyl: " . $nestId);
			$chat->sendOutputWithNewLine($servePrefix . "&cNest no longer exists in Pterodactyl: " . $nestId);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		// Check if user exists in Pterodactyl
		if (!\MythicalDash\Hooks\Pterodactyl\Admin\User::exists($pterodactylUserId)) {
			$logger->error("User no longer exists in Pterodactyl: " . $pterodactylUserId);
			$chat->sendOutputWithNewLine($servePrefix . "&cUser no longer exists in Pterodactyl: " . $pterodactylUserId);
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}

		return true;
	}

	private static function createPterodactylServer($app, $id, $servePrefix, $name, $pterodactylUserId, $eggId, $nestId, $ram, $disk, $cpu, $ports, $databases, $backups, $locationId, $description, $logger, $chat, $userUUID)
	{
		$chat->sendOutputWithNewLine($servePrefix . "&aCreating server...");

		$baseUrl = $app->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, 'https://pterodactyl.mythical.systems');
		$apiKey = $app->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, 'mythical');

		$servers = new ServersResource($baseUrl, $apiKey);
		$nests = new NestsResource($baseUrl, $apiKey);

		try {
			$eggInfo = $nests->getEgg($nestId, $eggId);

			// Prepare environment variables
			$environment = [];
			foreach ($eggInfo['attributes']['relationships']['variables']['data'] as $val) {
				$attr = $val['attributes'];
				$environment[$attr['env_variable']] = $attr['default_value'];
			}

			// Prepare server creation payload
			$json = [
				'name' => $name,
				'user' => (int) $pterodactylUserId,
				'egg' => (int) $eggId,
				'docker_image' => $eggInfo['attributes']['docker_image'],
				'startup' => $eggInfo['attributes']['startup'],
				'description' => $description,
				'limits' => [
					'memory' => (int) $ram,
					'swap' => 0,
					'disk' => (int) $disk,
					'io' => 500,
					'cpu' => (int) $cpu,
				],
				'feature_limits' => [
					'databases' => $databases ? (int) $databases : null,
					'allocations' => (int) $ports,
					'backups' => (int) $backups,
				],
				'deploy' => [
					'locations' => [(int) $locationId],
					'dedicated_ip' => false,
					'port_range' => [],
				],
				'environment' => $environment,
				'external_id' => (string) $id,
			];

			// Create the server
			$response = $servers->createServer($json);

			if (isset($response['attributes']) && isset($response['attributes']['id'])) {
				$chat->sendOutputWithNewLine($servePrefix . "&aServer created successfully with ID: " . $response['attributes']['id']);
				Server::create($response['attributes']['id'], $id, $userUUID);
				ServerQueue::updateStatus($id, 'completed');
				$chat->sendOutputWithNewLine($servePrefix . "&aServer information stored in database");
				return true;
			} else {
				$logger->error("Failed to create server {$id}: " . json_encode($response));
				$chat->sendOutputWithNewLine($servePrefix . "&cFailed to create server: " . json_encode($response));
				ServerQueue::updateStatus($id, 'failed');
				return false;
			}
		} catch (\Exception $e) {
			$logger->error("Error during server creation for {$id}: " . $e->getMessage());
			$chat->sendOutputWithNewLine($servePrefix . "&cError during server creation: " . $e->getMessage());
			ServerQueue::updateStatus($id, 'failed');
			return false;
		}
	}
}