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
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Database\Database;
use MythicalDash\Chat\Redeem\RedeemCoins;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// User endpoint to redeem a code
$router->post('/api/user/redeem', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    if (!isset($_POST['code']) || empty($_POST['code'])) {
        $appInstance->BadRequest('Redeem code is required', ['error_code' => 'CODE_REQUIRED']);

        return;
    }

    $code = $_POST['code'];

    // Check if code exists
    if (!RedeemCoins::existsByCode($code)) {
        $appInstance->BadRequest('Invalid redeem code', ['error_code' => 'INVALID_CODE']);

        return;
    }

    // Get code details by querying the database
    $dbConn = MythicalDash\Chat\Database::getPdoConnection();
    $stmt = $dbConn->prepare('SELECT * FROM ' . RedeemCoins::getTableName() . ' WHERE code = :code AND deleted = "false"');
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    $codeDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if code is enabled
    if ($codeDetails['enabled'] !== 'true') {
        $appInstance->BadRequest('This code is not currently active', ['error_code' => 'CODE_DISABLED']);

        return;
    }

    // Check if uses left
    if ((int) $codeDetails['uses'] <= 0) {
        $appInstance->BadRequest('This code has reached its usage limit', ['error_code' => 'CODE_DEPLETED']);

        return;
    }

    // Add credits to user account
    $currentCredits = (int) $session->getInfo(UserColumns::CREDITS, false);
    $coinsToAdd = (int) $codeDetails['coins'];
    $newCredits = $currentCredits + $coinsToAdd;

    // Update user credits
    if (!User::updateInfo($session->SESSION_KEY, UserColumns::CREDITS, (string) $newCredits, false)) {
        $appInstance->InternalServerError('Failed to add credits to your account', ['error_code' => 'CREDITS_UPDATE_FAILED']);

        return;
    }

    // Decrement code uses
    $newUses = (int) $codeDetails['uses'] - 1;
    $dbConn = MythicalDash\Chat\Database::getPdoConnection();
    $stmt = $dbConn->prepare('UPDATE ' . RedeemCoins::getTableName() . ' SET uses = :uses WHERE id = :id');
    $stmt->bindParam(':uses', $newUses);
    $stmt->bindParam(':id', $codeDetails['id']);
    $stmt->execute();

    // Add user activity log
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$user_redeemed_code,
        CloudFlareRealIP::getRealIP(),
        "Redeemed code: $code for $coinsToAdd credits"
    );

    $appInstance->OK('Code redeemed successfully', [
        'credits_added' => $coinsToAdd,
        'total_credits' => $newCredits,
    ]);
});

// Redeem code check - validates a code without redeeming it
$router->get('/api/user/redeem/check/(.*)', function ($code): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    new Session($appInstance); // Just to validate user session

    if (empty($code)) {
        $appInstance->BadRequest('Redeem code is required', ['error_code' => 'CODE_REQUIRED']);

        return;
    }

    // Check if code exists
    if (!RedeemCoins::existsByCode($code)) {
        $appInstance->NotFound('Invalid redeem code', ['error_code' => 'INVALID_CODE']);

        return;
    }

    // Get code details
    $dbConn = MythicalDash\Chat\Database::getPdoConnection();
    $stmt = $dbConn->prepare('SELECT * FROM ' . RedeemCoins::getTableName() . ' WHERE code = :code AND deleted = "false"');
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    $codeDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if code is enabled
    if ($codeDetails['enabled'] !== 'true') {
        $appInstance->BadRequest('This code is not currently active', ['error_code' => 'CODE_DISABLED']);

        return;
    }

    // Check if uses left
    if ((int) $codeDetails['uses'] <= 0) {
        $appInstance->BadRequest('This code has reached its usage limit', ['error_code' => 'CODE_DEPLETED']);

        return;
    }

    $appInstance->OK('Valid redeem code', [
        'code' => $code,
        'coins' => (int) $codeDetails['coins'],
        'uses_left' => (int) $codeDetails['uses'],
    ]);
});
