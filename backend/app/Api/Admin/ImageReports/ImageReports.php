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
use MythicalDash\Permissions;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\ImageReports\ImageReports;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\ImageHostingReportEvent;

$router->get('/api/admin/image-reports', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGE_REPORTS_LIST, $session);

    // Get query parameters for pagination and filtering
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
    $status = $_GET['status'] ?? null;

    // Validate and sanitize pagination parameters
    if ($page < 1) {
        $page = 1;
    }

    // Set maximum limit to prevent performance issues
    $maxLimit = 100;
    if ($limit < 1) {
        $limit = 20;
    } elseif ($limit > $maxLimit) {
        $limit = $maxLimit;
    }

    // Validate status if provided
    if ($status !== null) {
        $validStatuses = ['pending', 'reviewed', 'resolved', 'dismissed'];
        if (!in_array($status, $validStatuses)) {
            $appInstance->BadRequest('Invalid status parameter', ['error_code' => 'INVALID_STATUS']);

            return;
        }
    }

    $reports = ImageReports::getWithPagination($page, $limit, $status);
    $totalCount = ImageReports::getTotalCount($status);
    $pendingCount = ImageReports::getPendingCount();

    $appInstance->OK('Image reports retrieved successfully.', [
        'reports' => $reports,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalCount,
            'pages' => ceil($totalCount / $limit),
        ],
        'counts' => [
            'total' => $totalCount,
            'pending' => $pendingCount,
        ],
    ]);
});

$router->get('/api/admin/image-reports/(.*)', function ($reportId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGE_REPORTS_VIEW, $session);

    if (empty($reportId) || !is_numeric($reportId)) {
        $appInstance->BadRequest('Valid report ID is required', ['error_code' => 'INVALID_REPORT_ID']);

        return;
    }

    $report = ImageReports::get((int) $reportId);
    if (!$report) {
        $appInstance->NotFound('Image report not found', ['error_code' => 'REPORT_NOT_FOUND']);

        return;
    }

    // Log user activity for viewing report
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$image_report_view,
        CloudFlareRealIP::getRealIP(),
        "Viewed image report: $reportId (Image: {$report['image_id']})"
    );

    $appInstance->OK('Image report retrieved successfully.', [
        'report' => $report,
    ]);
});

