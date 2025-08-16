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

namespace MythicalDash\Cache;

/**
 * Class Cache.
 *
 * Provides simple file-based caching functionality.
 */
class Cache
{
    protected static $cacheDir = APP_CACHE_DIR . '/other';

    /**
     * Stores a value in the cache with a specified expiration time.
     *
     * @param string $key the cache key used to reference the data
     * @param mixed $value the data to be cached
     * @param int $minutes number of minutes before the data expires
     *
     * @return void
     */
    public static function put($key, $value, $minutes = 60)
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
        $filename = self::$cacheDir . '/' . md5($key);
        $data = [
            'expires' => time() + ($minutes * 60),
            'value' => $value,
        ];
        file_put_contents($filename, serialize($data));
    }

    /**
     * Retrieves a value from the cache by its key.
     *
     * @param string $key the cache key for the stored data
     *
     * @return mixed|null returns the stored data or null if not found or expired
     */
    public static function get($key)
    {
        $filename = self::$cacheDir . '/' . md5($key);
        if (!file_exists($filename)) {
            return null;
        }
        $data = unserialize(file_get_contents($filename));
        if (time() > $data['expires']) {
            unlink($filename);

            return null;
        }

        return $data['value'];
    }

    /**
     * Removes a cached entry by its key.
     *
     * @param string $key the cache key to remove
     *
     * @return void
     */
    public static function forget($key)
    {
        $filename = self::$cacheDir . '/' . md5($key);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Clears all entries in the cache directory.
     *
     * @return void
     */
    public static function clear()
    {
        $files = glob(self::$cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Checks if a valid cached entry exists for the specified key.
     *
     * @param string $key the cache key to check
     *
     * @return bool true if a valid cached entry exists, otherwise false
     */
    public static function exists($key)
    {
        $filename = self::$cacheDir . '/' . md5($key);
        if (!is_file($filename)) {
            return false;
        }
        $data = @unserialize(file_get_contents($filename));
        if (!is_array($data) || !isset($data['expires'])) {
            return false;
        }

        return time() <= $data['expires'];
    }

    /**
     * Stores JSON data in the cache with a specified expiration time.
     *
     * @param string $key the cache key used to reference the JSON data
     * @param mixed $json the JSON data to be cached
     * @param int $minutes number of minutes before the data expires
     *
     * @return void
     */
    public static function putJson($key, $json, $minutes = 60)
    {
        self::put($key, $json, $minutes);
    }

    /**
     * Retrieves a previously stored JSON data by its key.
     *
     * @param string $key the cache key for the JSON data
     *
     * @return mixed|null returns the JSON data or null if not found or expired
     */
    public static function getJson($key)
    {
        return self::get($key);
    }
}
