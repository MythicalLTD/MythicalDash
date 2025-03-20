<?php

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Pterodactyl\Exceptions\ServerException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class ServersResource extends PterodactylAdmin
{
	/**
	 * List all servers
	 *
	 * @param int $page
	 * @param int $perPage
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws RateLimitException
	 */
	public function listServers(int $page = 1, int $perPage = 50): array
	{
		try {
			return $this->request('GET', '/api/application/servers', [
				'query' => [
					'page' => $page,
					'per_page' => $perPage,
				],
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
	 * Get a specific server
	 *
	 * @param int $serverId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws RateLimitException
	 */
	public function getServer(int $serverId): array
	{
		try {
			return $this->request('GET', "/api/application/servers/{$serverId}");
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Get server details
	 *
	 * @param int $serverId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws RateLimitException
	 */
	public function getServerDetails(int $serverId): array
	{
		try {
			return $this->request('GET', "/api/application/servers/{$serverId}/details");
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Create a new server
	 *
	 * @param array $data
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ValidationException
	 * @throws RateLimitException
	 */
	public function createServer(array $data): array
	{
		try {
			return $this->request('POST', '/api/application/servers', [
				'json' => $data,
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
	 * Update server details
	 *
	 * @param int $serverId
	 * @param array $data
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws ValidationException
	 * @throws RateLimitException
	 */
	public function updateServerDetails(int $serverId, array $data): array
	{
		try {
			return $this->request('PATCH', "/api/application/servers/{$serverId}/details", [
				'json' => $data,
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
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
	 * Update server build configuration
	 *
	 * @param int $serverId
	 * @param array $data
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws ValidationException
	 * @throws RateLimitException
	 */
	public function updateServerBuild(int $serverId, array $data): array
	{
		try {
			return $this->request('PATCH', "/api/application/servers/{$serverId}/build", [
				'json' => $data,
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Update server startup configuration
	 *
	 * @param int $serverId
	 * @param array $data
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws ValidationException
	 * @throws RateLimitException
	 */
	public function updateServerStartup(int $serverId, array $data): array
	{
		try {
			return $this->request('PATCH', "/api/application/servers/{$serverId}/startup", [
				'json' => $data,
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Delete a server
	 *
	 * @param int $serverId
	 * @param bool $force
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws RateLimitException
	 */
	public function deleteServer(int $serverId, bool $force = false): array
	{
		$endpoint = "/api/application/servers/{$serverId}";
		if ($force) {
			$endpoint .= '/force';
		}
		try {
			return $this->request('DELETE', $endpoint);
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Suspend a server
	 *
	 * @param int $serverId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws ServerException
	 * @throws RateLimitException
	 */
	public function suspendServer(int $serverId): array
	{
		try {
			return $this->request('POST', "/api/application/servers/{$serverId}/suspend");
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 409) {
				throw ServerException::powerActionInProgress();
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Unsuspend a server
	 *
	 * @param int $serverId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws ServerException
	 * @throws RateLimitException
	 */
	public function unsuspendServer(int $serverId): array
	{
		try {
			return $this->request('POST', "/api/application/servers/{$serverId}/unsuspend");
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}


	/**
	 * Reinstall a server
	 *
	 * @param int $serverId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws ServerException
	 * @throws RateLimitException
	 */
	public function reinstallServer(int $serverId): array
	{
		try {
			return $this->request('POST', "/api/application/servers/{$serverId}/reinstall");
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 409) {
				throw ServerException::installationInProgress();
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Get server database
	 *
	 * @param int $serverId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws RateLimitException
	 */
	public function getServerDatabase(int $serverId): array
	{
		try {
			return $this->request('GET', "/api/application/servers/{$serverId}/database");
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Create server database
	 *
	 * @param int $serverId
	 * @param array $data
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws ValidationException
	 * @throws RateLimitException
	 */
	public function createServerDatabase(int $serverId, array $data): array
	{
		try {
			return $this->request('POST', "/api/application/servers/{$serverId}/database", [
				'json' => $data,
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
				throw ResourceNotFoundException::forResource('server', (string) $serverId);
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
	 * Reset server database password
	 *
	 * @param int $serverId
	 * @param string $databaseId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws RateLimitException
	 */
	public function resetServerDatabasePassword(int $serverId, string $databaseId): array
	{
		try {
			return $this->request('POST', "/api/application/servers/{$serverId}/database/{$databaseId}/reset-password");
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
				throw ResourceNotFoundException::forResource('server database', $databaseId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}

	/**
	 * Delete server database
	 *
	 * @param int $serverId
	 * @param string $databaseId
	 * @return array
	 * 
	 * @throws AuthenticationException
	 * @throws PermissionException
	 * @throws ResourceNotFoundException
	 * @throws RateLimitException
	 */
	public function deleteServerDatabase(int $serverId, string $databaseId): array
	{
		try {
			return $this->request('DELETE', "/api/application/servers/{$serverId}/database/{$databaseId}");
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
				throw ResourceNotFoundException::forResource('server database', $databaseId);
			}

			if ($statusCode === 429) {
				$retryAfter = (int) $response->getHeaderLine('Retry-After');
				throw RateLimitException::withRetryAfter($retryAfter);
			}

			throw $e;
		}
	}
}