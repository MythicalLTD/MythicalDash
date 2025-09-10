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

class Attachments extends Database
{
    public const TABLE_NAME = 'mythicaldash_tickets_attachments';

    /**
     * Adds a new attachment to a ticket.
     *
     * @param int $ticketId The ID of the ticket
     * @param string $filename The name of the attachment file
     */
    public static function addAttachment(int $ticketId, string $filename): void
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (ticket, file) VALUES (:ticket_id, :filename)';
            $stmt = $con->prepare($sql);
            $stmt->bindParam('ticket_id', $ticketId, \PDO::PARAM_INT);
            $stmt->bindParam('filename', $filename, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $ex) {
            self::db_Error('Error adding attachment: ' . $ex->getMessage());
        }
    }

    /**
     * Retrieves all attachments for a given ticket ID.
     *
     * @param int $ticketId The ID of the ticket to get attachments for
     *
     * @return array Array of attachments or empty array if none found/error occurs
     */
    public static function getAttachmentsByTicketId(int $ticketId): array
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ticket = :ticket_id AND deleted = \'false\'';
            $stmt = $con->prepare($sql);
            $stmt->bindParam('ticket_id', $ticketId, \PDO::PARAM_INT);
            $stmt->execute();
            $attachments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $attachments;
        } catch (\Exception $ex) {
            self::db_Error('Error getting attachments: ' . $ex->getMessage());

            return [];
        }
    }

    /**
     * Deletes an attachment from the database.
     *
     * @param int $attachmentId The ID of the attachment to delete
     */
    public static function deleteAttachment(int $attachmentId): void
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'UPDATE ' . self::TABLE_NAME . ' SET deleted = \'true\' WHERE id = :attachment_id';
            $stmt = $con->prepare($sql);
            $stmt->bindParam('attachment_id', $attachmentId, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Exception $ex) {
            self::db_Error('Error deleting attachment: ' . $ex->getMessage());
        }
    }
}
