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
use MythicalDash\Chat\Database;
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Roles;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Nodes;
use MythicalDash\Hooks\Pterodactyl\Admin\Servers;
use MythicalDash\Plugins\Events\Events\UserEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->post('/api/user/session/info/update', function (): void {
    App::init();
    global $eventManager;
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    try {
        if (!isset($_POST['first_name']) && $_POST['first_name'] == '') {
            $appInstance->BadRequest('First name is missing!', ['error_code' => 'FIRST_NAME_MISSING']);
        }
        if (!isset($_POST['last_name']) && $_POST['last_name'] == '') {
            $appInstance->BadRequest('Last name is missing!', ['error_code' => 'LAST_NAME_MISSING']);
        }
        if (!isset($_POST['email']) && $_POST['email'] == '') {
            $appInstance->BadRequest('Email is missing!', ['error_code' => 'EMAIL_MISSING']);
        }
        if (!isset($_POST['avatar']) && $_POST['avatar'] == '') {
            $appInstance->BadRequest('Avatar is missing!', ['error_code' => 'AVATAR_MISSING']);
        }
        if (!isset($_POST['background']) && $_POST['background'] == '') {
            $appInstance->BadRequest('Background is missing!', ['error_code' => 'BACKGROUND_MISSING']);
        }

        if ($_POST['email'] != $session->getInfo(UserColumns::EMAIL, false) && User::exists(UserColumns::EMAIL, $_POST['email'])) {
            $appInstance->BadRequest('Email already exists!', ['error_code' => 'EMAIL_EXISTS']);
        }

        $session->setInfo(UserColumns::FIRST_NAME, $_POST['first_name'], true);
        $session->setInfo(UserColumns::LAST_NAME, $_POST['last_name'], true);
        $session->setInfo(UserColumns::EMAIL, $_POST['email'], false);
        $session->setInfo(UserColumns::AVATAR, $_POST['avatar'], false);
        $session->setInfo(UserColumns::BACKGROUND, $_POST['background'], false);
        $eventManager->emit(UserEvent::onUserUpdate(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$user_update,
            CloudFlareRealIP::getRealIP()
        );
        $appInstance->OK('User info updated successfully!', []);
    } catch (Exception $e) {
        $appInstance->getLogger()->error('Failed to update user info! ' . $e->getMessage());
        $appInstance->BadRequest('Bad Request', ['error_code' => 'DB_ERROR', 'error' => $e->getMessage()]);
    }
});

$router->add('/api/user/session/apiKey/reset', function (): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $token = 'mythicaldash_clientapi_' . App::getInstance(true)->encrypt(date('Y-m-d H:i:s') . 'MythicalDash' . random_bytes(16) . base64_encode($appInstance->generateCode()));
    $session->setInfo(UserColumns::ACCOUNT_TOKEN, $token, false);
    $eventManager->emit(UserEvent::resetApiKey(), [$token]);
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$user_reset_api_key,
        CloudFlareRealIP::getRealIP()
    );
    setcookie('user_token', $token, time() + (86400 * 30), '/');
    $appInstance->OK('API KEY Reset!', ['api_key' => $token]);
});

$router->add('/api/user/session/newPin', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    global $eventManager;
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $pin = $appInstance->generatePin();
    try {
        $session->setInfo(UserColumns::SUPPORT_PIN, $pin, false);
        $eventManager->emit(UserEvent::newSupportPin(), [$pin]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$user_new_support_pin,
            CloudFlareRealIP::getRealIP()
        );

        $appInstance->OK('Support pin updated successfully!', ['pin' => $pin]);
    } catch (Exception $e) {
        $appInstance->getLogger()->error('Failed to generate new pin: ' . $e->getMessage());
        $appInstance->BadRequest('Bad Request', ['error_code' => 'DB_ERROR', 'error' => $e->getMessage()]);
    }
});

