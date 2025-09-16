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
use MythicalDash\Chat\Gateways\StripeDB;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;

$router->add('/api/webhooks/stripe', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $appInstance->OK('Stripe Webhook received', []);
});

$router->add('/api/stripe/processed', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();

    if (isset($_GET['code']) && !$_GET['code'] == '') {
        $code = $_GET['code'];
        $stripe = StripeDB::getByCode($code);
        if ($stripe) {
            $uuid = $stripe['user'];
            $coins = $stripe['coins'];

            $token = User::getTokenFromUUID($uuid);
            if (StripeDB::isPending($code)) {

                Stripe\Stripe::setApiKey($appInstance->getConfig()->getDBSetting(ConfigInterface::STRIPE_SECRET_KEY, 'NULL'));

                // Add credits atomically to prevent race conditions
                if (!User::addCreditsAtomic($token, (int) $coins)) {
                    // If adding credits failed, log this critical error
                    $appInstance->getLogger()->error('Failed to add Stripe credits atomically for user: ' . $uuid . ' for payment: ' . $code);
                    header('location: /?error=stripe_error=credit_addition_failed');
                    exit;
                }

                StripeDB::updateStatus($code, 'processed');

                header('location: /?success=coins_added');
                exit;
            }
            header('location: /?error=stripe_error=invalid_code');
            exit;

        }
        header('location: /?error=stripe_error=invalid_code');
        exit;

    }
    header('location: /?error=missing_data');
    exit;

});

$router->add('/api/stripe/cancelled', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    StripeDB::cancelLastTransactionForUser($session->getInfo(UserColumns::UUID, false));
    header('location: /?error=stripe_error=cancelled');
    exit;
});

$router->add('/api/stripe/process', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    if (isset($_GET['coins']) && !$_GET['coins'] == '') {
        $coins = $_GET['coins'];
        $uuid = $session->getInfo(UserColumns::UUID, false);
        $code = bin2hex(random_bytes(16));
        if (StripeDB::create($code, $coins, $uuid)) {
            try {
                Stripe\Stripe::setApiKey($appInstance->getConfig()->getDBSetting(ConfigInterface::STRIPE_SECRET_KEY, 'NULL'));

                $checkout_session = Stripe\Checkout\Session::create([
                    'mode' => 'payment',
                    'customer_email' => $session->getInfo(UserColumns::EMAIL, false),
                    'success_url' =>  'https://' . $appInstance->getConfig()->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems') . '/api/stripe/processed?code=' . $code,
                    'cancel_url' => 'https://' . $appInstance->getConfig()->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems') . '/api/stripe/cancelled',
                    'line_items' => [
                        [
                            'quantity' => 1,
                            'price_data' => [
                                'currency' => $appInstance->getConfig()->getDBSetting(ConfigInterface::CURRENCY, 'EUR'),
                                'unit_amount' => $coins * 100,
                                'product_data' => [
                                    'name' => 'Account Topup',
                                    'description' => 'Topup your account with ' . $coins . ' coins!',
                                    'images' => [],
                                ],
                            ],
                        ],
                    ],
                ]);
                http_response_code(303);
                header('location: ' . $checkout_session->url);
                exit;
            } catch (Exception $e) {
                header('location: /?error=stripe_error=' . $e->getMessage());
                exit;
            }
        } else {
            header('location: /?error=db_error');
            exit;
        }
    } else {
        header('location: /?error=missing_data');
        exit;
    }
});
