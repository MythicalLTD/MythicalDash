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
use MythicalDash\Chat\User\User;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Hooks\MythicalSystems\User\UUIDManager;

class ImageHostingService
{
    public function __construct(string $userId)
    {
        $app = App::getInstance(true);
        $config = $app->getConfig();
        $logger = $app->getLogger();

        // Only proceed if image hosting is enabled
        if (!$config->getDBSetting(ConfigInterface::IMAGE_HOSTING_ENABLED, false)) {
            $logger->info('Image hosting is disabled, skipping user image deletion for user: ' . $userId);

            return;
        }

        // Validate UUID format
        if (!UUIDManager::isValidUUID($userId)) {
            $logger->error('Invalid UUID format for user deletion: ' . $userId);

            return;
        }

        // Clean up database entries first
        $this->cleanupUserImageSettings($userId, $app, $logger);

        // Then delete physical files
        $this->deleteUserImages($userId, $app, $logger);
    }

    /**
     * Clean up all image hosting related database settings for a user.
     *
     * @param string $user_uuid The UUID of the user to clean up settings for
     * @param App $app The application instance
     * @param \MythicalDash\Logger\LoggerFactory $logger The logger instance
     */
    private function cleanupUserImageSettings(string $user_uuid, App $app, \MythicalDash\Logger\LoggerFactory $logger): void
    {
        try {
            $logger->info('Starting database cleanup for image hosting settings for user: ' . $user_uuid);

            // Get user token from UUID
            $userToken = User::getTokenFromUUID($user_uuid);
            if (!$userToken) {
                $logger->error('Could not find user token for UUID: ' . $user_uuid);

                return;
            }

            // List of all image hosting related columns to reset
            $imageHostingColumns = [
                UserColumns::IMAGE_HOSTING_ENABLED,
                UserColumns::IMAGE_HOSTING_EMBED_ENABLED,
                UserColumns::IMAGE_HOSTING_EMBED_TITLE,
                UserColumns::IMAGE_HOSTING_EMBED_DESCRIPTION,
                UserColumns::IMAGE_HOSTING_EMBED_COLOR,
                UserColumns::IMAGE_HOSTING_EMBED_IMAGE,
                UserColumns::IMAGE_HOSTING_EMBED_THUMBNAIL,
                UserColumns::IMAGE_HOSTING_EMBED_URL,
                UserColumns::IMAGE_HOSTING_EMBED_AUTHOR_NAME,
                UserColumns::IMAGE_HOSTING_UPLOAD_KEY,
            ];

            $resetCount = 0;
            foreach ($imageHostingColumns as $column) {
                try {
                    // Reset each setting to default/empty value
                    $defaultValue = $this->getDefaultValueForColumn($column);
                    User::updateInfo($userToken, $column, $defaultValue, false);
                    ++$resetCount;
                    $logger->debug("Reset image hosting setting {$column} for user: " . $user_uuid);
                } catch (\Exception $e) {
                    $logger->error("Failed to reset image hosting setting {$column} for user {$user_uuid}: " . $e->getMessage());
                }
            }

            $logger->info("Successfully reset {$resetCount} image hosting settings for user: " . $user_uuid);

        } catch (\Exception $e) {
            $logger->error("Exception occurred while cleaning up image hosting settings for user {$user_uuid}: " . $e->getMessage());
            $logger->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Get the default value for a specific image hosting column.
     *
     * @param string $column The column name
     *
     * @return string The default value
     */
    private function getDefaultValueForColumn(string $column): string
    {
        switch ($column) {
            case UserColumns::IMAGE_HOSTING_ENABLED:
            case UserColumns::IMAGE_HOSTING_EMBED_ENABLED:
                return 'false';
            case UserColumns::IMAGE_HOSTING_EMBED_TITLE:
                return 'Image Title';
            case UserColumns::IMAGE_HOSTING_EMBED_DESCRIPTION:
                return 'Image description goes here';
            case UserColumns::IMAGE_HOSTING_EMBED_COLOR:
                return '#5865F2';
            case UserColumns::IMAGE_HOSTING_EMBED_IMAGE:
            case UserColumns::IMAGE_HOSTING_EMBED_THUMBNAIL:
            case UserColumns::IMAGE_HOSTING_EMBED_URL:
            case UserColumns::IMAGE_HOSTING_EMBED_AUTHOR_NAME:
            case UserColumns::IMAGE_HOSTING_UPLOAD_KEY:
                return '';
            default:
                return '';
        }
    }

    /**
     * Delete all images and related data for a specific user.
     *
     * @param string $user_uuid The UUID of the user to delete images for
     * @param App $app The application instance
     * @param \MythicalDash\Logger\LoggerFactory $logger The logger instance
     */
    private function deleteUserImages(string $user_uuid, App $app, \MythicalDash\Logger\LoggerFactory $logger): void
    {
        try {
            // Define the user's image directory
            $userDir = APP_PUBLIC . '/attachments/imgs/users/' . $user_uuid;

            // Check if user directory exists
            if (!is_dir($userDir)) {
                $logger->info('User image directory does not exist for user: ' . $user_uuid);

                return;
            }

            $logger->info('Starting image deletion for user: ' . $user_uuid);

            // Delete all files in the raw directory (actual images)
            $rawDir = $userDir . '/raw';
            if (is_dir($rawDir)) {
                $rawFiles = glob($rawDir . '/*');
                $rawFileCount = 0;

                foreach ($rawFiles as $file) {
                    if (is_file($file)) {
                        if (unlink($file)) {
                            ++$rawFileCount;
                            $logger->debug('Deleted raw image file: ' . basename($file));
                        } else {
                            $logger->error('Failed to delete raw image file: ' . $file);
                        }
                    }
                }

                $logger->info("Deleted {$rawFileCount} raw image files for user: " . $user_uuid);
            }

            // Delete all files in the data directory (metadata)
            $dataDir = $userDir . '/data';
            if (is_dir($dataDir)) {
                $dataFiles = glob($dataDir . '/*.json');
                $dataFileCount = 0;

                foreach ($dataFiles as $file) {
                    if (is_file($file)) {
                        if (unlink($file)) {
                            ++$dataFileCount;
                            $logger->debug('Deleted metadata file: ' . basename($file));
                        } else {
                            $logger->error('Failed to delete metadata file: ' . $file);
                        }
                    }
                }

                $logger->info("Deleted {$dataFileCount} metadata files for user: " . $user_uuid);
            }

            // Remove the subdirectories
            if (is_dir($rawDir)) {
                if (rmdir($rawDir)) {
                    $logger->debug('Removed raw directory for user: ' . $user_uuid);
                } else {
                    $logger->error('Failed to remove raw directory for user: ' . $user_uuid);
                }
            }

            if (is_dir($dataDir)) {
                if (rmdir($dataDir)) {
                    $logger->debug('Removed data directory for user: ' . $user_uuid);
                } else {
                    $logger->error('Failed to remove data directory for user: ' . $user_uuid);
                }
            }

            // Finally, remove the main user directory
            if (rmdir($userDir)) {
                $logger->info('Successfully removed user image directory for user: ' . $user_uuid);
            } else {
                $logger->error('Failed to remove user image directory for user: ' . $user_uuid);
            }

            $logger->info('Completed image deletion for user: ' . $user_uuid);

        } catch (\Exception $e) {
            $logger->error("Exception occurred while deleting images for user {$user_uuid}: " . $e->getMessage());
            $logger->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Alternative method to delete user images using recursive directory removal
     * This is a more thorough approach that handles nested directories.
     *
     * @param string $user_uuid The UUID of the user to delete images for
     * @param App $app The application instance
     * @param \MythicalDash\Logger\LoggerFactory $logger The logger instance
     */
    private function deleteUserImagesRecursive(string $user_uuid, App $app, \MythicalDash\Logger\LoggerFactory $logger): void
    {
        try {
            $userDir = APP_PUBLIC . '/attachments/imgs/users/' . $user_uuid;

            if (!is_dir($userDir)) {
                $logger->info('User image directory does not exist for user: ' . $user_uuid);

                return;
            }

            $logger->info('Starting recursive image deletion for user: ' . $user_uuid);

            // Count files before deletion for logging
            $fileCount = $this->countFilesInDirectory($userDir);
            $logger->info("Found {$fileCount} files to delete for user: " . $user_uuid);

            // Use recursive directory removal
            if ($this->removeDirectoryRecursive($userDir)) {
                $logger->info('Successfully deleted all images and directories for user: ' . $user_uuid);
            } else {
                $logger->error('Failed to completely remove user image directory for user: ' . $user_uuid);
            }

        } catch (\Exception $e) {
            $logger->error("Exception occurred while recursively deleting images for user {$user_uuid}: " . $e->getMessage());
            $logger->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Count files in a directory recursively.
     *
     * @param string $dir The directory to count files in
     *
     * @return int The number of files
     */
    private function countFilesInDirectory(string $dir): int
    {
        $count = 0;
        $files = glob($dir . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                ++$count;
            } elseif (is_dir($file)) {
                $count += $this->countFilesInDirectory($file);
            }
        }

        return $count;
    }

    /**
     * Remove a directory and all its contents recursively.
     *
     * @param string $dir The directory to remove
     *
     * @return bool True if successful, false otherwise
     */
    private function removeDirectoryRecursive(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        $files = glob($dir . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                if (!unlink($file)) {
                    return false;
                }
            } elseif (is_dir($file)) {
                if (!$this->removeDirectoryRecursive($file)) {
                    return false;
                }
            }
        }

        return rmdir($dir);
    }
}
