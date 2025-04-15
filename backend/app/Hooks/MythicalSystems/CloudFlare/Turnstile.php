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

namespace MythicalDash\Hooks\MythicalSystems\CloudFlare;

class Turnstile
{
    public static function validate(string $response, string $ip, string $secret_key): int
    {
        $data = [
            'secret' => $secret_key,
            'response' => $response,
            'remoteip' => $ip,
        ];

        $verify = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($verify, false, $context);

        if ($result == false) {
            return false;
        }

        $result = json_decode($result, true);

        return $result['success'];
    }
}
