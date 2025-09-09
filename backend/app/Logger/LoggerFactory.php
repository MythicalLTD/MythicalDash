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

namespace MythicalDash\Logger;

class LoggerFactory
{
    public $logFile;

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
        if ($this->doesLogFileExist()) {
            $this->createLogFile();
        }
    }

    public function info(string $message): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown';
        $this->appendLog('[INFO] [' . $caller . '] ' . $message);
    }

    public function warning(string $message, bool $sendTelemetry = false): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown';
        // $eventID = null;
        $this->appendLog('[WARNING] [' . $caller . '] ' . $message);
    }

    public function error(string $message, bool $sendTelemetry = false): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown';
        $this->appendLog('[ERROR]  [' . $caller . '] ' . $message);
    }

    public function critical(string $message, bool $sendTelemetry = false): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown';
        $this->appendLog('[CRITICAL]  [' . $caller . '] ' . $message);
    }

    public function debug(string $message): void
    {
        if (APP_DEBUG == true) {
            $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown';
            $this->appendLog('[DEBUG] [' . $caller . '] ' . $message);
        }
    }

    public function getLogs(bool $isWebServer = false): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $logs = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$isWebServer) {
            return array_filter($logs ?? [], function ($log) {
                return !str_contains($log, '[DEBUG]');
            });
        }

        return $logs;
    }

    private function getFormattedDate(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function appendLog(string $message): void
    {
        try {
            file_put_contents($this->logFile, '| (' . $this->getFormattedDate() . ') ' . $message . PHP_EOL, FILE_APPEND);
        } catch (\Exception $e) {
            // Failed to write to log file, likely due to permissions
            error_log('Failed to write to log file: ' . $e->getMessage());
        }
    }

    private function createLogFile(): void
    {
        if (!$this->doesLogFileExist()) {
            try {
                file_put_contents($this->logFile, '');
            } catch (\Exception $e) {
                error_log('Failed to create log file: ' . $e->getMessage());
            }
        }
    }

    private function doesLogFileExist(): bool
    {
        return file_exists($this->logFile);
    }
}
