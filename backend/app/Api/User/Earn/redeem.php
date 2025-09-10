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
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\Redeem\RedeemCoins;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\RedeemEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// User endpoint to redeem a code
$router->post('/api/user/earn/redeem', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    if (!$appInstance->getConfig()->getDBSetting(ConfigInterface::CODE_REDEMPTION_ENABLED, false)) {
        $appInstance->BadRequest('Code redemption is not enabled', ['error_code' => 'CODE_REDEMPTION_NOT_ENABLED']);

        return;
    }
    if (!isset($_POST['code']) || empty($_POST['code'])) {
        $appInstance->BadRequest('Redeem code is required', ['error_code' => 'CODE_REQUIRED']);

        return;
    }
    global $eventManager;

    $code = $_POST['code'];

    // Validate code atomically (prevents race conditions)
    $codeValidation = RedeemCoins::validateCodeAtomic($code, $session->getInfo(UserColumns::UUID, false));
    if (!$codeValidation) {
        $appInstance->BadRequest('Invalid redeem code', ['error_code' => 'INVALID_CODE']);
        $eventManager->emit(RedeemEvent::onRedeemFailed(), [
            'code' => $code,
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);

        return;
    }

    // Check if code has uses left
    if ($codeValidation['uses_left'] <= 0) {
        $appInstance->BadRequest('This code has reached its usage limit', ['error_code' => 'CODE_DEPLETED']);
        $eventManager->emit(RedeemEvent::onRedeemAlreadyRedeemed(), [
            'code' => $code,
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);

        return;
    }

    // Check if user has already redeemed this code
    if ($codeValidation['already_redeemed']) {
        $appInstance->BadRequest('This code has already been redeemed', ['error_code' => 'CODE_ALREADY_REDEEMED']);
        $eventManager->emit(RedeemEvent::onRedeemAlreadyRedeemed(), [
            'code' => $code,
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);

        return;
    }

    $coinsToAdd = $codeValidation['coins'];

    // Process redemption atomically (prevents race conditions)
    try {
        // Redeem the code atomically (this handles all the database operations in one transaction)
        $redemptionResult = RedeemCoins::redeemCodeAtomic($code, $session->getInfo(UserColumns::UUID, false));

        if (!$redemptionResult) {
            $appInstance->BadRequest('Failed to redeem code', ['error_code' => 'REDEMPTION_FAILED']);

            return;
        }

        // Add credits atomically
        if (!$session->addCreditsAtomic($coinsToAdd)) {
            // If adding credits failed, we need to log this critical error
            // The code was already redeemed, so we can't rollback easily
            $appInstance->BadRequest('Failed to add credits', ['error_code' => 'CREDIT_ADDITION_FAILED']);

            return;
        }

        // Get the new credit balance for response
        $newCredits = $session->getInfo(UserColumns::CREDITS, false);

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
    } catch (Exception $e) {
        $appInstance->BadRequest('Failed to process redemption', [
            'error_code' => 'REDEMPTION_FAILED',
            'message' => $e->getMessage(),
        ]);
    }
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
