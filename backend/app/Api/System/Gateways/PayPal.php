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
