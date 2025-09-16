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
use MythicalDash\Services\ShareXApi;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;

$app = App::getInstance(true);
$logger = $app->getLogger();
$config = $app->getConfig();

$router->post('/api/user/images/toggle', function () use ($app) {
    $session = new Session($app);
    if ($session->getInfo(UserColumns::IMAGE_HOSTING_ENABLED, false) == 'true') {
        $session->setInfo(UserColumns::IMAGE_HOSTING_ENABLED, 'false', false);
    } else {
        $session->setInfo(UserColumns::IMAGE_HOSTING_ENABLED, 'true', false);
    }

    $app->OK('Success', []);

});

$router->post('/api/user/images/embed-settings/toggle', function () use ($app) {
    $session = new Session($app);
    if ($session->getInfo(UserColumns::IMAGE_HOSTING_EMBED_ENABLED, false) == 'true') {
        $session->setInfo(UserColumns::IMAGE_HOSTING_EMBED_ENABLED, 'false', false);
    } else {
        $session->setInfo(UserColumns::IMAGE_HOSTING_EMBED_ENABLED, 'true', false);
    }

    $app->OK('Success', []);
});

$router->post('/api/user/images/embed/settings', function () use ($app) {
    $session = new Session($app);
    $session->setInfo(UserColumns::IMAGE_HOSTING_ENABLED, 'true', false);
    $body = json_decode(file_get_contents('php://input'), true);

    if (!isset($body['title']) || $body['title'] == '') {
        $app->BadRequest('Title is required', ['error_code' => 'title_required']);
    }
    if (!isset($body['description']) || $body['description'] == '') {
        $app->BadRequest('Description is required', ['error_code' => 'description_required']);
    }
    if (!isset($body['color']) || $body['color'] == '') {
        $app->BadRequest('Color is required', ['error_code' => 'color_required']);
    }

    $session->setInfo(UserColumns::IMAGE_HOSTING_EMBED_TITLE, $body['title'], false);
    $session->setInfo(UserColumns::IMAGE_HOSTING_EMBED_DESCRIPTION, $body['description'], false);
    $session->setInfo(UserColumns::IMAGE_HOSTING_EMBED_COLOR, $body['color'], false);
    $session->setInfo(UserColumns::IMAGE_HOSTING_EMBED_AUTHOR_NAME, $body['author_name'] ?? '', false);

    $app->OK('Success', []);
});

