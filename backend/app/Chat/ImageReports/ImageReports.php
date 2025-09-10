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

namespace MythicalDash\Chat\ImageReports;

use MythicalDash\Chat\Database;

class ImageReports extends Database
{
    public const TABLE_NAME = 'mythicaldash_image_reports';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Create a new image report.
     */
    public static function create(string $imageId, string $imageUrl, string $reason, ?string $details, string $reporterIp, ?string $userAgent): int
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (image_id, image_url, reason, details, reporter_ip, user_agent, reported_at, status) VALUES (:image_id, :image_url, :reason, :details, :reporter_ip, :user_agent, NOW(), "pending")';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':image_id', $imageId);
            $stmt->bindParam(':image_url', $imageUrl);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':details', $details);
            $stmt->bindParam(':reporter_ip', $reporterIp);
            $stmt->bindParam(':user_agent', $userAgent);
            $stmt->execute();

            return $con->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create image report: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Update an existing image report.
     */
    public static function update(int $id, string $status, ?string $adminNotes = null, ?string $resolvedBy = null): void
    {
        try {
            $con = self::getPdoConnection();

            // Build SQL query conditionally based on status
            if ($status === 'resolved') {
                $sql = 'UPDATE ' . self::TABLE_NAME . ' SET status = :status, admin_notes = :admin_notes, resolved_at = NOW(), resolved_by = :resolved_by WHERE id = :id';
            } else {
                $sql = 'UPDATE ' . self::TABLE_NAME . ' SET status = :status, admin_notes = :admin_notes WHERE id = :id';
            }

            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':admin_notes', $adminNotes);

            // Only bind resolved_by parameter if status is 'resolved'
            if ($status === 'resolved') {
                $stmt->bindParam(':resolved_by', $resolvedBy);
            }

            $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update image report: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing image report within a transaction.
     * This method should be used when file deletion is also required.
     */
    public static function updateWithTransaction(int $id, string $status, ?string $adminNotes = null, ?string $resolvedBy = null): void
    {
        try {
            $con = self::getPdoConnection();
            $con->beginTransaction();

            // Build SQL query conditionally based on status
            if ($status === 'resolved') {
                $sql = 'UPDATE ' . self::TABLE_NAME . ' SET status = :status, admin_notes = :admin_notes, resolved_at = NOW(), resolved_by = :resolved_by WHERE id = :id';
            } else {
                $sql = 'UPDATE ' . self::TABLE_NAME . ' SET status = :status, admin_notes = :admin_notes WHERE id = :id';
            }

            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':admin_notes', $adminNotes);

            // Only bind resolved_by parameter if status is 'resolved'
            if ($status === 'resolved') {
                $stmt->bindParam(':resolved_by', $resolvedBy);
            }

            $stmt->execute();

            $con->commit();
        } catch (\Exception $e) {
            $con->rollBack();
            self::db_Error('Failed to update image report with transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an image report (soft delete).
     */
    public static function delete(int $id): void
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'UPDATE ' . self::TABLE_NAME . ' SET deleted = "true" WHERE id = :id';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete image report: ' . $e->getMessage());
        }
    }

    /**
     * Get an image report by ID.
     */
    public static function get(int $id)
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (\Exception $e) {
            self::db_Error('Failed to get image report: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get all image reports.
     */
    public static function getAll(): array
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE deleted = "false" ORDER BY reported_at DESC';
            $stmt = $con->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            self::db_Error('Failed to get all image reports: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get image reports by status.
     */
    public static function getByStatus(string $status): array
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE status = :status AND deleted = "false" ORDER BY reported_at DESC';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            self::db_Error('Failed to get image reports by status: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get image reports by image ID.
     */
    public static function getByImageId(string $imageId): array
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE image_id = :image_id AND deleted = "false" ORDER BY reported_at DESC';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':image_id', $imageId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            self::db_Error('Failed to get image reports by image ID: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get pending image reports count.
     */
    public static function getPendingCount(): int
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) as count FROM ' . self::TABLE_NAME . ' WHERE status = "pending" AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();

            return (int) $result['count'];
        } catch (\Exception $e) {
            self::db_Error('Failed to get pending image reports count: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Check if an image report exists.
     */
    public static function exists(int $id): bool
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) as count FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch();

            return (int) $result['count'] > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if image report exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get image reports with pagination.
     */
    public static function getWithPagination(int $page = 1, int $limit = 20, ?string $status = null): array
    {
        try {
            $con = self::getPdoConnection();
            $offset = ($page - 1) * $limit;

            $whereClause = 'WHERE deleted = "false"';
            $params = [];

            if ($status !== null) {
                $whereClause .= ' AND status = :status';
                $params[':status'] = $status;
            }

            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' ' . $whereClause . ' ORDER BY reported_at DESC LIMIT :limit OFFSET :offset';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);

            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            self::db_Error('Failed to get image reports with pagination: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get total count of image reports.
     */
    public static function getTotalCount(?string $status = null): int
    {
        try {
            $con = self::getPdoConnection();

            $whereClause = 'WHERE deleted = "false"';
            $params = [];

            if ($status !== null) {
                $whereClause .= ' AND status = :status';
                $params[':status'] = $status;
            }

            $sql = 'SELECT COUNT(*) as count FROM ' . self::TABLE_NAME . ' ' . $whereClause;
            $stmt = $con->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }

            $stmt->execute();
            $result = $stmt->fetch();

            return (int) $result['count'];
        } catch (\Exception $e) {
            self::db_Error('Failed to get total image reports count: ' . $e->getMessage());

            return 0;
        }
    }
}
