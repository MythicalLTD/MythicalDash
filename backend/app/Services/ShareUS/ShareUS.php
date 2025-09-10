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

namespace MythicalDash\Services\ShareUS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class ShareUS
{
    private const BASE_URL = 'https://api.shareus.io';
    private const RATE_LIMIT = 60; // requests per minute
    private string $apiKey;
    private Client $client;
    private array $rateLimitInfo = [
        'requests' => 0,
        'lastReset' => 0,
    ];

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout' => 10.0,
            'headers' => [
                'User-Agent' => 'MythicalDash/1.0',
                'Accept' => 'text/plain',
            ],
        ]);
    }

    /**
     * Get a shortened URL using ShareUS API.
     *
     * @param string $url The URL to shorten
     *
     * @throws \Exception If the API request fails or rate limit is exceeded
     *
     * @return string The shortened URL
     */
    public function getLink(string $url): string
    {
        $this->checkRateLimit();

        try {
            $response = $this->client->get('/easy_api', [
                'query' => [
                    'key' => $this->apiKey,
                    'link' => $url,
                ],
            ]);

            $this->updateRateLimit();

            $shortUrl = (string) $response->getBody();

            if (strpos($shortUrl, 'error/') === 0) {
                throw new \Exception('ShareUS API Error: ' . substr($shortUrl, 6));
            }

            return $shortUrl;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();

                if ($statusCode === 429) {
                    throw new \Exception('Rate limit exceeded. Please try again later.');
                }

                throw new \Exception("ShareUS API Error: HTTP {$statusCode} - " . $response->getBody());
            }
            throw new \Exception('ShareUS API Error: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new \Exception('ShareUS API Error: ' . $e->getMessage());
        }
    }

    /**
     * Get current rate limit information.
     *
     * @return array Rate limit information
     */
    public function getRateLimitInfo(): array
    {
        return [
            'requests' => $this->rateLimitInfo['requests'],
            'limit' => self::RATE_LIMIT,
            'remaining' => self::RATE_LIMIT - $this->rateLimitInfo['requests'],
            'reset' => $this->rateLimitInfo['lastReset'] + 60 - time(),
        ];
    }

    /**
     * Check if we're within rate limits.
     *
     * @throws \Exception If rate limit is exceeded
     */
    private function checkRateLimit(): void
    {
        $now = time();

        // Reset counter if a minute has passed
        if ($now - $this->rateLimitInfo['lastReset'] >= 60) {
            $this->rateLimitInfo['requests'] = 0;
            $this->rateLimitInfo['lastReset'] = $now;
        }

        if ($this->rateLimitInfo['requests'] >= self::RATE_LIMIT) {
            throw new \Exception('Rate limit exceeded. Please try again later.');
        }
    }

    /**
     * Update rate limit counter.
     */
    private function updateRateLimit(): void
    {
        ++$this->rateLimitInfo['requests'];
        if ($this->rateLimitInfo['lastReset'] === 0) {
            $this->rateLimitInfo['lastReset'] = time();
        }
    }
}