$router->get('/api/user/images/sharex/download', function () use ($app, $config) {
    $session = new Session($app);
    if (!$session->getInfo(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false)) {
        $app->BadRequest('You do not have permission to upload images.', [
            'status' => 400,
            'data' => [
                'error' => 'You do not have permission to upload images.',
            ],
        ]);
    }
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="sharex_config.sxcu"');
    $appUrl =  $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');
    if (strpos($appUrl, 'https://') !== 0) {
        $appUrl = 'https://' . $appUrl;
    }
    echo json_encode(ShareXApi::createConfig(
        $config->getDBSetting(ConfigInterface::APP_NAME, 'MythicalDash'),
        $appUrl,
        $session->getInfo(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false)
    ));
});
$router->get('/api/user/images/list', function () use ($app, $config) {
    $session = new Session($app);
    if ($config->getDBSetting(ConfigInterface::IMAGE_HOSTING_ENABLED, false)) {
        $userDir = APP_PUBLIC . '/attachments/imgs/users/' . $session->getInfo(UserColumns::UUID, false);
        $dataDir = $userDir . '/data';

        // Check if user directory exists
        if (!is_dir($userDir)) {
            $app->OK('Success', [
                'status' => 200,
                'data' => [
                    'images' => [],
                ],
            ]);

            return;
        }

        // Check if data directory exists
        if (!is_dir($dataDir)) {
            $app->OK('Success', [
                'status' => 200,
                'data' => [
                    'images' => [],
                ],
            ]);

            return;
        }

        $images = glob($dataDir . '/*.json');

        // Create array of image data with timestamps for sorting
        $imageData = [];
        foreach ($images as $image) {
            $metadata = json_decode(file_get_contents($image), true);
            if ($metadata && isset($metadata['uploaded_at'])) {
                $imageData[] = [
                    'path' => str_replace(APP_PUBLIC, '', $image),
                    'timestamp' => $metadata['uploaded_at'],
                ];
            }
        }

        // Sort by timestamp in descending order (latest first)
        usort($imageData, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        // Extract just the paths after sorting
        $sortedImages = array_column($imageData, 'path');

        $app->OK('Success', [
            'status' => 200,
            'data' => [
                'images' => $sortedImages,
            ],
        ]);
    } else {
        $app->BadRequest('Image hosting is not enabled.', [
            'status' => 400,
            'data' => [
                'error' => 'Image hosting is not enabled.',
            ],
        ]);
    }
});
$router->get('/api/user/images/sharex', function () use ($app, $config) {
    $session = new Session($app);
    if (!$session->getInfo(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false)) {
        $app->BadRequest('You do not have permission to upload images.', [
            'status' => 400,
            'data' => [
                'error' => 'You do not have permission to upload images.',
            ],
        ]);
    }

    $app->OK('Success', [
        'status' => 200,
        'data' => [
            'config' => ShareXApi::createConfig($config->getDBSetting(ConfigInterface::APP_NAME, 'MythicalDash'), 'https://' . $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems'), $session->getInfo(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false)),
        ],
    ]);
});

$router->get('/api/user/images/delete/(.*)', function ($name) use ($app) {
    $session = new Session($app);
    if (!$session->getInfo(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false)) {
        $app->BadRequest('You do not have permission to delete images.', [
            'status' => 400,
            'data' => [
                'error' => 'You do not have permission to delete images.',
            ],
        ]);
    }

    $user_uuid = $session->getInfo(UserColumns::UUID, false);
    $imagePath = APP_PUBLIC . '/attachments/imgs/users/' . $user_uuid . '/raw/' . $name;
    $name_no_ext = pathinfo($name, PATHINFO_FILENAME);
    $metadataPath = APP_PUBLIC . '/attachments/imgs/users/' . $user_uuid . '/data/' . $name_no_ext . '.json';

    // Check if metadata exists and user owns the image
    if (!file_exists($metadataPath)) {
        $app->NotFound('Metadata not found');

        return;
    }
    $metadata = json_decode(file_get_contents($metadataPath), true);
    if (!$metadata || !isset($metadata['user_uuid']) || $metadata['user_uuid'] !== $user_uuid) {
        $app->BadRequest('You do not have permission to delete this image.', [
            'status' => 403,
            'data' => [
                'error' => 'You do not own this image.',
            ],
        ]);

        return;
    }

    if (!file_exists($imagePath)) {
        $app->NotFound('Image not found');

        return;
    }

    unlink($imagePath);
    unlink($metadataPath);

    $app->OK('Success', []);
});

$router->get('/i/(.*)', function ($name) use ($app, $logger, $config) {
    $templatePath = getcwd() . '/../storage/templates/imagehosting.html';

    // Extract UUID and timestamp using regex pattern: ([0-9a-f\-]{36})-(\d+)\.
    if (!preg_match('/^([0-9a-f\-]{36})-(\d+)\./', $name, $matches)) {
        $logger->error('Invalid image format - no UUID/timestamp found in name: ' . $name);
        $app->NotFound('Invalid image format');

        return;
    }

    $user_uuid = $matches[1];
    $timestamp = $matches[2];

    // Get the full filename without extension for metadata lookup
    $filename = pathinfo($name, PATHINFO_FILENAME);

    // Construct paths using the public directory
    $baseDir = APP_PUBLIC . '/attachments/imgs/users/' . $user_uuid;
    $metadataPath = $baseDir . '/data/' . $filename . '.json';
    $imagePath = $baseDir . '/raw/' . $name;

    // Debug logging
    $logger->debug('Looking for image: ' . $name . ' in base dir: ' . $baseDir . ' with UUID: ' . $user_uuid);

    if (!file_exists($metadataPath)) {
        $logger->error('Metadata file not found: ' . $metadataPath);
        $app->NotFound('Image not found');

        return;
    }

    $metadata = json_decode(file_get_contents($metadataPath), true);
    if (!$metadata) {
        $logger->error('Invalid metadata file: ' . $metadataPath);
        $app->NotFound('Invalid image metadata');

        return;
    }
    $appUrl = $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');
    if (strpos($appUrl, 'https://') !== 0) {
        $appUrl = 'https://' . $appUrl;
    }
    // Update the file URL in metadata to match our naming scheme
    $metadata['metadata']['file_url'] = sprintf(
        '%s/attachments/imgs/users/%s/raw/%s',
        $appUrl,
        $user_uuid,
        $name
    );

    if (!file_exists($imagePath)) {
        $logger->error('Image file not found: ' . $imagePath);
        $app->NotFound('Image file not found');

        return;
    }

    // If it's an API request (has Accept: application/json header), return metadata
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode($metadata, JSON_PRETTY_PRINT);

        return;
    }

    // If embed is enabled, return HTML with embed info
    if ($metadata['embed'] === true) {
        header('Content-Type: text/html');
        $embedInfo = $metadata['embed_info'];

        // Helper to safely get string values
        function safe($val)
        {
            return htmlspecialchars((string) ($val ?? ''), ENT_QUOTES, 'UTF-8');
        }

        // Prepare replacements
        $replacements = [
            '{{title}}' => safe($embedInfo['title']),
            '{{description}}' => safe($embedInfo['description']),
            '{{image_url}}' => safe($metadata['metadata']['file_url']),
            '{{url}}' => safe($embedInfo['url']),
            '{{color}}' => safe($embedInfo['color']),
            '{{user_name}}' => safe($metadata['user_name']),
            '{{uploaded_at}}' => date('Y-m-d H:i:s', $metadata['metadata']['uploaded_at'] ?? time()),
            '{{file_size}}' => (isset($metadata['metadata']['file_size']) ? round($metadata['metadata']['file_size'] / 1024, 2) . ' KB' : 'N/A'),
        ];
        // Load template
        $template = file_get_contents($templatePath);
        // Replace placeholders
        $html = strtr($template, $replacements);

        echo $html . "\n" . $templatePath;

        return;
    }

    // Default: serve the image directly
    header('Content-Type: ' . $metadata['metadata']['file_type']);
    header('Content-Length: ' . filesize($imagePath));
    readfile($imagePath);
});
$router->post('/api/user/images/upload', function () use ($app, $logger, $config) {
    if (!isset($_POST['upload_api'])) {
        ShareXApi::showError($app, 'Invalid upload key.');

        return;
    }

    $uploadKey = $_POST['upload_api'];
    if (!User::exists(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, $uploadKey)) {
        ShareXApi::showError($app, 'Invalid upload key.');

        return;
    }

    $user_uuid = User::getUserByUploadKey($uploadKey);
    if (!$user_uuid) {
        ShareXApi::showError($app, 'User not found.');

        return;
    }

    if (!isset($_FILES['file'])) {
        ShareXApi::showError($app, 'No file uploaded.');

        return;
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        ShareXApi::showError($app, 'Invalid file type. Allowed types: ' . implode(', ', $allowed));

        return;
    }

    // Get max size in bytes (default 10MB)
    $maxSize = (int) $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_MAX_FILE_SIZE, 10) * 1024 * 1024;

    // Debug log the sizes
    $logger->debug(sprintf(
        'File size check - Max: %d bytes, Uploaded: %d bytes',
        $maxSize,
        $file['size']
    ));

    if ($file['size'] <= 0) {
        ShareXApi::showError($app, 'Invalid file size: File appears to be empty.');

        return;
    }

    if ($file['size'] > $maxSize) {
        ShareXApi::showError($app, sprintf(
            'File is too large. Max size: %d MB, Provided: %.2f MB',
            $maxSize / (1024 * 1024),
            $file['size'] / (1024 * 1024)
        ));

        return;
    }

    // Create user-specific directories
    $userDir = APP_PUBLIC . '/attachments/imgs/users/' . $user_uuid;
    $rawDir = $userDir . '/raw';
    $dataDir = $userDir . '/data';

    foreach ([$userDir, $rawDir, $dataDir] as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            $logger->error('Failed to create directory: ' . $dir);
            ShareXApi::showError($app, 'Failed to create upload directory.');

            return;
        }
    }

    // Generate unique filename
    $timestamp = time();
    $new_name = sprintf(
        '%s-%s.%s',
        $user_uuid,
        $timestamp,
        $ext
    );

    $targetPath = $rawDir . '/' . $new_name;
    /**
     * Coins per image enabled.
     */
    if ($config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE_ENABLED, 'false') == 'true') {
        $coins = User::getInfoUUID($user_uuid, UserColumns::CREDITS, false);
        if ($coins <= 0) {
            ShareXApi::showError($app, 'You do not have enough coins to upload images.');

            return;
        }
        if ($coins < $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE, 1)) {
            ShareXApi::showError($app, 'You do not have enough coins to upload images.');

            return;
        }
        // Note: Coin deduction moved to after successful file upload
    }

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $logger->error('Failed to move uploaded file to: ' . $targetPath);
        ShareXApi::showError($app, 'Failed to save uploaded file.');

        return;
    }

    // Deduct coins only after successful file upload
    if ($config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE_ENABLED, 'false') == 'true') {
        $coinsPerImage = (int) $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE, 1);
        $token = User::getTokenFromUUID($user_uuid);

        if (!User::removeCreditsAtomic($token, $coinsPerImage)) {
            // If removing credits failed, log this critical error
            $logger->error('Failed to remove image hosting credits atomically for user: ' . $user_uuid . ' for coins: ' . $coinsPerImage);
            // Continue with the upload but log the error
        }
    }

    $appUrl = $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');
    if (strpos($appUrl, 'https://') !== 0) {
        $appUrl = 'https://' . $appUrl;
    }
    // Store metadata
    $metadata = [
        'original_name' => $file['name'],
        'uploaded_at' => $timestamp,
        'metadata' => [
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'file_name' => $new_name,
            'file_url' => sprintf(
                '%s/attachments/imgs/users/%s/raw/%s',
                $appUrl,
                $user_uuid,
                $new_name
            ),
            'uploaded_at' => $timestamp,
        ],
        'user_uuid' => $user_uuid,
        'user_name' => User::getInfoUUID($user_uuid, UserColumns::USERNAME, false),
        'embed' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_ENABLED, false) == 'true' ? true : false,
        'embed_info' => [
            'title' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_TITLE, false),
            'description' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_DESCRIPTION, false),
            'color' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_COLOR, false),
            'author_name' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_AUTHOR_NAME, false),
            'image' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_IMAGE, false),
            'thumbnail' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_THUMBNAIL, false),
            'url' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_URL, false),
        ],
    ];

    $metadataPath = $dataDir . '/' . pathinfo($new_name, PATHINFO_FILENAME) . '.json';
    if (!file_put_contents($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT))) {
        $logger->error('Failed to save metadata for: ' . $new_name);
    }

    $deleteUrl = sprintf(
        '%s/api/user/images/delete/%s',
        $appUrl,
        $new_name
    );

    $imageUrl = sprintf(
        '%s/attachments/imgs/users/%s/raw/%s',
        $appUrl,
        $user_uuid,
        $new_name
    );

    $embedUrl = sprintf(
        '%s/i/%s',
        $appUrl,
        $new_name
    );

    $logger->debug('Embed URL: ' . $embedUrl);
    $logger->debug('Image URL: ' . $imageUrl);
    $logger->debug('Delete URL: ' . $deleteUrl);

    ShareXApi::showSuccess(
        $app,
        $embedUrl,
        $imageUrl,
        $deleteUrl
    );
});

