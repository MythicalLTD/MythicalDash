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

namespace MythicalDash\Chat\Tickets;

use MythicalDash\Chat\Database;

class Tickets extends Database
{
    public const TABLE_NAME = 'mythicaldash_tickets';
    public const TABLE_NAME_ATTACHMENTS = 'mythicaldash_tickets_attachments';

    /**
     * Get the table name for tickets.
     *
     * @return string The table name
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get the table name for ticket attachments.
     *
     * @return string The attachments table name
     */
    public static function getAttachmentsTableName(): string
    {
        return self::TABLE_NAME_ATTACHMENTS;
    }

    /**
     * Create a new ticket.
     *
     * @param string $uuid The user UUID
     * @param int $department The department ID
     * @param string $title The ticket title
     * @param string $description The ticket description
     * @param string $priority The ticket priority
     *
     * @return int|false The ID of the newly created ticket, or false on failure
     */
    public static function create(
        string $uuid,
        int $department,
        string $title,
        string $description,
        string $priority,
    ): int|false {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::getTableName() . ' (user, department, priority, title, description) VALUES (:uuid, :department, :priority, :title, :description)';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':uuid', $uuid, \PDO::PARAM_STR);
            $stmt->bindParam(':department', $department, \PDO::PARAM_INT);
            $stmt->bindParam(':priority', $priority, \PDO::PARAM_STR);
            $stmt->bindParam(':title', $title, \PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, \PDO::PARAM_STR);
            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create ticket: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a ticket (soft delete).
     *
     * @param int $id The ID of the ticket to delete
     *
     * @return bool True on success, false on failure
     */
    public static function delete(int $id): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Ticket does not exist but tried to delete it: ' . $id);

                return false;
            }

            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET deleted = "true" WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete ticket: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a ticket exists by ID.
     *
     * @param int $id The ID of the ticket to check
     *
     * @return bool True if the ticket exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if ticket exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all tickets with optional limit.
     *
     * @param int $limit Maximum number of tickets to return
     *
     * @return array The list of tickets
     */
    public static function getAll(int $limit = 150): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false" ORDER BY id DESC LIMIT :limit';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all tickets: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Alias for getAll for backward compatibility.
     *
     * @param int $limit Maximum number of tickets to return
     *
     * @return array The list of tickets
     */
    public static function getAllTickets(int $limit = 150): array
    {
        return self::getAll($limit);
    }

    /**
     * Update a ticket's status.
     *
     * @param int $ticketId The ID of the ticket to update
     * @param string $status The new status
     *
     * @return bool True on success, false on failure
     */
    public static function updateTicketStatus(int $ticketId, string $status): bool
    {
        try {
            if (!self::exists($ticketId)) {
                self::db_Error('Ticket does not exist but tried to update its status: ' . $ticketId);

                return false;
            }

            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET status = :status WHERE id = :ticket_id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':ticket_id', $ticketId, \PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, \PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update ticket status: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all tickets for a specific user.
     *
     * @param string $uuid The user UUID
     * @param int $limit Maximum number of tickets to return
     *
     * @return array The list of tickets for the user
     */
    public static function getAllByUser(string $uuid, int $limit = 150): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE user = :uuid AND deleted = "false" ORDER BY id DESC LIMIT :limit';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':uuid', $uuid, \PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();

            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($tickets as $key => $ticket) {
                $tickets[$key]['department_id'] = $ticket['department'];
                $tickets[$key]['department'] = Departments::getById((int) $ticket['department']);

                if (empty($tickets[$key]['department'])) {
                    $tickets[$key]['department'] = [
                        'id' => 0,
                        'name' => 'Deleted Department',
                        'description' => 'This department has been deleted.',
                        'time_open' => '08:30',
                        'time_close' => '17:30',
                        'enabled' => 'true',
                        'deleted' => 'false',
                        'locked' => 'false',
                        'date' => '2024-12-25 22:25:09',
                    ];
                }
            }

            return $tickets;
        } catch (\Exception $e) {
            self::db_Error('Failed to get all tickets by user: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Alias for getAllByUser for backward compatibility.
     *
     * @param string $uuid The user UUID
     * @param int $limit Maximum number of tickets to return
     *
     * @return array The list of tickets for the user
     */
    public static function getAllTicketsByUser(string $uuid, int $limit = 150): array
    {
        return self::getAllByUser($uuid, $limit);
    }

    /**
     * Get a ticket by ID.
     *
     * @param int $id The ID of the ticket to get
     *
     * @return array|null The ticket data or null if not found
     */
    public static function getById(int $id): ?array
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Ticket does not exist but tried to get it: ' . $id);

                return null;
            }

            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get ticket: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Alias for getById for backward compatibility.
     *
     * @param int $id The ID of the ticket to get
     *
     * @return array|null The ticket data or null if not found
     */
    public static function getTicket(int $id): ?array
    {
        return self::getById($id);
    }
}
