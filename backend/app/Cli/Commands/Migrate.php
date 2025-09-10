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

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Chat\Database;
use MythicalDash\App as MainApp;
use MythicalDash\Cli\CommandBuilder;

class Migrate extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cliApp = App::getInstance();

        // Display header
        $cliApp->send('&6&l[FeatherPanel] &r&eDatabase Migration Tool');
        $cliApp->send('&7' . str_repeat('─', 50));

        if (!file_exists(__DIR__ . '/../../../storage/.env')) {
            MainApp::getInstance(true)->getLogger()->warning('Executed a command without a .env file');
            $cliApp->send('&c&l❌ Error: &rThe .env file does not exist. Please create one before running this command');
            exit;
        }

        $sqlScript = self::getMigrationSQL();
        $startTime = microtime(true);

        try {
            MainApp::getInstance(true)->loadEnv();
            $cliApp->send('&e&l⏳ Connecting to database... &r&7' . $_ENV['DATABASE_HOST'] . ':' . $_ENV['DATABASE_PORT']);

            $db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);

            // --- Fix duplicate settings before running migrations that add unique constraints ---
            $pdo = $db->getPdo();
            $tableExists = $pdo->query("SHOW TABLES LIKE 'mythicaldash_settings'")->rowCount() > 0;
            if ($tableExists) {
                $cliApp->send('&e&l🔧 Cleaning duplicate settings...');
                $fixSql = 'DELETE FROM mythicaldash_settings WHERE id NOT IN (SELECT id FROM (SELECT MAX(id) as id FROM mythicaldash_settings GROUP BY name) as keep_ids);';
                $deletedRows = $pdo->exec($fixSql);
                if ($deletedRows > 0) {
                    $cliApp->send('&a&l✅ Cleaned &r&f' . $deletedRows . '&r&a duplicate settings');
                } else {
                    $cliApp->send('&a&l✅ No duplicate settings found');
                }
            }
            // --- End fix ---
        } catch (\Exception $e) {
            $cliApp->send('&c&l❌ Database Connection Failed: &r' . $e->getMessage());
            exit;
        }

        $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
        $cliApp->send('&a&l✅ Connected to database! &r&7(' . $connectionTime . 'ms)');

        /**
         * Check if the migrations table exists.
         */
        try {
            $query = $db->getPdo()->query("SHOW TABLES LIKE 'mythicaldash_migrations'");
            if ($query->rowCount() > 0) {
                $cliApp->send('&e&l📋 Migrations table already exists');
            } else {
                $cliApp->send('&e&l🏗️  Creating migrations table...');
                $db->getPdo()->exec(statement: $sqlScript);
                $cliApp->send('&a&l✅ Migrations table created successfully!');
            }
        } catch (\Exception $e) {
            $cliApp->send('&c&l❌ Failed to create migrations table: &r' . $e->getMessage());
            exit;
        }

        /**
         * Get all the migration scripts.
         */
        $migrations = scandir(__DIR__ . '/../../../storage/migrations/');
        $migrationFiles = array_filter($migrations, function ($file) {
            return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql';
        });

        $totalMigrations = count($migrationFiles);
        $executedMigrations = 0;
        $skippedMigrations = 0;
        $failedMigrations = 0;

        $cliApp->send('&e&l📊 Found &r&f' . $totalMigrations . '&r&e migration files');
        $cliApp->send('&7' . str_repeat('─', 50));

        foreach ($migrationFiles as $migration) {
            $migrationPath = __DIR__ . "/../../../storage/migrations/$migration";
            $migrationContent = file_get_contents($migrationPath);
            $migrationName = $migration;

            /**
             * Check if the migration was already executed.
             */
            $stmt = $db->getPdo()->prepare("SELECT COUNT(*) FROM mythicaldash_migrations WHERE script = :script AND migrated = 'true'");
            $stmt->execute(['script' => $migrationName]);
            $migrationExists = $stmt->fetchColumn();

            if ($migrationExists > 0) {
                $cliApp->send('&7&l⏭️  Skipped: &r&7' . $migrationName . ' &8(already executed)');
                ++$skippedMigrations;
                continue;
            }

            /**
             * Execute the migration.
             */
            $cliApp->send('&e&l🔄 Executing: &r&f' . $migrationName);
            $migrationStartTime = microtime(true);

            try {
                if ($migrationName == '2024-11-15-22.17-create-settings.sql') {
                    $cliApp->send('&e&l🔄 Generating encryption key...');
                    // Generate an encryption key for xchacha20
                    $encryptionKey = \MythicalDash\Hooks\MythicalSystems\Utils\XChaCha20::generateStrongKey(true);
                    MainApp::getInstance(true)->updateEnvValue('DATABASE_ENCRYPTION', 'xchacha20', false);
                    MainApp::getInstance(true)->updateEnvValue('DATABASE_ENCRYPTION_KEY', $encryptionKey, true);
                    $cliApp->send('&a&l✅ Encryption key generated successfully!');
                }
                $db->getPdo()->exec($migrationContent);
                $migrationTime = round((microtime(true) - $migrationStartTime) * 1000, 2);
                $cliApp->send('&a&l✅ Success: &r&f' . $migrationName . ' &7(' . $migrationTime . 'ms)');
                ++$executedMigrations;
            } catch (\Exception $e) {
                $cliApp->send('&c&l❌ Failed: &r&f' . $migrationName);
                $cliApp->send('&c&l   Error: &r' . $e->getMessage());
                ++$failedMigrations;
                exit;
            }

            /**
             * Save the migration to the database.
             */
            try {
                $stmt = $db->getPdo()->prepare('INSERT INTO mythicaldash_migrations (script, migrated) VALUES (:script, :migrated)');
                $stmt->execute([
                    'script' => $migrationName,
                    'migrated' => 'true',
                ]);
            } catch (\Exception $e) {
                $cliApp->send('&c&l❌ Failed to save migration record: &r' . $e->getMessage());
                exit;
            }
        }

        $totalTime = round((microtime(true) - $startTime) * 1000, 2);

        $cliApp->send('&7' . str_repeat('─', 50));
        $cliApp->send('&6&l📈 Migration Summary:');
        $cliApp->send('&a&l   ✅ Executed: &r&f' . $executedMigrations . '&r&a migrations');
        $cliApp->send('&7&l   ⏭️  Skipped: &r&f' . $skippedMigrations . '&r&7 migrations');
        $cliApp->send('&c&l   ❌ Failed: &r&f' . $failedMigrations . '&r&c migrations');
        $cliApp->send('&e&l   ⏱️  Total Time: &r&f' . $totalTime . '&r&e ms');

        if ($failedMigrations > 0) {
            $cliApp->send('&c&l⚠️  Some migrations failed. Please check the errors above.');
        } else {
            $cliApp->send('&a&l🎉 All migrations completed successfully!');
        }

        $cliApp->send('&e&l🔄 Please restart the server to apply the changes!');
    }

    public static function getDescription(): string
    {
        return 'Migrate the database to the latest version';
    }

    public static function getSubCommands(): array
    {
        return [];
    }

    private static function getMigrationSQL(): string
    {
        return "CREATE TABLE IF NOT EXISTS `mythicaldash_migrations` (
            `id` INT NOT NULL AUTO_INCREMENT COMMENT 'The id of the migration!',
            `script` TEXT NOT NULL COMMENT 'The script to be migrated!',
            `migrated` ENUM('true','false') NOT NULL DEFAULT 'true' COMMENT 'Did we migrate this already?',
            `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date from when this was executed!',
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = 'The migrations table is table where save the sql migrations!';";
    }
}
