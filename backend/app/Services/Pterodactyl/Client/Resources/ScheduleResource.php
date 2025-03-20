<?php

namespace MythicalDash\Services\Pterodactyl\Client\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Client\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class ScheduleResource extends PterodactylClient
{
    /**
     * List schedules for a server
     *
     * @param string $serverId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listSchedules(string $serverId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/schedules");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_schedules');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get specific schedule
     *
     * @param string $serverId
     * @param string $scheduleId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getSchedule(string $serverId, string $scheduleId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/schedules/{$scheduleId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_schedule');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('schedule', $scheduleId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create new schedule
     *
     * @param string $serverId
     * @param string $name
     * @param bool $isActive
     * @param string $minute
     * @param string $hour
     * @param string $dayOfWeek
     * @param string $dayOfMonth
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createSchedule(
        string $serverId,
        string $name,
        bool $isActive,
        string $minute = '*',
        string $hour = '*',
        string $dayOfWeek = '*',
        string $dayOfMonth = '*'
    ): array {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/schedules", [
                'json' => [
                    'name' => $name,
                    'is_active' => $isActive,
                    'minute' => $minute,
                    'hour' => $hour,
                    'day_of_week' => $dayOfWeek,
                    'day_of_month' => $dayOfMonth,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_schedule');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
            }

            if ($statusCode === 422) {
                $errors = json_decode($response->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Update schedule
     *
     * @param string $serverId
     * @param string $scheduleId
     * @param array $data
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateSchedule(string $serverId, string $scheduleId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/client/servers/{$serverId}/schedules/{$scheduleId}", [
                'json' => $data,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_schedule');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('schedule', $scheduleId);
            }

            if ($statusCode === 422) {
                $errors = json_decode($response->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete schedule
     *
     * @param string $serverId
     * @param string $scheduleId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteSchedule(string $serverId, string $scheduleId): array
    {
        try {
            return $this->request('DELETE', "/api/client/servers/{$serverId}/schedules/{$scheduleId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_schedule');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('schedule', $scheduleId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create task
     *
     * @param string $serverId
     * @param string $scheduleId
     * @param string $action
     * @param string $payload
     * @param int $timeOffset
     * @param bool $continueOnFailure
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createTask(
        string $serverId,
        string $scheduleId,
        string $action,
        string $payload,
        int $timeOffset = 0,
        bool $continueOnFailure = false
    ): array {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/schedules/{$scheduleId}/tasks", [
                'json' => [
                    'action' => $action,
                    'payload' => $payload,
                    'time_offset' => $timeOffset,
                    'continue_on_failure' => $continueOnFailure,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_task');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('schedule', $scheduleId);
            }

            if ($statusCode === 422) {
                $errors = json_decode($response->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Update task
     *
     * @param string $serverId
     * @param string $scheduleId
     * @param string $taskId
     * @param array $data
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateTask(string $serverId, string $scheduleId, string $taskId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/client/servers/{$serverId}/schedules/{$scheduleId}/tasks/{$taskId}", [
                'json' => $data,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_task');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('task', $taskId);
            }

            if ($statusCode === 422) {
                $errors = json_decode($response->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete task
     *
     * @param string $serverId
     * @param string $scheduleId
     * @param string $taskId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteTask(string $serverId, string $scheduleId, string $taskId): array
    {
        try {
            return $this->request('DELETE', "/api/client/servers/{$serverId}/schedules/{$scheduleId}/tasks/{$taskId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_task');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('task', $taskId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
} 