$router->put('/api/admin/image-reports/(.*)', function ($reportId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPUT();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGE_REPORTS_EDIT, $session);

    if (empty($reportId) || !is_numeric($reportId)) {
        $appInstance->BadRequest('Valid report ID is required', ['error_code' => 'INVALID_REPORT_ID']);

        return;
    }

    // Check if report exists
    if (!ImageReports::exists((int) $reportId)) {
        $appInstance->NotFound('Image report not found', ['error_code' => 'REPORT_NOT_FOUND']);

        return;
    }

    // Get and validate request body
    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body) {
        $appInstance->BadRequest('Invalid JSON data', ['error_code' => 'INVALID_JSON']);

        return;
    }

    // Validate required fields
    if (!isset($body['status']) || empty($body['status'])) {
        $appInstance->BadRequest('Status is required', ['error_code' => 'STATUS_REQUIRED']);

        return;
    }

    // Validate status
    $validStatuses = ['pending', 'reviewed', 'resolved', 'dismissed'];
    if (!in_array($body['status'], $validStatuses)) {
        $appInstance->BadRequest('Invalid status', ['error_code' => 'INVALID_STATUS']);

        return;
    }

    // Validate admin notes length
    $adminNotes = isset($body['admin_notes']) ? trim($body['admin_notes']) : null;
    if ($adminNotes !== null && strlen($adminNotes) > 1000) {
        $appInstance->BadRequest('Admin notes too long (max 1000 characters)', ['error_code' => 'ADMIN_NOTES_TOO_LONG']);

        return;
    }

    // Get current user info for resolved_by
    $currentUser = $session->getInfo(UserColumns::USERNAME, false);

    try {
        // Get the current report to check if we need to delete the image
        $currentReport = ImageReports::get((int) $reportId);
        $wasResolved = $currentReport && $currentReport['status'] === 'resolved';
        $willBeResolved = $body['status'] === 'resolved';

        // If the report is being marked as resolved and wasn't already resolved, use transaction
        if ($willBeResolved && !$wasResolved && $currentReport) {
            // Use transaction for atomic operation
            try {
                // Update the report within transaction
                ImageReports::updateWithTransaction((int) $reportId, $body['status'], $adminNotes, $currentUser);

                // File deletion logic - if this fails, the transaction will be rolled back
                $imageDeleted = false;
                $imageId = $currentReport['image_id'];

                // Extract UUID and filename from image_id (format: uuid-timestamp.ext)
                if (preg_match('/^([0-9a-f\-]{36})-(\d+)\.([a-zA-Z0-9]+)$/', $imageId, $matches)) {
                    $userUuid = $matches[1];
                    $timestamp = $matches[2];
                    $extension = $matches[3];
                    $filename = $imageId; // Full filename
                    $nameNoExt = pathinfo($filename, PATHINFO_FILENAME);

                    // Construct paths
                    $imagePath = APP_PUBLIC . '/attachments/imgs/users/' . $userUuid . '/raw/' . $filename;
                    $metadataPath = APP_PUBLIC . '/attachments/imgs/users/' . $userUuid . '/data/' . $nameNoExt . '.json';

                    // Delete the image file if it exists
                    if (file_exists($imagePath)) {
                        if (unlink($imagePath)) {
                            $imageDeleted = true;
                            $appInstance->getLogger()->info("Deleted image file: $imagePath for resolved report: $reportId");
                        } else {
                            throw new Exception("Failed to delete image file: $imagePath for report: $reportId");
                        }
                    }

                    // Delete the metadata file if it exists
                    if (file_exists($metadataPath)) {
                        if (unlink($metadataPath)) {
                            $appInstance->getLogger()->info("Deleted metadata file: $metadataPath for resolved report: $reportId");
                        } else {
                            throw new Exception("Failed to delete metadata file: $metadataPath for report: $reportId");
                        }
                    }

                    if ($imageDeleted) {
                        $appInstance->getLogger()->info("Successfully deleted image and metadata for resolved report: $reportId, image: $imageId");
                    }
                } else {
                    throw new Exception("Could not parse image ID format: $imageId for report: $reportId");
                }

            } catch (Exception $e) {
                // Transaction will be rolled back automatically
                $appInstance->getLogger()->error("Transaction failed for report $reportId: " . $e->getMessage());
                throw $e;
            }
        } else {
            // Regular update without file deletion
            ImageReports::update((int) $reportId, $body['status'], $adminNotes, $currentUser);
        }

        // Emit events based on status change
        global $eventManager;
        $eventData = [
            'report_id' => $reportId,
            'image_id' => $currentReport['image_id'],
            'old_status' => $currentReport['status'],
            'new_status' => $body['status'],
            'updated_by' => $currentUser,
            'admin_notes' => $adminNotes,
        ];

        // Emit general update event
        $eventManager->emit(ImageHostingReportEvent::onImageReportUpdated(), $eventData);

        // Emit specific status events
        if ($body['status'] === 'resolved') {
            $eventManager->emit(ImageHostingReportEvent::onImageReportResolved(), $eventData);
        } elseif ($body['status'] === 'dismissed') {
            $eventManager->emit(ImageHostingReportEvent::onImageReportDismissed(), $eventData);
        }

        // Log user activity for updating report
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$image_report_update,
            CloudFlareRealIP::getRealIP(),
            "Updated image report: $reportId (Status: {$body['status']})"
        );

        // Log specific activity for resolve/dismiss actions
        if ($body['status'] === 'resolved') {
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$image_report_resolve,
                CloudFlareRealIP::getRealIP(),
                "Resolved image report: $reportId (Image: {$currentReport['image_id']})"
            );
        } elseif ($body['status'] === 'dismissed') {
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$image_report_dismiss,
                CloudFlareRealIP::getRealIP(),
                "Dismissed image report: $reportId (Image: {$currentReport['image_id']})"
            );
        }

        $appInstance->OK('Image report updated successfully.', [
            'report_id' => $reportId,
            'status' => $body['status'],
            'image_deleted' => $willBeResolved && !$wasResolved && $currentReport,
        ]);

    } catch (Exception $e) {
        $appInstance->InternalServerError('Failed to update image report', [
            'error_code' => 'UPDATE_FAILED',
            'message' => 'An error occurred while updating the report.',
        ]);
    }
});

