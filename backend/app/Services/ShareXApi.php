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

namespace MythicalDash\Services;

use MythicalDash\App;

class ShareXApi
{
    /**
     * Show an error response.
     *
     * @param App $app The application instance
     * @param string $error The error message
     *
     * @return void The response will be sent to the client
     */
    public static function showError(App $app, string $error): void
    {
        $app->BadRequest($error, [
            'status' => 400,
            'data' => [
                'error' => $error,
            ],
        ]);
    }

    /**
     * Show a success response.
     *
     * @param App $app The application instance
     * @param string $url The URL of the image
     * @param string $thumbnail The thumbnail of the image
     * @param string $delete The delete URL of the image
     *
     * @return void The response will be sent to the client
     */
    public static function showSuccess(App $app, string $url, string $thumbnail, string $delete): void
    {
        $app->OK('Success', [
            'status' => 200,
            'data' => [
                'link' => $url,
                'thumbnail' => $thumbnail,
                'delete' => $delete,
            ],
        ]);
    }

    /**
     * Create a ShareX config.
     *
     * @return array The ShareX config
     */
    public static function createConfig(string $app_name, string $app_url, string $upload_key, array $headers = ['x-From-MythicalDash' => 'ShareX']): array
    {
        return [
            'Version' => '17.0.0',
            'Name' => $app_name,
            'DestinationType' => 'ImageUploader',
            'RequestMethod' => 'POST',
            'RequestURL' => $app_url . '/api/user/images/upload',
            'Headers' => $headers,
            'Body' => 'MultipartFormData',
            'Arguments' => [
                'upload_api' => $upload_key,
            ],
            'FileFormName' => 'file',
            'URL' => '{json:data.link}',
            'ThumbnailURL' => '{json:data.thumbnail}',
            'DeletionURL' => '{json:data.delete}',
            'ErrorMessage' => '{json:data.error}',
        ];
    }
}
