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

use MythicalDash\App;
use MythicalDash\Permissions;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\Redeem\RedeemCoins;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Plugins\Events\Events\RedeemEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// Get all redeem codes
$router->get('/api/admin/redeem/codes', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDEEM_CODES_LIST, $session);
    $codes = RedeemCoins::getList();
    $appInstance->OK('Redeem codes retrieved successfully.', [
        'codes' => $codes,
    ]);
});

// Get a specific redeem code
$router->get('/api/admin/redeem/code/(.*)', function ($codeId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDEEM_CODES_LIST, $session);
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
});

// Create a new redeem code
$router->post('/api/admin/redeem/code/create', function (): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDEEM_CODES_CREATE, $session);
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
});

// Update a redeem code
$router->post('/api/admin/redeem/code/(.*)/update', function ($codeId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    global $eventManager;
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDEEM_CODES_EDIT, $session);
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
});

// Delete a redeem code
$router->post('/api/admin/redeem/code/(.*)/delete', function ($codeId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDEEM_CODES_DELETE, $session);
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
});
