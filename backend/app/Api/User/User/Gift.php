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
use MythicalDash\Chat\columns\UserColumns;

$router->post('/api/user/gift', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $config = $appInstance->getConfig();

    if ($config->getDBSetting(ConfigInterface::ALLOW_COINS_SHARING, 'false') !== 'true') {
        $appInstance->BadRequest('Coins sharing is not enabled!', ['error_code' => 'COINS_SHARING_NOT_ENABLED']);
    }

    $s = new Session($appInstance);

    if (isset($_POST['coins']) && isset($_POST['recipient_uuid'])) {
        $coins = (int) $_POST['coins'];
        $recipientUuid = $_POST['recipient_uuid'];

        if (!is_numeric($coins) || $coins <= 0) {
            $appInstance->BadRequest('Invalid coins amount!', ['error_code' => 'INVALID_COINS_AMOUNT']);
        }

        if (!User::exists(UserColumns::UUID, $recipientUuid)) {
            $appInstance->BadRequest('Recipient user not found!', ['error_code' => 'RECIPIENT_USER_NOT_FOUND']);
        }

        $fee = (int) $config->getDBSetting(ConfigInterface::COINS_SHARE_FEE, 10);
        $feeAmount = ($coins * $fee) / 100;
        $coinsAfterFee = $coins + $feeAmount;
        if ($coinsAfterFee > $s->getInfo(UserColumns::CREDITS, false)) {
            $appInstance->BadRequest('Insufficient balance! You need ' . $coinsAfterFee . ' coins (including ' . $fee . '% fee)', ['error_code' => 'INSUFFICIENT_BALANCE']);
        }

        $minAmount = $config->getDBSetting(ConfigInterface::COINS_SHARE_MIN_AMOUNT, 1);
        $maxAmount = $config->getDBSetting(ConfigInterface::COINS_SHARE_MAX_AMOUNT, 1000);

        if ($coins < $minAmount) {
            $appInstance->BadRequest('Amount is too low! Minimum amount is ' . $minAmount . ' coins', ['error_code' => 'COINS_AMOUNT_TOO_LOW']);
        }
        if ($coins > $maxAmount) {
            $appInstance->BadRequest('Amount is too high! Maximum amount is ' . $maxAmount . ' coins', ['error_code' => 'COINS_AMOUNT_TOO_HIGH']);
        }

        // Process gift atomically to prevent race conditions
        try {
            // First, remove credits from sender atomically
            if (!$s->removeCreditsAtomic((int) intval($coinsAfterFee))) {
                $appInstance->BadRequest('Failed to process gift - insufficient balance or operation failed', ['error_code' => 'GIFT_PROCESSING_FAILED']);

                return;
            }

            // Then, add credits to recipient atomically
            $recipientToken = User::getTokenFromUUID($recipientUuid);
            if (!$recipientToken) {
                $appInstance->BadRequest('Failed to get recipient token', ['error_code' => 'RECIPIENT_TOKEN_ERROR']);

                return;
            }

            if (!User::addCreditsAtomic($recipientToken, (int) intval($coins))) {
                // If adding to recipient failed, we need to rollback the sender's deduction
                // This is a critical error that should be logged
                $appInstance->getLogger()->error('Failed to add gift credits to recipient: ' . $recipientUuid . ' for amount: ' . $coins);
                $appInstance->BadRequest('Failed to process gift - recipient credit addition failed', ['error_code' => 'RECIPIENT_CREDIT_ADDITION_FAILED']);

                return;
            }

            $appInstance->OK('Coins gifted successfully!', []);
        } catch (Exception $e) {
            $appInstance->BadRequest('Failed to process gift', [
                'error_code' => 'GIFT_PROCESSING_FAILED',
                'message' => $e->getMessage(),
            ]);
        }
    } else {
        $appInstance->BadRequest('Invalid request!', ['error_code' => 'INVALID_REQUEST']);
    }

});
