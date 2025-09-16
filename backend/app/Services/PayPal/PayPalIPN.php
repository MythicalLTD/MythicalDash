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

namespace MythicalDash\Services\PayPal;

use MythicalDash\App;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Gateways\PayPalDB;
use MythicalDash\Config\ConfigInterface;
use GuzzleHttp\Exception\GuzzleException;

class PayPalIPN
{
    private const SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    private const LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';
    private const IPN_SANDBOX_URL = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
    private const IPN_LIVE_URL = 'https://ipnpb.paypal.com/cgi-bin/webscr';

    private App $app;
    private Client $client;
    private bool $isSandbox;
    private string $businessEmail;
    private string $appUrl;

    public function __construct()
    {
        $this->app = App::getInstance(true);
        $this->isSandbox = $this->app->getConfig()->getDBSetting(ConfigInterface::PAYPAL_IS_SANDBOX, 'false') === 'true';
        $this->businessEmail = $this->app->getConfig()->getDBSetting(ConfigInterface::PAYPAL_CLIENT_ID, '');
        $this->appUrl = 'https://' . $this->app->getConfig()->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');

        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'MythicalDash-PayPal/1.0',
                'Connection' => 'Close',
            ],
        ]);

        if (empty($this->businessEmail)) {
            throw new \RuntimeException('PayPal business email not configured: ' . $this->businessEmail);
        }
    }

    public function handleIPN(): void
    {
        try {
            $postData = $this->getPostData();
            $verificationResponse = $this->verifyIPNMessage($postData);

            if ($verificationResponse === 'VERIFIED') {
                $this->processVerifiedIPN($postData);
            }

            header('HTTP/1.1 200 OK');
        } catch (\Throwable $e) {
            header('HTTP/1.1 500 Internal Server Error');
        }
    }

    public function createPayment(float $amount, string $uuid): string
    {
        try {
            $code = bin2hex(random_bytes(16));
            PayPalDB::create($code, $amount, $uuid);

            $params = [
                'cmd' => '_xclick',
                'business' => $this->businessEmail,
                'item_name' => 'Account Topup',
                'item_number' => $code,
                'amount' => number_format($amount, 2, '.', ''),
                'currency_code' => $this->app->getConfig()->getDBSetting(ConfigInterface::CURRENCY, 'EUR'),
                'custom' => $code . '|' . $uuid,
                'no_shipping' => '1',
                'no_note' => '1',
                'charset' => 'UTF-8',
                'rm' => '2',
                'return' => "{$this->appUrl}/api/paypal/finish",
                'cancel_return' => "{$this->appUrl}/dashboard",
                'notify_url' => "{$this->appUrl}/api/webhooks/paypal",
                'bn' => 'MythicalDash_BuyNow_WPS_US',
            ];

            return ($this->isSandbox ? self::SANDBOX_URL : self::LIVE_URL) .
                   '?' . http_build_query($params);

        } catch (\Throwable $e) {
            $this->app->getLogger()->error('Failed to create PayPal payment: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getPostData(): array
    {
        $raw = file_get_contents('php://input');
        parse_str($raw, $postData);

        return $postData;
    }

    private function verifyIPNMessage(array $postData): string
    {
        try {
            $verifyData = array_merge(['cmd' => '_notify-validate'], $postData);

            $response = $this->client->post(
                $this->isSandbox ? self::IPN_SANDBOX_URL : self::IPN_LIVE_URL,
                [
                    RequestOptions::FORM_PARAMS => $verifyData,
                    RequestOptions::HEADERS => [
                        'User-Agent' => 'MythicalDash-PayPal-IPN/1.0',
                        'Connection' => 'Close',
                    ],
                ]
            );

            return (string) $response->getBody();

        } catch (GuzzleException $e) {
            throw new \RuntimeException("IPN Verification Failed: {$e->getMessage()}");
        }
    }

    private function processVerifiedIPN(array $postData): void
    {
        if ($postData['payment_status'] !== 'Completed') {
            return;
        }

        [$code, $uuid] = explode('|', $postData['custom']);

        if (!PayPalDB::exists($code)) {
            throw new \RuntimeException("Payment code not found: $code");
        }

        if (!PayPalDB::isPending($code)) {
            return;
        }

        $this->creditUserAccount($code, $uuid);
    }

    private function creditUserAccount(string $code, string $uuid): void
    {
        PayPalDB::updateStatus($code, 'processed');

        $token = User::getTokenFromUUID($uuid);
        $payment = PayPalDB::getByCode($code);

        // Add credits atomically to prevent race conditions
        if (!User::addCreditsAtomic($token, (int) $payment['coins'])) {
            // If adding credits failed, log this critical error
            // The payment was already marked as processed, so we can't rollback easily
            $this->app->getLogger()->error('Failed to add PayPal credits atomically for user: ' . $uuid . ' for payment: ' . $code);

            // Don't throw exception - this would break IPN processing
            // Instead, log the failure and continue. The user can contact support if credits are missing.
            $this->app->getLogger()->warning('PayPal payment processed but credits failed to add for user: ' . $uuid . ' payment: ' . $code);
        }
    }
}
