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
use MythicalDash\Config\ConfigInterface;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\ImageReports\ImageReports;
use MythicalDash\Plugins\Events\Events\ImageHostingReportEvent;

$router->post('/api/system/imagehosting/report', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();

    $config = $appInstance->getConfig();
    $logger = $appInstance->getLogger();

    // Check if image hosting is enabled
    if (!$config->getDBSetting(ConfigInterface::IMAGE_HOSTING_ENABLED, 'false')) {
        $appInstance->BadRequest('Image hosting is not enabled', ['error_code' => 'IMAGE_HOSTING_DISABLED']);

        return;
    }

    // Get and validate request body
    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body) {
        $appInstance->BadRequest('Invalid JSON data', ['error_code' => 'INVALID_JSON']);

        return;
    }

    // Validate required fields
    if (!isset($body['image_url']) || empty($body['image_url'])) {
        $appInstance->BadRequest('Image URL is required', ['error_code' => 'IMAGE_URL_REQUIRED']);

        return;
    }

    if (!isset($body['reason']) || empty($body['reason'])) {
        $appInstance->BadRequest('Report reason is required', ['error_code' => 'REASON_REQUIRED']);

        return;
    }

    // Validate reason
    $validReasons = ['inappropriate', 'copyright', 'spam', 'harassment', 'violence', 'other'];
    if (!in_array($body['reason'], $validReasons)) {
        $appInstance->BadRequest('Invalid report reason', ['error_code' => 'INVALID_REASON']);

        return;
    }

    // Validate image URL format
    $imageUrl = $body['image_url'];
    $appUrl = $config->getDBSetting(ConfigInterface::APP_URL, '');

    // Parse the image URL to extract the host
    $parsedImageUrl = parse_url($imageUrl);
    if (!$parsedImageUrl || !isset($parsedImageUrl['host'])) {
        $appInstance->BadRequest('Invalid image URL format', ['error_code' => 'INVALID_IMAGE_URL_FORMAT']);

        return;
    }

    $imageHost = $parsedImageUrl['host'];
    $serverHost = $_SERVER['HTTP_HOST'] ?? '';

    // Parse the configured app URL to extract its host
    $parsedAppUrl = parse_url($appUrl);
    $appHost = $parsedAppUrl['host'] ?? '';

    // Validate that the image URL host matches either the configured app URL host or the server's HTTP_HOST
    $isValidHost = false;

    if (!empty($appHost) && $imageHost === $appHost) {
        $isValidHost = true;
    }

    if (!empty($serverHost) && $imageHost === $serverHost) {
        $isValidHost = true;
    }

    if (!$isValidHost) {
        $appInstance->BadRequest('Invalid image URL - host does not match application domain', [
            'error_code' => 'INVALID_IMAGE_URL_HOST',
            'provided_host' => $imageHost,
            'expected_hosts' => array_filter([$appHost, $serverHost]),
        ]);

        return;
    }

    // Extract image ID from URL (format: /i/{uuid-timestamp.ext})
    $imageId = null;
    if (preg_match('/\/i\/([0-9a-f\-]{36}-\d+\.[a-zA-Z0-9]+)/', $imageUrl, $matches)) {
        $imageId = $matches[1];
    } else {
        $appInstance->BadRequest('Could not extract image ID from URL. Expected format: /i/{uuid-timestamp.ext}', ['error_code' => 'INVALID_IMAGE_ID']);

        return;
    }

    // Sanitize and validate details
    $details = isset($body['details']) ? trim($body['details']) : '';
    if (strlen($details) > 500) {
        $appInstance->BadRequest('Details too long (max 500 characters)', ['error_code' => 'DETAILS_TOO_LONG']);

        return;
    }

    // Get client IP and user agent for logging
    $clientIp = CloudFlareRealIP::getRealIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    try {
        // Create the report using the CRUD class
        $reportId = ImageReports::create($imageId, $imageUrl, $body['reason'], $details, $clientIp, $userAgent);

        if ($reportId === 0) {
            throw new Exception('Failed to create image report');
        }
        global $eventManager;
        $eventManager->emit(ImageHostingReportEvent::onImageHostingReport(), [
            'image_id' => $imageId,
            'image_url' => $imageUrl,
            'reason' => $body['reason'],
        ]);
        $appInstance->OK('Report submitted successfully', [
            'report_id' => $reportId,
            'message' => 'Thank you for your report. Our team will review it shortly.',
        ]);

    } catch (Exception $e) {
        $logger->error('Failed to submit image report', false);

        $appInstance->InternalServerError('Failed to submit report', [
            'error_code' => 'DATABASE_ERROR',
            'message' => 'An error occurred while submitting your report. Please try again later.',
        ]);
    }
});
