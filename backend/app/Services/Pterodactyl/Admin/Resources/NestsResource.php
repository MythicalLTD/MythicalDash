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

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class NestsResource extends PterodactylAdmin
{
    /**
     * List all nests.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listNests(int $page = 1, int $perPage = 50, array $includes = ['eggs', 'servers']): array
    {
        try {
            $query = [
                'page' => $page,
                'per_page' => $perPage,
            ];

            if (!empty($includes)) {
                $query['include'] = implode(',', $includes);
            }

            return $this->request('GET', '/api/application/nests', [
                'query' => $query,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::adminRequired();
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get a specific nest.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNest(int $nestId, array $includes = ['eggs', 'servers']): array
    {
        try {
            $query = [
                'include' => implode(',', $includes),
            ];

            return $this->request('GET', "/api/application/nests/{$nestId}", [
                'query' => $query,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::adminRequired();
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('nest', (string) $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * List all eggs in a nest.
     *
     * @param int $nestId The ID of the nest
     * @param int $page Page number for pagination
     * @param int $perPage Number of items per page
     * @param array $includes Additional data to include in the response (nest, servers, config, script, variables)
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listEggs(int $nestId, int $page = 1, int $perPage = 50, array $includes = ['nest', 'servers', 'config', 'script', 'variables']): array
    {
        try {
            $query = [
                'page' => $page,
                'per_page' => $perPage,
            ];

            if (!empty($includes)) {
                $query['include'] = implode(',', $includes);
            }

            return $this->request('GET', "/api/application/nests/{$nestId}/eggs", [
                'query' => $query,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::adminRequired();
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('nest', (string) $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get a specific egg.
     *
     * @param int $nestId The ID of the nest
     * @param int $eggId The ID of the egg
     * @param array $includes Additional data to include in the response (nest, servers, config, script, variables)
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getEgg(int $nestId, int $eggId, array $includes = ['nest', 'servers', 'config', 'script', 'variables']): array
    {
        try {
            $query = [];
            if (!empty($includes)) {
                $query['include'] = implode(',', $includes);
            }

            return $this->request('GET', "/api/application/nests/{$nestId}/eggs/{$eggId}", [
                'query' => $query,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::adminRequired();
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('egg', (string) $eggId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
