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

namespace MythicalDash\Hooks\MythicalSystems\Helpers;

final class GarbageCollector
{
    /**
     * Throw away a value.
     *
     * @param mixed $value The value to throw away
     *
     * @return void
     */
    public static function throwAway(mixed $value)
    {
        unset($value);
    }

    /**
     * Throw away all values in an array.
     *
     * @param array $values The values to throw away
     *
     * @return void
     */
    public static function throwAwayAll(array $values)
    {
        foreach ($values as $value) {
            self::throwAway($value);
        }
    }

    /**
     * Call the garbage collector to free up memory.
     *
     * @return void
     */
    public static function callGarbageCollector()
    {
        gc_enable();
        gc_mem_caches();
        gc_collect_cycles();
        gc_disable();
    }

    /**
     * Delete all files in a directory.
     *
     * @param string $directory The directory to delete all files in
     *
     * @return void
     */
    public static function deleteAllFilesInDirectory(string $directory)
    {
        $files = new \FilesystemIterator($directory);
        foreach ($files as $file) {
            unlink($file->getPathname());
        }
    }

    /**
     * Delete all files in a directory recursively.
     *
     * @param string $directory The directory to delete all files in
     *
     * @return void
     */
    public static function deleteAllFilesInDirectoryRecursively(string $directory)
    {
        $files = new \FilesystemIterator($directory);
        foreach ($files as $file) {
            if ($file->isDir()) {
                self::deleteAllFilesInDirectoryRecursively($file->getPathname());
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
    }
}
