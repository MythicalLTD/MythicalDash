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
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\User\UserLock;
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
    $userUuid = $session->getInfo(UserColumns::UUID, false);

    // Execute code redemption with user lock protection to prevent race conditions
    try {
        $result = UserLock::executeWithLock($userUuid, function () use ($code, $session) {
            // Check if code exists
            if (!RedeemCoins::existsByCode($code)) {
                throw new Exception('Invalid redeem code');
            }

            $codeDB = RedeemCoins::getByCode($code);

            $coinsToAdd = $codeDB['coins'];
            $usesLeft = $codeDB['uses'];

            if ($usesLeft <= 0) {
                throw new Exception('This code has reached its usage limit');
            }

            if (RedeemRedeems::isCodeRedeemed($codeDB['id'], $session->getInfo(UserColumns::UUID, false))) {
                throw new Exception('This code has already been redeemed');
            }

            // Process the redemption
            RedeemRedeems::redeemCode($codeDB['id'], $session->getInfo(UserColumns::UUID, false));
            $newCredits = $session->getInfo(UserColumns::CREDITS, false) + $coinsToAdd;
            $session->addCredits((int) intval($coinsToAdd));
            RedeemCoins::removeUsage($codeDB['id']);

            return [
                'success' => true,
                'coins_added' => $coinsToAdd,
                'total_credits' => $newCredits,
            ];
        });

        // If we get here, the redemption was successful
        UserActivities::add(
            $userUuid,
            UserActivitiesTypes::$user_redeemed_code,
            CloudFlareRealIP::getRealIP(),
            "Redeemed code: $code for {$result['coins_added']} credits"
        );

        $eventManager->emit(RedeemEvent::onRedeemSuccess(), [
            'code' => $code,
            'user' => $userUuid,
            'credits_added' => $result['coins_added'],
        ]);

        $appInstance->OK('Code redeemed successfully', [
            'credits_added' => $result['coins_added'],
            'total_credits' => $result['total_credits'],
        ]);

    } catch (Exception $e) {
        $errorMessage = $e->getMessage();

        // Handle specific error cases
        if ($errorMessage === 'Invalid redeem code') {
            $eventManager->emit(RedeemEvent::onRedeemFailed(), [
                'code' => $code,
                'user' => $userUuid,
            ]);
            $appInstance->BadRequest('Invalid redeem code', ['error_code' => 'INVALID_CODE']);
        } elseif ($errorMessage === 'This code has reached its usage limit') {
            $eventManager->emit(RedeemEvent::onRedeemAlreadyRedeemed(), [
                'code' => $code,
                'user' => $userUuid,
            ]);
            $appInstance->BadRequest('This code has reached its usage limit', ['error_code' => 'CODE_DEPLETED']);
        } elseif ($errorMessage === 'This code has already been redeemed') {
            $eventManager->emit(RedeemEvent::onRedeemAlreadyRedeemed(), [
                'code' => $code,
                'user' => $userUuid,
            ]);
            $appInstance->BadRequest('This code has already been redeemed', ['error_code' => 'CODE_ALREADY_REDEEMED']);
        } else {
            $appInstance->BadRequest('Failed to redeem code', [
                'error_code' => 'REDEEM_FAILED',
                'message' => $errorMessage,
            ]);
        }
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
