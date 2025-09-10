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

namespace MythicalDash\Chat\Tickets;

use MythicalDash\Chat\Database;

class Messages extends Database
{
    public const TABLE_NAME = 'mythicaldash_tickets_messages';

    /**
     * Retrieves all messages for a given ticket ID.
     *
     * @param int $ticketId The ID of the ticket to get messages for
     *
     * @return array Array of messages or empty array if none found/error occurs
     */
    public static function getMessagesByTicketId(int $ticketId): array
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ticket = :ticket_id AND deleted = \'false\' ORDER BY id DESC';
            $stmt = $con->prepare($sql);
            $stmt->bindParam('ticket_id', $ticketId, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!$result) {
                return [];
            }

            return $result;
        } catch (\Exception $e) {
            self::db_Error('Failed to get messages by ticket id: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Creates a new message for a ticket.
     *
     * @param int $ticketId The ID of the ticket to create message for
     * @param string $message The message content
     * @param string $sender The sender of the message
     */
    public static function createMessage(int $ticketId, string $message, string $sender): void
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (ticket, user, message) VALUES (:ticket_id, :sender, :message)';
            $stmt = $con->prepare($sql);
            $stmt->bindParam('ticket_id', $ticketId, \PDO::PARAM_INT);
            $stmt->bindParam('message', $message, \PDO::PARAM_STR);
            $stmt->bindParam('sender', $sender, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to create message: ' . $e->getMessage());
        }
    }

    /**
     * Deletes a message by its ID.
     *
     * @param int $messageId The ID of the message to delete
     */
    public static function deleteMessage(int $messageId): void
    {
        try {
            $con = self::getPdoConnection();
            if (self::exists($messageId)) {
                $sql = 'UPDATE ' . self::TABLE_NAME . ' SET deleted = true WHERE id = :message_id';
                $stmt = $con->prepare($sql);
                $stmt->bindParam('message_id', $messageId, \PDO::PARAM_INT);
                $stmt->execute();
            } else {
                return;
            }
        } catch (\Exception $e) {
            self::db_Error('Failed to delete message: ' . $e->getMessage());
        }
    }

    /**
     * Checks if a message exists by its ID.
     *
     * @param int $messageId The ID of the message to check
     *
     * @return bool Returns true if message exists, false otherwise
     */
    public static function exists(int $messageId): bool
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE id = :message_id';
            $stmt = $con->prepare($sql);
            $stmt->bindParam('message_id', $messageId, \PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if message exists: ' . $e->getMessage());

            return false;
        }
    }
}
