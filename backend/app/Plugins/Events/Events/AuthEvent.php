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

namespace MythicalDash\Plugins\Events\Events;

use MythicalDash\Plugins\Events\PluginEvent;

class AuthEvent implements PluginEvent
{
    public static function onAuthLoginFailed(): string
    {
        return 'auth::LoginFailed';
    }

    public static function onAuthLoginSuccess(): string
    {
        return 'auth::LoginSuccess';
    }

    public static function onAuthLogout(): string
    {
        return 'auth::Logout';
    }

    public static function onAuthRegister(): string
    {
        return 'auth::Register';
    }

    public static function onAuthRegisterFailed(): string
    {
        return 'auth::RegisterFailed';
    }

    public static function onAuthRegisterSuccess(): string
    {
        return 'auth::RegisterSuccess';
    }

    public static function onAuthForgotPassword(): string
    {
        return 'auth::ForgotPassword';
    }

    public static function onAuthForgotPasswordFailed(): string
    {
        return 'auth::ForgotPasswordFailed';
    }

    public static function onAuthForgotPasswordSuccess(): string
    {
        return 'auth::ForgotPasswordSuccess';
    }

    public static function onAuthResetPasswordFailed(): string
    {
        return 'auth::ResetPasswordFailed';
    }

    public static function onAuthResetPasswordSuccess(): string
    {
        return 'auth::ResetPasswordSuccess';
    }

    public static function onAuth2FAVerifyFailed(): string
    {
        return 'auth::2FAVerifyFailed';
    }

    public static function onAuth2FAVerifySuccess(): string
    {
        return 'auth::2FAVerifySuccess';
    }
}
