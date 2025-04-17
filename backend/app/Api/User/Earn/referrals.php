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
    if (!$appInstance->getConfig()->getSetting(ConfigInterface::REFERRALS_ENABLED, false)) {
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