$router->get('/api/user/images/upload/config', function () use ($app, $config) {
    $session = new Session($app);
    if (!$session->getInfo(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false)) {
        $app->BadRequest('You do not have permission to upload images.', [
            'status' => 400,
            'data' => [
                'error' => 'You do not have permission to upload images.',
            ],
        ]);
    }

    $app->OK('Success', [
        'status' => 200,
        'data' => [
            'max_file_size' => (int) $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_MAX_FILE_SIZE, 10),
            'coins_per_image_enabled' => $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE_ENABLED, 'false'),
            'coins_per_image' => (int) $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE, 1),
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        ],
    ]);
});

$router->post('/api/user/images/upload/web', function () use ($app, $logger, $config) {
    $session = new Session($app);
    if (!$session->getInfo(UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false)) {
        $app->BadRequest('You do not have permission to upload images.', [
            'status' => 400,
            'data' => [
                'error' => 'You do not have permission to upload images.',
            ],
        ]);
    }

    if (!isset($_FILES['file'])) {
        $app->BadRequest('No file uploaded.', [
            'status' => 400,
            'data' => [
                'error' => 'No file uploaded.',
            ],
        ]);
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        $app->BadRequest('Invalid file type. Allowed types: ' . implode(', ', $allowed), [
            'status' => 400,
            'data' => [
                'error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed),
            ],
        ]);
    }

    // Get max size in bytes (default 10MB)
    $maxSize = (int) $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_MAX_FILE_SIZE, 10) * 1024 * 1024;

    if ($file['size'] <= 0) {
        $app->BadRequest('Invalid file size: File appears to be empty.', [
            'status' => 400,
            'data' => [
                'error' => 'Invalid file size: File appears to be empty.',
            ],
        ]);
    }

    if ($file['size'] > $maxSize) {
        $app->BadRequest(sprintf(
            'File is too large. Max size: %d MB, Provided: %.2f MB',
            $maxSize / (1024 * 1024),
            $file['size'] / (1024 * 1024)
        ), [
            'status' => 400,
            'data' => [
                'error' => sprintf(
                    'File is too large. Max size: %d MB, Provided: %.2f MB',
                    $maxSize / (1024 * 1024),
                    $file['size'] / (1024 * 1024)
                ),
            ],
        ]);
    }

    $user_uuid = $session->getInfo(UserColumns::UUID, false);

    // Create user-specific directories
    $userDir = APP_PUBLIC . '/attachments/imgs/users/' . $user_uuid;
    $rawDir = $userDir . '/raw';
    $dataDir = $userDir . '/data';

    foreach ([$userDir, $rawDir, $dataDir] as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            $logger->error('Failed to create directory: ' . $dir);
            $app->InternalServerError('Failed to create upload directory.', [
                'status' => 500,
                'data' => [
                    'error' => 'Failed to create upload directory.',
                ],
            ]);
        }
    }

    // Generate unique filename
    $timestamp = time();
    $new_name = sprintf(
        '%s-%s.%s',
        $user_uuid,
        $timestamp,
        $ext
    );

    $targetPath = $rawDir . '/' . $new_name;

    /**
     * Coins per image enabled.
     */
    if ($config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE_ENABLED, 'false') == 'true') {
        $coinsPerImage = (int) $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE, 1);

        // Check if user has sufficient credits atomically to prevent race conditions
        $token = User::getTokenFromUUID($user_uuid);
        if (!$token) {
            ShareXApi::showError($app, 'User token not found.');

            return;
        }

        $creditCheck = User::checkCreditsAtomic($token, $coinsPerImage);
        if (!$creditCheck['has_sufficient']) {
            ShareXApi::showError($app, 'You do not have enough coins to upload images.');

            return;
        }

        // Note: Coin deduction moved to after successful file upload
    }

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $logger->error('Failed to move uploaded file to: ' . $targetPath);
        $app->InternalServerError('Failed to save uploaded file.', [
            'status' => 500,
            'data' => [
                'error' => 'Failed to save uploaded file.',
            ],
        ]);
    }

    // Deduct coins only after successful file upload
    if ($config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE_ENABLED, 'false') == 'true') {
        $coinsPerImage = (int) $config->getDBSetting(ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE, 1);
        $token = User::getTokenFromUUID($user_uuid);

        if (!
        User::removeCreditsAtomic($token, $coinsPerImage)) {
            // If removing credits failed, log this critical error
            $logger->error('Failed to remove image hosting credits atomically for user: ' . $user_uuid . ' for coins: ' . $coinsPerImage);
            // Continue with the upload but log the error
        }
    }

    $appUrl = $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');
    if (strpos($appUrl, 'https://') !== 0) {
        $appUrl = 'https://' . $appUrl;
    }

    // Store metadata
    $metadata = [
        'original_name' => $file['name'],
        'uploaded_at' => $timestamp,
        'metadata' => [
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'file_name' => $new_name,
            'file_url' => sprintf(
                '%s/attachments/imgs/users/%s/raw/%s',
                $appUrl,
                $user_uuid,
                $new_name
            ),
            'uploaded_at' => $timestamp,
        ],
        'user_uuid' => $user_uuid,
        'user_name' => User::getInfoUUID($user_uuid, UserColumns::USERNAME, false),
        'embed' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_ENABLED, false) == 'true' ? true : false,
        'embed_info' => [
            'title' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_TITLE, false),
            'description' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_DESCRIPTION, false),
            'color' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_COLOR, false),
            'author_name' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_AUTHOR_NAME, false),
            'image' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_IMAGE, false),
            'thumbnail' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_THUMBNAIL, false),
            'url' => User::getInfoUUID($user_uuid, UserColumns::IMAGE_HOSTING_EMBED_URL, false),
        ],
    ];

    $metadataPath = $dataDir . '/' . pathinfo($new_name, PATHINFO_FILENAME) . '.json';
    if (!file_put_contents($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT))) {
        $logger->error('Failed to save metadata for: ' . $new_name);
    }

    $deleteUrl = sprintf(
        '%s/api/user/images/delete/%s',
        $appUrl,
        $new_name
    );

    $imageUrl = sprintf(
        '%s/attachments/imgs/users/%s/raw/%s',
        $appUrl,
        $user_uuid,
        $new_name
    );

    $embedUrl = sprintf(
        '%s/i/%s',
        $appUrl,
        $new_name
    );

    $logger->debug('Web upload - Embed URL: ' . $embedUrl);
    $logger->debug('Web upload - Image URL: ' . $imageUrl);
    $logger->debug('Web upload - Delete URL: ' . $deleteUrl);

    $app->OK('Image uploaded successfully', [
        'status' => 200,
        'data' => [
            'success' => true,
            'image_url' => $imageUrl,
            'embed_url' => $embedUrl,
            'delete_url' => $deleteUrl,
            'filename' => $new_name,
            'size' => $file['size'],
            'type' => $file['type'],
        ],
    ]);
});
