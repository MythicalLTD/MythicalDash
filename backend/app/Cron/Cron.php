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

namespace MythicalDash\Cron;

class Cron
{
    /**
     * @var array Supported time units and their minute equivalents
     */
    private const TIME_UNITS = [
        'S' => 1,        // Seconds
        'M' => 1,        // Minutes
        'H' => 60,       // Hours
        'D' => 1440,     // Days
        'W' => 10080,    // Weeks
    ];

    /**
     * @var string The unique identifier for this cron job
     */
    private string $identifier;

    /**
     * @var int Minutes between executions
     */
    private int $interval;

    /**
     * @var string Path to store last run timestamps
     */
    private string $storageFile;

    /**
     * Initialize a new Cron instance.
     *
     * @param string $identifier Unique identifier for this cron job
     * @param string $interval Interval string (e.g., "30M", "1H", "1D")
     *
     * @throws \Exception If interval format is invalid
     */
    public function __construct(string $identifier, string $interval)
    {
        $this->identifier = $identifier;
        $this->interval = $this->parseInterval($interval);
        $this->storageFile = $this->getStoragePath();

        // Ensure storage directory exists
        if (!is_dir(dirname($this->storageFile))) {
            mkdir(dirname($this->storageFile), 0755, true);
        }
    }

    /**
     * Check if the cron job should run.
     */
    public function shouldRun(): bool
    {
        if (!file_exists($this->storageFile)) {
            return true;
        }

        $lastRun = (int) file_get_contents($this->storageFile);
        $minutesSinceLastRun = (time() - $lastRun) / 60;

        return $minutesSinceLastRun >= $this->interval;
    }

    /**
     * Mark the cron job as run.
     */
    public function markAsRun(): void
    {
        file_put_contents($this->storageFile, time());
    }

    /**
     * Get the last run time.
     */
    public function getLastRunTime(): ?\DateTime
    {
        if (!file_exists($this->storageFile)) {
            return null;
        }

        $timestamp = (int) file_get_contents($this->storageFile);

        return (new \DateTime())->setTimestamp($timestamp);
    }

    /**
     * Get the next scheduled run time.
     */
    public function getNextRunTime(): \DateTime
    {
        $lastRun = $this->getLastRunTime() ?? new \DateTime();
        $nextRun = clone $lastRun;

        return $nextRun->modify("+{$this->interval} minutes");
    }

    /**
     * Execute the cron job if it's due.
     *
     * @param callable $callback Function to execute
     *
     * @return bool Whether the job was executed
     */
    public function runIfDue(callable $callback, bool $force = false): bool
    {
        if (!$this->shouldRun() && !$force) {
            return false;
        }

        try {
            $callback();
            $this->markAsRun();

            return true;
        } catch (\Exception $e) {
            // Log the error but don't mark as run
            error_log("Cron job {$this->identifier} failed: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Parse the interval string into minutes.
     *
     * @param string $interval Interval string (e.g., "30M", "1H", "1D")
     *
     * @throws \Exception If interval format is invalid
     *
     * @return int Minutes
     */
    private function parseInterval(string $interval): int
    {
        if (!preg_match('/^(\d+)([MHDWS])$/', $interval, $matches)) {
            throw new \Exception('Invalid interval format. Use number followed by M (minutes), H (hours), D (days), W (weeks), or S (seconds). Example: 30M');
        }

        $value = (int) $matches[1];
        $unit = $matches[2];

        if (!isset(self::TIME_UNITS[$unit])) {
            throw new \Exception('Invalid time unit. Use M, H, D, W, or S');
        }

        return $value * self::TIME_UNITS[$unit];
    }

    /**
     * Get the storage path for this cron's last run time.
     */
    private function getStoragePath(): string
    {
        return APP_CACHE_DIR . "/cron/{$this->identifier}.mydtt";
    }
}
