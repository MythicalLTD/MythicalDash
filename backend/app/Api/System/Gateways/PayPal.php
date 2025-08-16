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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

use MythicalDash\App;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\columns\UserColumns;

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

// Route handlers
$router->post('/api/webhooks/paypal', function () {
    $paypal = new MythicalDash\Services\PayPal\PayPalIPN();
    $paypal->handleIPN();
});

$router->get('/api/paypal/process', function () {
    App::init();
    $app = App::getInstance(true);
    $session = new Session($app);

    try {
        if (!isset($_GET['coins']) || empty($_GET['coins'])) {
            throw new InvalidArgumentException('Missing coins parameter');
        }

        $paypal = new MythicalDash\Services\PayPal\PayPalIPN();
        $redirectUrl = $paypal->createPayment(
            (float) $_GET['coins'],
            $session->getInfo(UserColumns::UUID, false)
        );

        header("Location: $redirectUrl");
        exit;
    } catch (Throwable $e) {
        $app->getLogger()->error('PayPal process error: ' . $e->getMessage());
        header('Location: /?error=payment_failed&message=' . $e->getMessage());
        exit;
    }
});

$router->add('/api/paypal/finish', function () {
    App::init();
    $app = App::getInstance(true);

    header('Location: /dashboard');
    exit;
});
