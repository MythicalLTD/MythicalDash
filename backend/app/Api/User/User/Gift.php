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
use MythicalDash\Chat\columns\UserColumns;

$router->post('/api/user/gift', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $config = $appInstance->getConfig();

    if ($config->getSetting(ConfigInterface::ALLOW_COINS_SHARING, 'false') !== 'true') {
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

        $fee = (int) $config->getSetting(ConfigInterface::COINS_SHARE_FEE, 10);
        $feeAmount = ($coins * $fee) / 100;
        $coinsAfterFee = $coins + $feeAmount;
        if ($coinsAfterFee > $s->getInfo(UserColumns::CREDITS, false)) {
            $appInstance->BadRequest('Insufficient balance! You need ' . $coinsAfterFee . ' coins (including ' . $fee . '% fee)', ['error_code' => 'INSUFFICIENT_BALANCE']);
        }
        $minAmount = $config->getSetting(ConfigInterface::COINS_SHARE_MIN_AMOUNT, 1);
        $maxAmount = $config->getSetting(ConfigInterface::COINS_SHARE_MAX_AMOUNT, 1000);

        if ($coins < $minAmount) {
            $appInstance->BadRequest('Amount is too low! Minimum amount is ' . $minAmount . ' coins', ['error_code' => 'COINS_AMOUNT_TOO_LOW']);
        }
        if ($coins > $maxAmount) {
            $appInstance->BadRequest('Amount is too high! Maximum amount is ' . $maxAmount . ' coins', ['error_code' => 'COINS_AMOUNT_TOO_HIGH']);
        }

        $s->setInfo(UserColumns::CREDITS, $s->getInfo(UserColumns::CREDITS, false) - $coinsAfterFee, false);
        User::updateInfo(User::getTokenFromUUID($recipientUuid), UserColumns::CREDITS, User::getInfo(User::getTokenFromUUID($recipientUuid), UserColumns::CREDITS, false) + $coinsAfterFee, false);

        $appInstance->OK('Coins gifted successfully!', []);
    } else {
        $appInstance->BadRequest('Invalid request!', ['error_code' => 'INVALID_REQUEST']);
    }

});
