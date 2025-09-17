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

namespace MythicalDash\Hooks;

use GuzzleHttp\Client;
use MythicalDash\Cache\Cache;

class GitHub
{
    private $cacheKey = 'github_repo_data';
    private $cacheTTL = 60; // 1 minute in seconds
    private $client;
    // Cache key for releases list
    private $releasesCacheKey = 'github_repo_releases';

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Retrieves repository data from GitHub API, using cache if available.
     *
     * @return array the repository data
     */
    public function getRepoData()
    {
        // Check if data is cached
        if (Cache::exists($this->cacheKey)) {
            return Cache::getJson($this->cacheKey);
        }

        // Make GET request to GitHub API
        $response = $this->client->request('GET', 'https://api.github.com/repos/mythicalltd/mythicaldash', [
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent' => 'MythicalDash',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        // Cache the response
        Cache::putJson($this->cacheKey, $data, $this->cacheTTL);

        return $data;
    }

    /**
     * Retrieves releases (changelogs) from GitHub API, using cache if available.
     *
     * @param int $perPage Number of releases to fetch (GitHub max 100)
     *
     * @return array list of release objects as associative arrays
     */
    public function getReleases(int $perPage = 20)
    {
        $perPage = max(1, min(100, $perPage));
        $cacheKey = $this->releasesCacheKey . ':' . $perPage;

        if (Cache::exists($cacheKey)) {
            return Cache::getJson($cacheKey);
        }

        try {
            $response = $this->client->request('GET', 'https://api.github.com/repos/mythicalltd/mythicaldash/releases', [
                'headers' => [
                    // Request GitHub-rendered HTML in addition to JSON fields
                    'Accept' => 'application/vnd.github.v3.html+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'User-Agent' => 'MythicalDash',
                ],
                'query' => [
                    'per_page' => $perPage,
                    'page' => 1,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (!is_array($data)) {
                $data = [];
            }

            Cache::putJson($cacheKey, $data, $this->cacheTTL);

            return $data;
        } catch (\Throwable $e) {
            // On error, return cached data if any, else empty array
            if (Cache::exists($cacheKey)) {
                return Cache::getJson($cacheKey);
            }

            return [];
        }
    }
}
