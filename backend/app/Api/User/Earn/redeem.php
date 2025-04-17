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
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\Redeem\RedeemCoins;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Chat\Redeem\RedeemRedeems;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\RedeemEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// User endpoint to redeem a code
$router->post('/api/user/earn/redeem', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    if (!$appInstance->getConfig()->getSetting(ConfigInterface::CODE_REDEMPTION_ENABLED, false)) {
        $appInstance->BadRequest('Code redemption is not enabled', ['error_code' => 'CODE_REDEMPTION_NOT_ENABLED']);

        return;
    }
    if (!isset($_POST['code']) || empty($_POST['code'])) {
        $appInstance->BadRequest('Redeem code is required', ['error_code' => 'CODE_REQUIRED']);

        return;
    }
    global $eventManager;

    $code = $_POST['code'];

    // Check if code exists
    if (!RedeemCoins::existsByCode($code)) {
        $appInstance->BadRequest('Invalid redeem code', ['error_code' => 'INVALID_CODE']);
        $eventManager->emit(RedeemEvent::onRedeemFailed(), [
            'code' => $code,
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);

        return;
    }

    $codeDB = RedeemCoins::getByCode($code);

    $coinsToAdd = $codeDB['coins'];
    $usesLeft = $codeDB['uses'];

    if ($usesLeft <= 0) {
        $appInstance->BadRequest('This code has reached its usage limit', ['error_code' => 'CODE_DEPLETED']);
        $eventManager->emit(RedeemEvent::onRedeemAlreadyRedeemed(), [
            'code' => $code,
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);

        return;
    }

    if (RedeemRedeems::isCodeRedeemed($codeDB['id'], $session->getInfo(UserColumns::UUID, false))) {
        $appInstance->BadRequest('This code has already been redeemed', ['error_code' => 'CODE_ALREADY_REDEEMED']);
        $eventManager->emit(RedeemEvent::onRedeemAlreadyRedeemed(), [
            'code' => $code,
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);

        return;
    }

    RedeemRedeems::redeemCode($codeDB['id'], $session->getInfo(UserColumns::UUID, false));
    $newCredits = $session->getInfo(UserColumns::CREDITS, false) + $coinsToAdd;
    $session->setInfo(UserColumns::CREDITS, $newCredits, false);
    RedeemCoins::removeUsage($codeDB['id']);

    // Add user activity log
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$user_redeemed_code,
        CloudFlareRealIP::getRealIP(),
        "Redeemed code: $code for $coinsToAdd credits"
    );
    $eventManager->emit(RedeemEvent::onRedeemSuccess(), [
        'code' => $code,
        'user' => $session->getInfo(UserColumns::UUID, false),
        'credits_added' => $coinsToAdd,
    ]);
    $appInstance->OK('Code redeemed successfully', [
        'credits_added' => $coinsToAdd,
        'total_credits' => $newCredits,
    ]);
});

// Redeem code check - validates a code without redeeming it
$router->get('/api/user/earn/redeem/check/(.*)', function ($code): void {
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
