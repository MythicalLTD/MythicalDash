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

namespace MythicalDash\Cron;

use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\TimedTask;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Servers\ServerQueue;
use MythicalDash\Chat\Servers\ServerQueueLogs;
use MythicalDash\Services\Pterodactyl\Admin\Resources\NestsResource;
use MythicalDash\Services\Pterodactyl\Admin\Resources\ServersResource;

class ServerCreationJob implements TimeTask
{
    private static $logs = [];
    private static $currentBuildId;
    private static $logId;

    public function run()
    {
        $cron = new Cron('server-deploy', '1M');
        try {
            $cron->runIfDue(function () {
                $app = \MythicalDash\App::getInstance(false, true);
                $logger = $app->getLogger();
                $chat = new \MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi();

                $logger->info('=== Server Creation Job Started ===');
                $startTime = microtime(true);

                // Get all pending servers from the queue
                $serversQ = ServerQueue::getAll();
                $totalServers = count($serversQ);
                $logger->info("Total servers in queue: {$totalServers}");

                $pendingServers = array_filter($serversQ, function ($server) {
                    return $server['status'] === 'pending';
                });

                $pendingCount = count($pendingServers);
                $logger->info("Pending servers to process: {$pendingCount}");

                if (empty($pendingServers)) {
                    $logger->info('No pending servers found. Job completed.');
                    TimedTask::markRun('server-deploy', true, 'Server creation job completed with no servers to process');

                    return;
                }

                $logger->info("Processing {$pendingCount} pending server(s)...");
                foreach ($pendingServers as $index => $server) {
                    $logger->info('Processing server ' . ($index + 1) . "/{$pendingCount} - ID: {$server['id']}, Name: {$server['name']}");
                    self::processServer($server, $app, $logger, $chat);
                }

                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 2);
                $logger->info('=== Server Creation Job Completed ===');
                $logger->info("Processed {$pendingCount} server(s) in {$duration} seconds");
                TimedTask::markRun('server-deploy', true, 'Server creation job completed with ' . count($pendingServers) . ' servers processed');

            }, true);
        } catch (\Exception $e) {
            $app = \MythicalDash\App::getInstance(false, true);
            $app->getLogger()->error('Failed to run server creation job: ' . $e->getMessage());
            $app->getLogger()->error('Exception trace: ' . $e->getTraceAsString());
            TimedTask::markRun('server-deploy', false, 'Server creation job failed: ' . $e->getMessage());
        }
    }

    private static function processServer($server, $app, $logger, $chat)
    {
        $processStartTime = microtime(true);
        $id = $server['id'];
        self::$currentBuildId = $id;
        self::$logs = []; // Reset logs for this server

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

        $logger->info('=== Processing Server Deployment ===');
        $logger->info("Server ID: {$id}");
        $logger->info("Server Name: {$name}");
        $logger->info("User UUID: {$userUUID}");
        $logger->info("Resources - RAM: {$ram}MB, Disk: {$disk}MB, CPU: {$cpu}%, Ports: {$ports}, Databases: {$databases}, Backups: {$backups}");
        $logger->info("Location ID: {$location}, Nest ID: {$nest}, Egg ID: {$egg}");
        $logger->info('Description: ' . substr($description, 0, 100) . (strlen($description) > 100 ? '...' : ''));

        ServerQueue::updateStatus($id, 'building');
        $servePrefix = '&7[&bServer&f/&d' . $name . '&f/&5' . $id . '&7] ';
        self::logMessage('--------------------------------');
        self::logMessage("{$servePrefix} Starting server creation");
        self::logMessage("{$servePrefix} Server Details:");
        self::logMessage("{$servePrefix}   - Name: {$name}");
        self::logMessage("{$servePrefix}   - RAM: {$ram}MB");
        self::logMessage("{$servePrefix}   - Disk: {$disk}MB");
        self::logMessage("{$servePrefix}   - CPU: {$cpu}%");
        self::logMessage("{$servePrefix}   - Ports: {$ports}");
        self::logMessage("{$servePrefix}   - Databases: {$databases}");
        self::logMessage("{$servePrefix}   - Backups: {$backups}");
        $chat->sendOutputWithNewLine('--------------------------------');

        // Create a log entry for this build and save initial logs
        self::$logId = ServerQueueLogs::saveJobLogs($id, self::$logs);
        $logger->info('Created log entry with ID: ' . self::$logId);

        // Validate all required resources exist
        $logger->info('Starting resource validation...');
        if (!self::validateResources($id, $servePrefix, $userUUID, $nest, $egg, $location, $logger, $chat)) {
            $processDuration = round(microtime(true) - $processStartTime, 2);
            $logger->error("Resource validation failed for server {$id}. Process took {$processDuration} seconds.");

            return;
        }
        $logger->info('Resource validation passed');

        // Get resource details
        $logger->info('Fetching resource details from database...');
        $logger->info("Fetching location data for ID: {$location}");
        $locationData = Locations::get((int) $location);
        $logger->info('Location data retrieved: ' . json_encode($locationData));

        $logger->info("Fetching egg data for ID: {$egg}");
        $eggData = Eggs::getById((int) $egg);
        $logger->info('Egg data retrieved: ' . json_encode($eggData));

        $logger->info("Fetching nest/category data for ID: {$nest}");
        $category = EggCategories::get((int) $nest);
        $logger->info('Category data retrieved: ' . json_encode($category));

        try {
            $locationId = $locationData['pterodactyl_location_id'] ?? null;
            $eggId = $eggData['pterodactyl_egg_id'] ?? null;
            $nestId = $category['pterodactyl_nest_id'] ?? null;

            $logger->info("Pterodactyl IDs - Location: {$locationId}, Egg: {$eggId}, Nest: {$nestId}");
            self::logMessage("{$servePrefix} Pterodactyl IDs - Location: {$locationId}, Egg: {$eggId}, Nest: {$nestId}");

            if ($locationId == null || $eggId == null || $nestId == null) {
                $errorMsg = 'Location, egg, or nest not found';
                $logger->error($errorMsg);
                $logger->error('Location ID: ' . ($locationId ?? 'NULL'));
                $logger->error('Egg ID: ' . ($eggId ?? 'NULL'));
                $logger->error('Nest ID: ' . ($nestId ?? 'NULL'));
                $logger->error('Location Data: ' . json_encode($locationData));
                $logger->error('Egg Data: ' . json_encode($eggData));
                $logger->error('Category Data: ' . json_encode($category));
                self::logMessage($servePrefix . '&cLocation, egg, or nest not found');
                $chat->sendOutputWithNewLine($servePrefix . '&cLocation, egg, or nest not found');
                $chat->sendOutputWithNewLine(print_r($locationData, true));
                $chat->sendOutputWithNewLine(print_r($eggData, true));
                $chat->sendOutputWithNewLine(print_r($category, true));
                ServerQueue::updateStatus($id, 'failed');
                TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because location, egg, or nest not found');
            } else {
                $logger->info("Retrieving user token for UUID: {$userUUID}");
                $serverOwnerToken = User::getTokenFromUUID($userUUID);
                $logger->info('User token retrieved: ' . substr($serverOwnerToken, 0, 20) . '...');

                $logger->info('Retrieving Pterodactyl user ID...');
                $pterodactylUserId = User::getInfo($serverOwnerToken, UserColumns::PTERODACTYL_USER_ID, false);
                $logger->info("Pterodactyl User ID: {$pterodactylUserId}");
                self::logMessage("{$servePrefix} Pterodactyl User ID: {$pterodactylUserId}");

                // Validate Pterodactyl resources
                $logger->info('Validating Pterodactyl resources...');
                if (!self::validatePterodactylResources($id, $servePrefix, $locationId, $eggId, $nestId, $pterodactylUserId, $logger, $chat)) {
                    $processDuration = round(microtime(true) - $processStartTime, 2);
                    $logger->error("Pterodactyl resource validation failed for server {$id}. Process took {$processDuration} seconds.");

                    return;
                }
                $logger->info('Pterodactyl resource validation passed');

                $logger->info('Starting Pterodactyl server creation...');
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

                $processDuration = round(microtime(true) - $processStartTime, 2);
                $logger->info("Server deployment completed successfully in {$processDuration} seconds");
                TimedTask::markRun('server-deploy', true, 'Server creation job completed for server ' . $id);
            }
        } catch (\Exception $e) {
            $processDuration = round(microtime(true) - $processStartTime, 2);
            $errorMsg = "Error creating server {$id}: " . $e->getMessage();
            $logger->error($errorMsg);
            $logger->error("Exception occurred after {$processDuration} seconds");
            $logger->error('Exception trace: ' . $e->getTraceAsString());
            $chat->sendOutputWithNewLine($servePrefix . '&cError creating server: ' . $e->getMessage());
            self::logMessage($servePrefix . '&cError creating server: ' . $e->getMessage());
            ServerQueue::updateStatus($id, 'failed');

            // Save failure logs
            ServerQueueLogs::logFailure($id, self::$logs, $e->getMessage());
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because ' . $e->getMessage());
        }
    }

    private static function validateResources($id, $servePrefix, $userUUID, $nest, $egg, $location, $logger, $chat)
    {
        $logger->info('Validating user existence...');
        self::logMessage("{$servePrefix} Validating user: {$userUUID}");

        // Check if user exists
        if (!User::exists(UserColumns::UUID, $userUUID)) {
            $errorMsg = 'User no longer exists: ' . $userUUID;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cUser no longer exists: ' . $userUUID);
            $chat->sendOutputWithNewLine($servePrefix . '&cUser no longer exists: ' . $userUUID);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because user no longer exists: ' . $userUUID);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('User validation passed');
        self::logMessage("{$servePrefix} &aUser validation passed");

        // Check if nest exists
        $logger->info('Validating nest existence...');
        self::logMessage("{$servePrefix} Validating nest: {$nest}");
        if (!EggCategories::exists($nest)) {
            $errorMsg = 'Nest no longer exists: ' . $nest;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cNest no longer exists: ' . $nest);
            $chat->sendOutputWithNewLine($servePrefix . '&cNest no longer exists: ' . $nest);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because nest no longer exists: ' . $nest);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('Nest validation passed');
        self::logMessage("{$servePrefix} &aNest validation passed");

        // Check if egg exists
        $logger->info('Validating egg existence...');
        self::logMessage("{$servePrefix} Validating egg: {$egg}");
        if (!Eggs::exists($egg)) {
            $errorMsg = 'Egg no longer exists: ' . $egg;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cEgg no longer exists: ' . $egg);
            $chat->sendOutputWithNewLine($servePrefix . '&cEgg no longer exists: ' . $egg);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because egg no longer exists: ' . $egg);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('Egg validation passed');
        self::logMessage("{$servePrefix} &aEgg validation passed");

        // Check if location exists
        $logger->info('Validating location existence...');
        self::logMessage("{$servePrefix} Validating location: {$location}");
        if (!Locations::exists($location)) {
            $errorMsg = 'Location no longer exists: ' . $location;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cLocation no longer exists: ' . $location);
            $chat->sendOutputWithNewLine($servePrefix . '&cLocation no longer exists: ' . $location);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because location no longer exists: ' . $location);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('Location validation passed');
        self::logMessage("{$servePrefix} &aLocation validation passed");
        self::logMessage("{$servePrefix} &aAll resource validations passed");

        return true;
    }

    private static function validatePterodactylResources($id, $servePrefix, $locationId, $eggId, $nestId, $pterodactylUserId, $logger, $chat)
    {
        $logger->info('Validating Pterodactyl egg existence...');
        self::logMessage("{$servePrefix} Validating Pterodactyl egg: {$eggId}");

        // Check if egg exists in Pterodactyl
        if (!\MythicalDash\Hooks\Pterodactyl\Admin\Eggs::doesEggExist($eggId)) {
            $errorMsg = 'Egg no longer exists in Pterodactyl: ' . $eggId;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cEgg no longer exists in Pterodactyl: ' . $eggId);
            $chat->sendOutputWithNewLine($servePrefix . '&cEgg no longer exists in Pterodactyl: ' . $eggId);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because egg no longer exists in Pterodactyl: ' . $eggId);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('Pterodactyl egg validation passed');
        self::logMessage("{$servePrefix} &aPterodactyl egg validation passed");

        // Check if location exists in Pterodactyl
        $logger->info('Validating Pterodactyl location existence...');
        self::logMessage("{$servePrefix} Validating Pterodactyl location: {$locationId}");
        if (!\MythicalDash\Hooks\Pterodactyl\Admin\Locations::doesLocationExist($locationId)) {
            $errorMsg = 'Location no longer exists in Pterodactyl: ' . $locationId;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cLocation no longer exists in Pterodactyl: ' . $locationId);
            $chat->sendOutputWithNewLine($servePrefix . '&cLocation no longer exists in Pterodactyl: ' . $locationId);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because location no longer exists in Pterodactyl: ' . $locationId);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('Pterodactyl location validation passed');
        self::logMessage("{$servePrefix} &aPterodactyl location validation passed");

        // Check if nest exists in Pterodactyl
        $logger->info('Validating Pterodactyl nest existence...');
        self::logMessage("{$servePrefix} Validating Pterodactyl nest: {$nestId}");
        if (!\MythicalDash\Hooks\Pterodactyl\Admin\Nests::doesNestExist($nestId)) {
            $errorMsg = 'Nest no longer exists in Pterodactyl: ' . $nestId;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cNest no longer exists in Pterodactyl: ' . $nestId);
            $chat->sendOutputWithNewLine($servePrefix . '&cNest no longer exists in Pterodactyl: ' . $nestId);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because nest no longer exists in Pterodactyl: ' . $nestId);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('Pterodactyl nest validation passed');
        self::logMessage("{$servePrefix} &aPterodactyl nest validation passed");

        // Check if user exists in Pterodactyl
        $logger->info('Validating Pterodactyl user existence...');
        self::logMessage("{$servePrefix} Validating Pterodactyl user: {$pterodactylUserId}");
        if (!\MythicalDash\Hooks\Pterodactyl\Admin\User::exists($pterodactylUserId)) {
            $errorMsg = 'User no longer exists in Pterodactyl: ' . $pterodactylUserId;
            $logger->error($errorMsg);
            self::logMessage($servePrefix . '&cUser no longer exists in Pterodactyl: ' . $pterodactylUserId);
            $chat->sendOutputWithNewLine($servePrefix . '&cUser no longer exists in Pterodactyl: ' . $pterodactylUserId);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because user no longer exists in Pterodactyl: ' . $pterodactylUserId);
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
        $logger->info('Pterodactyl user validation passed');
        self::logMessage("{$servePrefix} &aPterodactyl user validation passed");
        self::logMessage("{$servePrefix} &aAll Pterodactyl resource validations passed");

        return true;
    }

    private static function createPterodactylServer($app, $id, $servePrefix, $name, $pterodactylUserId, $eggId, $nestId, $ram, $disk, $cpu, $ports, $databases, $backups, $locationId, $description, $logger, $chat, $userUUID)
    {
        $chat->sendOutputWithNewLine($servePrefix . '&aCreating server...');
        self::logMessage($servePrefix . '&aCreating server...');
        $baseUrl = $app->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, 'https://pterodactyl.mythical.systems');
        $apiKey = $app->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, 'mythical');

        // Log configuration (without exposing full API key)
        $maskedApiKey = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
        self::logMessage($servePrefix . '&7Pterodactyl URL: ' . $baseUrl);
        self::logMessage($servePrefix . '&7API Key: ' . $maskedApiKey);
        $logger->info("Creating server {$id} - Pterodactyl URL: {$baseUrl}, API Key: {$maskedApiKey}");

        $servers = new ServersResource($baseUrl, $apiKey);
        $nests = new NestsResource($baseUrl, $apiKey);

        try {
            $creationStartTime = microtime(true);
            $chat->sendOutputWithNewLine($servePrefix . '&7Fetching egg information...');
            self::logMessage($servePrefix . "&7Fetching egg information (Nest: {$nestId}, Egg: {$eggId})...");
            $logger->info("Fetching egg information from Pterodactyl API - Nest: {$nestId}, Egg: {$eggId}");

            $eggFetchStart = microtime(true);
            $eggInfo = $nests->getEgg($nestId, $eggId);
            $eggFetchDuration = round(microtime(true) - $eggFetchStart, 2);
            $logger->info("Egg information fetched in {$eggFetchDuration} seconds");

            if (empty($eggInfo) || !isset($eggInfo['attributes'])) {
                $errorMsg = 'Failed to fetch egg information from Pterodactyl API. Response was empty or invalid.';
                $logger->error($errorMsg);
                self::logMessage($servePrefix . '&c' . $errorMsg);
                $chat->sendOutputWithNewLine($servePrefix . '&c' . $errorMsg);
                ServerQueue::updateStatus($id, 'failed');
                if (self::$logId) {
                    ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                    ServerQueueLogs::setPurge(self::$logId, true);
                } else {
                    ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
                }

                return false;
            }

            // Log egg information details
            $dockerImage = $eggInfo['attributes']['docker_image'] ?? 'N/A';
            $startup = $eggInfo['attributes']['startup'] ?? 'N/A';
            $logger->info("Egg Docker Image: {$dockerImage}");
            $logger->info("Egg Startup Command: {$startup}");
            self::logMessage("{$servePrefix} &7Docker Image: {$dockerImage}");
            self::logMessage("{$servePrefix} &7Startup: {$startup}");

            // Prepare environment variables
            $logger->info('Preparing environment variables...');
            $environment = [];
            if (isset($eggInfo['attributes']['relationships']['variables']['data'])) {
                $varCount = count($eggInfo['attributes']['relationships']['variables']['data']);
                $logger->info("Found {$varCount} environment variables to process");
                foreach ($eggInfo['attributes']['relationships']['variables']['data'] as $val) {
                    $attr = $val['attributes'];
                    $environment[$attr['env_variable']] = $attr['default_value'];
                }
                $logger->info("Prepared {$varCount} environment variables");
                self::logMessage("{$servePrefix} &7Prepared {$varCount} environment variables");
            } else {
                $logger->info('No environment variables found in egg data');
                self::logMessage("{$servePrefix} &7No environment variables found");
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

            // Log request payload (without sensitive data)
            $logPayload = $json;
            $logPayload['environment'] = '[REDACTED - ' . count($environment) . ' variables]';
            $logger->info("Server creation request payload for server {$id}: " . json_encode($logPayload));
            self::logMessage($servePrefix . '&7Sending server creation request to Pterodactyl API...');
            self::logMessage($servePrefix . '&7Payload summary:');
            self::logMessage($servePrefix . "&7  - Name: {$name}");
            self::logMessage($servePrefix . "&7  - User ID: {$pterodactylUserId}");
            self::logMessage($servePrefix . "&7  - Egg ID: {$eggId}");
            self::logMessage($servePrefix . "&7  - Nest ID: {$nestId}");
            self::logMessage($servePrefix . "&7  - Location ID: {$locationId}");
            self::logMessage($servePrefix . "&7  - Resources: RAM={$ram}MB, Disk={$disk}MB, CPU={$cpu}%");
            self::logMessage($servePrefix . "&7  - Features: Ports={$ports}, Databases={$databases}, Backups={$backups}");

            // Create the server
            $apiCallStart = microtime(true);
            $logger->info('Calling Pterodactyl API to create server...');
            $response = $servers->createServer($json);
            $apiCallDuration = round(microtime(true) - $apiCallStart, 2);
            $logger->info("Pterodactyl API call completed in {$apiCallDuration} seconds");
            self::logMessage($servePrefix . "&7API call completed in {$apiCallDuration} seconds");

            // Check if response is empty or invalid
            if (empty($response)) {
                $errorMsg = 'Pterodactyl API returned an empty response. This usually indicates: 1) Authentication failure (check API key), 2) Network/connection issue, 3) Pterodactyl API error. Check Pterodactyl logs for more details.';
                $logger->error($errorMsg);
                $logger->error('Request payload was: ' . json_encode($logPayload));
                self::logMessage($servePrefix . '&c' . $errorMsg);
                $chat->sendOutputWithNewLine($servePrefix . '&c' . $errorMsg);
                ServerQueue::updateStatus($id, 'failed');
                TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' - Pterodactyl API returned empty response');
                if (self::$logId) {
                    ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                    ServerQueueLogs::setPurge(self::$logId, true);
                } else {
                    ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
                }

                return false;
            }

            // Log full response for debugging
            $logger->debug("Pterodactyl API response for server {$id}: " . json_encode($response));

            if (isset($response['attributes']) && isset($response['attributes']['id'])) {
                $pterodactylServerId = $response['attributes']['id'];
                $successMsg = 'Server created successfully with ID: ' . $pterodactylServerId;
                $chat->sendOutputWithNewLine($servePrefix . '&a' . $successMsg);
                self::logMessage($servePrefix . '&a' . $successMsg);
                $logger->info("Pterodactyl server created with ID: {$pterodactylServerId}");

                // Log additional server details from response
                if (isset($response['attributes']['uuid'])) {
                    $logger->info('Server UUID: ' . $response['attributes']['uuid']);
                    self::logMessage($servePrefix . '&7Server UUID: ' . $response['attributes']['uuid']);
                }
                if (isset($response['attributes']['identifier'])) {
                    $logger->info('Server Identifier: ' . $response['attributes']['identifier']);
                    self::logMessage($servePrefix . '&7Server Identifier: ' . $response['attributes']['identifier']);
                }

                $logger->info('Storing server information in database...');
                self::logMessage($servePrefix . '&7Storing server information in database...');
                $dbCreateStart = microtime(true);
                $svID = Server::create($pterodactylServerId, $id, $userUUID);
                $dbCreateDuration = round(microtime(true) - $dbCreateStart, 2);
                $logger->info("Server record created in database with ID: {$svID} (took {$dbCreateDuration} seconds)");
                self::logMessage($servePrefix . "&aServer record created in database (ID: {$svID})");
                $logger->info('Checking server renewal settings...');
                $isRenewal = $app->getConfig()->getDBSetting(ConfigInterface::SERVER_RENEW_ENABLED, 'false');
                $logger->info("Server renewal enabled: {$isRenewal}");

                if ($isRenewal == 'true') {
                    $server_renew_days = (int) $app->getConfig()->getDBSetting(ConfigInterface::SERVER_RENEW_DAYS, 30);
                    $logger->info("Server renewal days: {$server_renew_days}");
                    self::logMessage($servePrefix . "&7Setting server expiration ({$server_renew_days} days)...");

                    // Get existing expiration date if it exists, otherwise use current time
                    $existingExpiration = Server::getExpirationTimestamp($svID);
                    // If no existing expiration or it's null, use current time
                    $baseTime = $existingExpiration ?: strtotime(date('Y-m-d H:i:s'));
                    $newExpiresAt = $baseTime + ($server_renew_days * 86400); // Convert days to seconds
                    $expirationDate = date('Y-m-d H:i:s', $newExpiresAt);

                    $logger->info("Server expiration set to: {$expirationDate}");
                    self::logMessage($servePrefix . "&aServer expiration set to: {$expirationDate}");
                    try {
                        // Update server expiration
                        if (!Server::update($svID, $newExpiresAt)) {
                            $failureMsg = 'Failed to update server expiration';
                            self::logMessage($servePrefix . '&c' . $failureMsg);
                            $logger->error($failureMsg);
                        } else {
                            $logger->info('Server expiration updated successfully');
                        }
                    } catch (\Exception $e) {
                        $errorMsg = 'Error updating server expiration: ' . $e->getMessage();
                        $logger->error($errorMsg);
                        $logger->error('Exception trace: ' . $e->getTraceAsString());
                        self::logMessage($servePrefix . '&c' . $errorMsg);
                        $chat->sendOutputWithNewLine($servePrefix . '&c' . $errorMsg);
                    }
                } else {
                    $logger->info('Server renewal is disabled, skipping expiration setting');
                    self::logMessage($servePrefix . '&7Server renewal disabled, skipping expiration');
                }

                $logger->info("Updating server queue status to 'completed'...");
                ServerQueue::updateStatus($id, 'completed');
                self::logMessage($servePrefix . '&aServer information stored in database');
                $chat->sendOutputWithNewLine($servePrefix . '&aServer information stored in database');

                // Update logs with completion status
                $logger->info('Saving final logs...');
                if (self::$logId) {
                    ServerQueueLogs::appendLogs(self::$logId, self::$logs);
                    $logger->info('Logs appended to log entry ID: ' . self::$logId);
                } else {
                    ServerQueueLogs::saveJobLogs($id, self::$logs);
                    $logger->info('New log entry created');
                }

                $totalDuration = round(microtime(true) - $creationStartTime, 2);
                $logger->info("Server creation completed successfully in {$totalDuration} seconds");
                self::logMessage($servePrefix . "&aServer creation completed in {$totalDuration} seconds");
                self::logMessage("{$servePrefix} &a========================================");

                return true;
            }
            // Response exists but doesn't have expected structure
            $responseStr = json_encode($response);
            $errorMsg = "Failed to create server {$id}: Pterodactyl API returned unexpected response structure. Response: " . $responseStr;
            $logger->error($errorMsg);
            $logger->error("Expected response structure: ['attributes' => ['id' => ...]], but got: " . $responseStr);
            $chat->sendOutputWithNewLine($servePrefix . '&cFailed to create server: Unexpected API response structure');
            self::logMessage($servePrefix . '&cFailed to create server: ' . $responseStr);
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' - Unexpected API response: ' . substr($responseStr, 0, 200));
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;

        } catch (\Exception $e) {
            $errorMsg = "Error during server creation for {$id}: " . $e->getMessage();
            $logger->error($errorMsg);
            $logger->error('Exception trace: ' . $e->getTraceAsString());
            $chat->sendOutputWithNewLine($servePrefix . '&cError during server creation: ' . $e->getMessage());
            self::logMessage($servePrefix . '&cError during server creation: ' . $e->getMessage());
            ServerQueue::updateStatus($id, 'failed');
            TimedTask::markRun('server-deploy', false, 'Server creation job failed for server ' . $id . ' because error during server creation: ' . $e->getMessage());
            // Save failure logs
            if (self::$logId) {
                ServerQueueLogs::appendLogs(self::$logId, 'ERROR: ' . $errorMsg);
                ServerQueueLogs::setPurge(self::$logId, true);
            } else {
                ServerQueueLogs::logFailure($id, self::$logs, $errorMsg);
            }

            return false;
        }
    }

    /**
     * Add a message to the logs array and update the database if needed.
     *
     * @param string $message The message to log
     * @param bool $updateDb Whether to update the logs in the database
     */
    private static function logMessage(string $message, bool $updateDb = false)
    {
        self::$logs[] = $message;

        // Update the database if requested and we have a log ID
        if ($updateDb && self::$logId && self::$currentBuildId) {
            ServerQueueLogs::appendLogs(self::$logId, $message);
        }
    }
}