$router->get('/api/user/session', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    try {
        $stats_tickets = Database::getTableColumnCount('mythicaldash_tickets', [
            'user' => User::getInfo($accountToken, UserColumns::UUID, false),
        ]);

        $stats_servers = Database::getTableColumnCount('mythicaldash_servers', [
            'user' => User::getInfo($accountToken, UserColumns::UUID, false),
        ]);

        $columns = [
            UserColumns::USERNAME,
            UserColumns::EMAIL,
            UserColumns::VERIFIED,
            UserColumns::SUPPORT_PIN,
            UserColumns::BANNED,
            UserColumns::TWO_FA_BLOCKED,
            UserColumns::TWO_FA_ENABLED,
            UserColumns::TWO_FA_KEY,
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
            UserColumns::AVATAR,
            UserColumns::CREDITS,
            UserColumns::UUID,
            UserColumns::ROLE_ID,
            UserColumns::FIRST_IP,
            UserColumns::LAST_IP,
            UserColumns::DELETED,
            UserColumns::LAST_SEEN,
            UserColumns::FIRST_SEEN,
            UserColumns::BACKGROUND,
            UserColumns::MINUTES_AFK,
            UserColumns::LAST_SEEN_AFK,
            UserColumns::DISK_LIMIT,
            UserColumns::MEMORY_LIMIT,
            UserColumns::CPU_LIMIT,
            UserColumns::SERVER_LIMIT,
            UserColumns::BACKUP_LIMIT,
            UserColumns::DATABASE_LIMIT,
            UserColumns::ALLOCATION_LIMIT,
            UserColumns::PTERODACTYL_USER_ID,

            UserColumns::DISCORD_LINKED,
            UserColumns::DISCORD_USERNAME,
            UserColumns::DISCORD_EMAIL,
            UserColumns::DISCORD_ID,

            UserColumns::GITHUB_LINKED,
            UserColumns::GITHUB_USERNAME,
            UserColumns::GITHUB_EMAIL,
            UserColumns::GITHUB_ID,

        ];

        $info = User::getInfoArray($accountToken, $columns, [
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
            UserColumns::TWO_FA_KEY,
        ]);
        $info['role_name'] = Roles::getUserRoleName($info[UserColumns::UUID]);
        $info['role_real_name'] = strtolower($info['role_name']);

        $appInstance->OK('Account token is valid', [
            'user_info' => $info,
            'stats' => [
                'tickets' => $stats_tickets,
                'servers' => $stats_servers,
            ],
        ]);

    } catch (Exception $e) {
        $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_ACCOUNT_TOKEN', 'error' => $e->getMessage()]);
    }

});

$router->get('/api/user/session/activities', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    $appInstance->OK('User activities', [
        'activities' => UserActivities::get(User::getInfo($accountToken, UserColumns::UUID, false)),
    ]);
});

$router->get('/api/user/session/pterodactyl/resources', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    $pterodactylUserId = User::getInfo($accountToken, UserColumns::PTERODACTYL_USER_ID, false);

    $resources = Servers::getUserTotalResourcesUsage($pterodactylUserId);

    $appInstance->OK('User resources', [
        'resources' => $resources,
    ]);
});

$router->get('/api/user/session/servers', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    $pterodactylUserId = User::getInfo($accountToken, UserColumns::PTERODACTYL_USER_ID, false);

    $servers = Servers::getUserServersList($pterodactylUserId);
    foreach ($servers as &$server) {
        $nodeId = $server['node'];
        $locationId = Nodes::getLocationIdFromNode($nodeId);
        $location = Locations::getLocationByPterodactylLocationId($locationId);
        $server['location'] = $location;

        $eggId = $server['egg'];
        $egg = Eggs::getByPterodactylEggId($eggId);
        $server['service'] = $egg;

        $nestId = $server['nest'];
        $nest = MythicalDash\Chat\Eggs\EggCategories::getByPterodactylNestId($nestId);
        $server['category'] = $nest;
    }
    unset($server); // Unset the reference to avoid potential issues

    $appInstance->OK('User servers', [
        'servers' => $servers,
    ]);
});
