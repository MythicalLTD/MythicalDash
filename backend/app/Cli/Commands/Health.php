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
