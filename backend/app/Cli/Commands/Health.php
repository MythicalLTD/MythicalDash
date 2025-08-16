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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Cli\Extra\HealthCheck;

class Health extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cmdInstance = self::getInstance();
        $cmdInstance->send($cmdInstance->bars);
        $cmdInstance->send('&5&lMythical&d&lDash &7- &d&lHealth Check');
        $cmdInstance->send('');

        // Run health check
        $healthCheck = new HealthCheck($cmdInstance);
        $results = $healthCheck->run();

        // Display results
        $cmdInstance->send('&7Running health checks...');
        $cmdInstance->send('');

        // Display individual check results
        foreach ($results['results'] as $checkName => $result) {
            $status = $result['status'] === 'pass' ? '&a✓' : '&c✗';
            $message = $result['message'];
            $cmdInstance->send("  {$status} &7{$message}");
        }

        $cmdInstance->send('');

        // Display errors
        if (!empty($results['errors'])) {
            $cmdInstance->send('&c❌ ERRORS:');
            foreach ($results['errors'] as $error) {
                $cmdInstance->send("  &c• {$error['message']}");
            }
            $cmdInstance->send('');
        }

        // Display warnings
        if (!empty($results['warnings'])) {
            $cmdInstance->send('&e⚠️  WARNINGS:');
            foreach ($results['warnings'] as $warning) {
                $cmdInstance->send("  &e• {$warning['message']}");
            }
            $cmdInstance->send('');
        }

        // Display summary
        $totalChecks = count($results['results']);
        $passedChecks = count(array_filter($results['results'], fn ($r) => $r['status'] === 'pass'));
        $errorCount = count($results['errors']);
        $warningCount = count($results['warnings']);

        $cmdInstance->send('&7📊 SUMMARY:');
        $cmdInstance->send("  &7• Total checks: &f{$totalChecks}");
        $cmdInstance->send("  &7• Passed: &a{$passedChecks}");
        $cmdInstance->send("  &7• Errors: &c{$errorCount}");
        $cmdInstance->send("  &7• Warnings: &e{$warningCount}");
        $cmdInstance->send('');

        // Final status
        if ($results['status'] === 'healthy') {
            $cmdInstance->send('&a✅ All checks passed! System is healthy.');
        } else {
            $cmdInstance->send('&c❌ System has issues that need attention.');
        }

        $cmdInstance->send($cmdInstance->bars);
    }

    public static function getDescription(): string
    {
        return 'Check the health of your MythicalDash instance';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
