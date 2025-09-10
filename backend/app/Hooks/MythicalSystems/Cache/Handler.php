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

namespace MythicalDash\Hooks\MythicalSystems\Cache;

class Handler
{
    private $cacheFile;

    /**
     * Constructor to initialize the cache file path.
     *
     * @param string $cacheFile the path to the cache file
     */
    public function __construct($cacheFile)
    {
        $this->cacheFile = $cacheFile;
        if (!file_exists($this->cacheFile)) {
            $this->createCacheFile();
        }
    }

    /**
     * Set a value in the cache with a specified expiration time.
     *
     * @param string $key the key to store the value under
     * @param mixed $value the value to store in the cache
     * @param int $expiryTimestamp the Unix timestamp indicating when the cache entry will expire
     *
     * @return void
     */
    public function set($key, $value, $expiryTimestamp)
    {
        $cacheData = $this->loadCache();
        $cacheData[$key] = [
            'value' => $value,
            'expiry' => $expiryTimestamp,
        ];
        $this->saveCache($cacheData);
    }

    /**
     * Get a value from the cache if it exists and is not expired.
     *
     * @param string $key the key of the value to retrieve
     *
     * @return mixed|null the cached value if it exists and is not expired, or null otherwise
     */
    public function get($key)
    {
        $cacheData = $this->loadCache();
        if (isset($cacheData[$key]) && $cacheData[$key]['expiry'] > time()) {
            return $cacheData[$key]['value'];
        }

        return null;
    }

    /**
     * Update an existing cache entry with a new value and expiration time.
     *
     * @param string $key the key of the cache entry to update
     * @param mixed $value the new value to set for the cache entry
     * @param int $expiryTimestamp the new expiration timestamp for the cache entry
     *
     * @return void
     */
    public function update($key, $value, $expiryTimestamp)
    {
        $cacheData = $this->loadCache();
        if (isset($cacheData[$key])) {
            $cacheData[$key]['value'] = $value;
            $cacheData[$key]['expiry'] = $expiryTimestamp;
            $this->saveCache($cacheData);
        }
    }

    /**
     * Delete a cache entry by key.
     *
     * @param string $key the key of the cache entry to delete
     *
     * @return void
     */
    public function delete($key)
    {
        $cacheData = $this->loadCache();
        if (isset($cacheData[$key])) {
            unset($cacheData[$key]);
            $this->saveCache($cacheData);
        }
    }

    /**
     * Remove expired cache entries.
     *
     * @return void
     */
    public function process()
    {
        $cacheData = $this->loadCache();
        foreach ($cacheData as $key => $entry) {
            if ($entry['expiry'] <= time()) {
                unset($cacheData[$key]);
            }
        }
        $this->saveCache($cacheData);
    }

    /**
     * Purge the entire cache, removing all entries.
     *
     * @return void
     */
    public function purge()
    {
        $this->saveCache([]);
    }

    /**
     * Create the cache file with appropriate permissions.
     *
     * @return void
     */
    private function createCacheFile()
    {
        touch($this->cacheFile); // Create the file if it doesn't exist
    }

    /**
     * Load cache data from the cache file.
     *
     * @return array the cache data
     */
    private function loadCache()
    {
        if (file_exists($this->cacheFile)) {
            $contents = file_get_contents($this->cacheFile);

            return json_decode($contents, true);
        }

        return [];
    }

    /**
     * Save cache data to the cache file.
     *
     * @param array $data the cache data to save
     *
     * @return void
     */
    private function saveCache($data)
    {
        file_put_contents($this->cacheFile, json_encode($data));
    }
}