$router->delete('/api/admin/image-reports/(.*)', function ($reportId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyDELETE();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGE_REPORTS_DELETE, $session);

    if (empty($reportId) || !is_numeric($reportId)) {
        $appInstance->BadRequest('Valid report ID is required', ['error_code' => 'INVALID_REPORT_ID']);

        return;
    }

    // Check if report exists
    if (!ImageReports::exists((int) $reportId)) {
        $appInstance->NotFound('Image report not found', ['error_code' => 'REPORT_NOT_FOUND']);

        return;
    }

    try {
        // Get report data before deletion for event
        $report = ImageReports::get((int) $reportId);

        ImageReports::delete((int) $reportId);

        // Emit event for report deleted
        global $eventManager;
        $eventManager->emit(ImageHostingReportEvent::onImageReportDeleted(), [
            'report_id' => $reportId,
            'image_id' => $report['image_id'],
            'status' => $report['status'],
            'deleted_by' => $session->getInfo(UserColumns::USERNAME, false),
        ]);

        // Log user activity for deleting report
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$image_report_delete,
            CloudFlareRealIP::getRealIP(),
            "Deleted image report: $reportId (Image: {$report['image_id']})"
        );

        $appInstance->OK('Image report deleted successfully.', [
            'report_id' => $reportId,
        ]);

    } catch (Exception $e) {
        $appInstance->InternalServerError('Failed to delete image report', [
            'error_code' => 'DELETE_FAILED',
            'message' => 'An error occurred while deleting the report.',
        ]);
    }
});

$router->get('/api/admin/image-reports/status/(.*)', function ($status): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGE_REPORTS_LIST, $session);

    // Validate status
    $validStatuses = ['pending', 'reviewed', 'resolved', 'dismissed'];
    if (!in_array($status, $validStatuses)) {
        $appInstance->BadRequest('Invalid status parameter', ['error_code' => 'INVALID_STATUS']);

        return;
    }

    $reports = ImageReports::getByStatus($status);
    $count = count($reports);

    $appInstance->OK('Image reports by status retrieved successfully.', [
        'reports' => $reports,
        'status' => $status,
        'count' => $count,
    ]);
});

$router->get('/api/admin/image-reports/image/(.*)', function ($imageId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGE_REPORTS_LIST, $session);

    if (empty($imageId)) {
        $appInstance->BadRequest('Image ID is required', ['error_code' => 'IMAGE_ID_REQUIRED']);

        return;
    }

    $reports = ImageReports::getByImageId($imageId);
    $count = count($reports);

    $appInstance->OK('Image reports by image ID retrieved successfully.', [
        'reports' => $reports,
        'image_id' => $imageId,
        'count' => $count,
    ]);
});

$router->get('/api/admin/image-reports/stats/overview', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGE_REPORTS_LIST, $session);

    $totalCount = ImageReports::getTotalCount();
    $pendingCount = ImageReports::getPendingCount();
    $reviewedCount = ImageReports::getTotalCount('reviewed');
    $resolvedCount = ImageReports::getTotalCount('resolved');
    $dismissedCount = ImageReports::getTotalCount('dismissed');

    $appInstance->OK('Image reports statistics retrieved successfully.', [
        'stats' => [
            'total' => $totalCount,
            'pending' => $pendingCount,
            'reviewed' => $reviewedCount,
            'resolved' => $resolvedCount,
            'dismissed' => $dismissedCount,
        ],
    ]);
});
