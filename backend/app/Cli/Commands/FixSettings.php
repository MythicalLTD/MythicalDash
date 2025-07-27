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

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Chat\Database;
use MythicalDash\Cli\CommandBuilder;

class FixSettings extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cliApp = App::getInstance();
        if (!file_exists(__DIR__ . '/../../../storage/.env')) {
            $cliApp->send('&7The application is not setup!');
            exit;
        }

        $cliApp->send('&aScanning for duplicate settings...');
        \MythicalDash\App::getInstance(true)->loadEnv();

        try {
            $db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
            $pdo = $db->getPdo();

            // Find all names with duplicates
            $sql = 'SELECT name FROM mythicaldash_settings GROUP BY name HAVING COUNT(*) > 1';
            $stmt = $pdo->query($sql);
            $duplicates = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($duplicates)) {
                $cliApp->send('&aNo duplicate settings found.');

                return;
            }

            foreach ($duplicates as $name) {
                // Get all ids for this name, ordered by id ASC (oldest first)
                $sql = 'SELECT id FROM mythicaldash_settings WHERE name = :name ORDER BY id ASC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['name' => $name]);
                $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                // Keep the oldest (first) id, delete the rest
                $idsToDelete = array_slice($ids, 1);
                if (!empty($idsToDelete)) {
                    $in = str_repeat('?,', count($idsToDelete) - 1) . '?';
                    $delSql = "DELETE FROM mythicaldash_settings WHERE id IN ($in)";
                    $delStmt = $pdo->prepare($delSql);
                    $delStmt->execute($idsToDelete);
                    $cliApp->send('&cDeleted duplicates for setting &e' . $name . '&c: IDs [&e' . implode(', ', $idsToDelete) . '&c]');
                }
            }

            $cliApp->send('&aDuplicate cleanup complete.');
        } catch (\Exception $e) {
            $cliApp->send('&cAn error occurred: ' . $e->getMessage());
        }
    }

    public static function getDescription(): string
    {
        return 'Fix duplicate settings by keeping only the oldest entry for each name.';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
