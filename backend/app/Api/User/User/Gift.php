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
use MythicalDash\Chat\User\UserLock;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->post('/api/user/gift', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $config = $appInstance->getConfig();

    if (!$config->getDBSetting(ConfigInterface::ALLOW_COINS_SHARING, 'false')) {
        $appInstance->BadRequest('Coins sharing is not enabled', ['error_code' => 'COINS_SHARE_NOT_ENABLED']);

        return;
    }

    $s = new Session($appInstance);

    if (isset($_POST['coins']) && isset($_POST['recipient_uuid'])) {
        $coins = (int) $_POST['coins'];
        $recipientUuid = $_POST['recipient_uuid'];
        $senderUuid = $s->getInfo(UserColumns::UUID, false);

        if (!is_numeric($coins) || $coins <= 0) {
            $appInstance->BadRequest('Invalid coins amount!', ['error_code' => 'INVALID_COINS_AMOUNT']);
        }

        if (!User::exists(UserColumns::UUID, $recipientUuid)) {
            $appInstance->BadRequest('Recipient user not found!', ['error_code' => 'RECIPIENT_USER_NOT_FOUND']);
        }

        if ($senderUuid === $recipientUuid) {
            $appInstance->BadRequest('You cannot gift coins to yourself!', ['error_code' => 'CANNOT_GIFT_SELF']);
        }

        $fee = (int) $config->getDBSetting(ConfigInterface::COINS_SHARE_FEE, 10);
        $feeAmount = ($coins * $fee) / 100;
        $coinsAfterFee = $coins + $feeAmount;

        $minAmount = $config->getDBSetting(ConfigInterface::COINS_SHARE_MIN_AMOUNT, 1);
        $maxAmount = $config->getDBSetting(ConfigInterface::COINS_SHARE_MAX_AMOUNT, 1000);

        if ($coins < $minAmount) {
            $appInstance->BadRequest('Amount is too low! Minimum amount is ' . $minAmount . ' coins', ['error_code' => 'COINS_AMOUNT_TOO_LOW']);
        }
        if ($coins > $maxAmount) {
            $appInstance->BadRequest('Amount is too high! Maximum amount is ' . $maxAmount . ' coins', ['error_code' => 'COINS_AMOUNT_TOO_HIGH']);
        }

        // Execute gift operation with user lock protection to prevent race conditions
        try {
            $result = UserLock::executeWithLock($senderUuid, function () use ($s, $coinsAfterFee, $recipientUuid, $coins, $feeAmount) {
                // Re-check balance after acquiring lock
                $currentCredits = (int) $s->getInfo(UserColumns::CREDITS, false);
                if ($currentCredits < $coinsAfterFee) {
                    throw new Exception('Insufficient balance');
                }

                // Process the gift
                $s->removeCredits((int) intval($coinsAfterFee));
                User::addCredits($recipientUuid, (int) intval($coins));

                return [
                    'success' => true,
                    'coins_gifted' => $coins,
                    'fee_paid' => $feeAmount,
                    'total_deducted' => $coinsAfterFee,
                ];
            });

            // If we get here, the gift was successful
            UserActivities::add(
                $senderUuid,
                UserActivitiesTypes::$user_gifted_coins,
                CloudFlareRealIP::getRealIP(),
                "Gifted {$result['coins_gifted']} coins to user $recipientUuid (Fee: {$result['fee_paid']} coins)"
            );

            $appInstance->OK('Coins gifted successfully!', [
                'coins_gifted' => $result['coins_gifted'],
                'fee_paid' => $result['fee_paid'],
                'total_deducted' => $result['total_deducted'],
            ]);

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            if ($errorMessage === 'Insufficient balance') {
                $currentCredits = (int) $s->getInfo(UserColumns::CREDITS, false);
                $appInstance->BadRequest('Insufficient balance! You need ' . $coinsAfterFee . ' coins (including ' . $fee . '% fee)', ['error_code' => 'INSUFFICIENT_BALANCE']);
            } else {
                $appInstance->BadRequest('Failed to process gift', [
                    'error_code' => 'GIFT_FAILED',
                    'message' => $errorMessage,
                ]);
            }
        }
    } else {
        $appInstance->BadRequest('Invalid request!', ['error_code' => 'INVALID_REQUEST']);
    }
});
