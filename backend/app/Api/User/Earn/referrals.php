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
use MythicalDash\Chat\Referral\ReferralUses;
use MythicalDash\Chat\Referral\ReferralCodes;
use MythicalDash\Plugins\Events\Events\ReferralsEvent;

$router->get('/api/user/earn/referrals', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $uuid = $session->getInfo(UserColumns::UUID, false);

    // Check if referrals are enabled
    if (!$appInstance->getConfig()->getDBSetting(ConfigInterface::REFERRALS_ENABLED, false)) {
        $appInstance->BadRequest('Referrals are not enabled', ['error_code' => 'REFERRALS_NOT_ENABLED']);

        return;
    }

    // Get or create referral code for user
    $referralCode = ReferralCodes::getByUser($uuid);

    // If no referral code exists, create one
    if (!$referralCode || empty($referralCode)) {
        $code = $session->getInfo(UserColumns::USERNAME, false) . '_' . $appInstance->generatePin();
        $newReferralId = ReferralCodes::create($uuid, $code);
        global $eventManager;
        $eventManager->emit(ReferralsEvent::onReferralCreated(), [
            'user' => $uuid,
            'referral_code' => $code,
        ]);
        if (!$newReferralId) {
            $appInstance->BadRequest('Failed to create referral code', ['error_code' => 'REFERRALS_CREATE_FAILED']);

            return;
        }

        $referralCode = ReferralCodes::getById($newReferralId);
        if (!$referralCode || empty($referralCode)) {
            $appInstance->BadRequest('Failed to retrieve created referral code', ['error_code' => 'REFERRALS_RETRIEVE_FAILED']);

            return;
        }
    }

    // Get the actual code string
    $code = $referralCode[0]['code'] ?? null;
    if (!$code) {
        $appInstance->BadRequest('Invalid referral code data', ['error_code' => 'REFERRALS_INVALID_DATA']);

        return;
    }

    // Get list of referrals for the user
    $referrals = ReferralUses::getListByReferralCode((int) $referralCode[0]['id']);

    $processedReferrals = [];
    foreach ($referrals as $referral) {
        $token = User::getTokenFromUUID($referral['referred_user_id']);
        $processedReferral = [
            'id' => $referral['id'],
            'referral_code_id' => $referral['referral_code_id'],
            'deleted' => $referral['deleted'],
            'updated_at' => $referral['updated_at'],
            'created_at' => $referral['created_at'],
            'user' => [
                'username' => User::getInfo($token, UserColumns::USERNAME, false),
                'avatar' => User::getInfo($token, UserColumns::AVATAR, false),
                'uuid' => $referral['referred_user_id'],
            ],
        ];
        $processedReferrals[] = $processedReferral;
    }

    $appInstance->OK('Referrals', [
        'referrals' => $processedReferrals,
        'referral_code' => $code,
    ]);
});
