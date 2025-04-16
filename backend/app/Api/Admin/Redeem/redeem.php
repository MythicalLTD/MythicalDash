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
use MythicalDash\Chat\User\Can;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\Redeem\RedeemCoins;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\RedeemEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// Get all redeem codes
$router->get('/api/admin/redeem/codes', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $codes = RedeemCoins::getList();
        $appInstance->OK('Redeem codes retrieved successfully.', [
            'codes' => $codes,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Get a specific redeem code
$router->get('/api/admin/redeem/code/(.*)', function ($codeId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (empty($codeId)) {
            $appInstance->BadRequest('Code ID is required', ['error_code' => 'CODE_ID_REQUIRED']);
        }
        if (RedeemCoins::exists($codeId)) {
            $code = RedeemCoins::get($codeId);
            $appInstance->OK('Redeem code retrieved successfully.', [
                'code' => $code,
            ]);
        } else {
            $appInstance->NotFound('Redeem code not found', ['error_code' => 'CODE_NOT_FOUND']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Create a new redeem code
$router->post('/api/admin/redeem/code/create', function (): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (!isset($_POST['code']) || empty($_POST['code'])) {
            $appInstance->BadRequest('Code is required', ['error_code' => 'CODE_REQUIRED']);
        }
        if (!isset($_POST['coins']) || empty($_POST['coins'])) {
            $appInstance->BadRequest('Coins amount is required', ['error_code' => 'COINS_REQUIRED']);
        }

        $code = $_POST['code'];
        $coins = (int) $_POST['coins'];
        $uses = isset($_POST['uses']) ? (int) $_POST['uses'] : 1;
        $enabled = isset($_POST['enabled']) ? $_POST['enabled'] === 'true' : false;

        if (RedeemCoins::existsByCode($code)) {
            $appInstance->BadRequest('Redeem code already exists', ['error_code' => 'CODE_ALREADY_EXISTS']);

            return;
        }

        $result = RedeemCoins::create($code, $coins, $uses, $enabled);
        if ($result) {
            // Add admin activity log
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_created_redeem_code,
                CloudFlareRealIP::getRealIP(),
                "Created redeem code: $code"
            );
            global $eventManager;
            $eventManager->emit(RedeemEvent::onRedeemCreate(), [
                'code' => $code,
                'coins' => $coins,
                'uses' => $uses,
                'enabled' => $enabled,
            ]);
            $appInstance->OK('Redeem code created successfully.', [
                'id' => $result,
            ]);
        } else {
            $appInstance->InternalServerError('Failed to create redeem code', ['error_code' => 'CREATE_CODE_FAILED']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Update a redeem code
$router->post('/api/admin/redeem/code/(.*)/update', function ($codeId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    global $eventManager;
    $session = new Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (empty($codeId)) {
            $appInstance->BadRequest('Code ID is required', ['error_code' => 'CODE_ID_REQUIRED']);
        }
        if (!RedeemCoins::exists($codeId)) {
            $appInstance->NotFound('Redeem code not found', ['error_code' => 'CODE_NOT_FOUND']);

            return;
        }

        if (!isset($_POST['code']) || empty($_POST['code'])) {
            $appInstance->BadRequest('Code is required', ['error_code' => 'CODE_REQUIRED']);
        }
        if (!isset($_POST['coins']) || empty($_POST['coins'])) {
            $appInstance->BadRequest('Coins amount is required', ['error_code' => 'COINS_REQUIRED']);
        }

        $code = $_POST['code'];
        $coins = (int) $_POST['coins'];
        $uses = isset($_POST['uses']) ? (int) $_POST['uses'] : 1;
        $enabled = isset($_POST['enabled']) ? $_POST['enabled'] === 'true' : false;

        // Check if code exists but is not the current one being updated
        $existingCode = RedeemCoins::get($codeId);
        if ($existingCode['code'] !== $code && RedeemCoins::existsByCode($code)) {
            $appInstance->BadRequest('Redeem code already exists', ['error_code' => 'CODE_ALREADY_EXISTS']);

            return;
        }

        if (RedeemCoins::update($codeId, $code, $coins, $uses, $enabled)) {
            // Add admin activity log
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_updated_redeem_code,
                CloudFlareRealIP::getRealIP(),
                "Updated redeem code: $code"
            );
            $eventManager->emit(RedeemEvent::onRedeemUpdate(), [
                'code' => $code,
                'coins' => $coins,
                'uses' => $uses,
                'enabled' => $enabled,
            ]);
            $appInstance->OK('Redeem code updated successfully.', []);
        } else {
            $appInstance->InternalServerError('Failed to update redeem code', ['error_code' => 'UPDATE_CODE_FAILED']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Delete a redeem code
$router->post('/api/admin/redeem/code/(.*)/delete', function ($codeId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (empty($codeId)) {
            $appInstance->BadRequest('Code ID is required', ['error_code' => 'CODE_ID_REQUIRED']);
        }
        if (!RedeemCoins::exists($codeId)) {
            $appInstance->NotFound('Redeem code not found', ['error_code' => 'CODE_NOT_FOUND']);

            return;
        }

        $code = RedeemCoins::get($codeId);
        if (RedeemCoins::delete($codeId)) {
            // Add admin activity log
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_deleted_redeem_code,
                CloudFlareRealIP::getRealIP(),
                "Deleted redeem code: {$code['code']}"
            );
            global $eventManager;
            $eventManager->emit(RedeemEvent::onRedeemDelete(), [
                'code' => $code['code'],
            ]);
            $appInstance->OK('Redeem code deleted successfully.', []);
        } else {
            $appInstance->InternalServerError('Failed to delete redeem code', ['error_code' => 'DELETE_CODE_FAILED']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
