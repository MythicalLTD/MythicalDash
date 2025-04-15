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
 */

namespace MythicalDash\Hooks;

class MythicalAPP extends MythicalSystems\Api\Api
{
    /**
     * Return a 200 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function OK(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(200, null, $message, true, $extraContent);
    }

    /**
     * Return a 201 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function Created(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(201, 'The request has been fulfilled and a new resource has been created.', $message, true, $extraContent);
    }

    /**
     * Return a 204 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function NoContent(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(204, 'The server has successfully fulfilled the request and there is no content to send in the response.', $message, true, $extraContent);
    }

    /**
     * Return a 400 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function BadRequest(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(400, 'The server cannot process the request due to a client error (e.g., malformed syntax).', $message, false, $extraContent);
    }

    /**
     * Return a 401 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function Unauthorized(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(401, 'The client must authenticate itself to get the requested response.', $message, false, $extraContent);
    }

    /**
     * Return a 403 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function Forbidden(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(403, 'The server understood the request, but refuses to authorize it.', $message, false, $extraContent);
    }

    /**
     * Return a 404 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function NotFound(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(404, 'The requested resource could not be found on the server.', $message, false, $extraContent);
    }

    /**
     * Return a 405 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function MethodNotAllowed(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(405, 'The method specified in the request is not allowed for the resource identified by the request URI.', $message, false, $extraContent);
    }

    /**
     * Return a 500 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function InternalServerError(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(500, 'A generic error message, given when an unexpected condition was encountered and no more specific message is suitable.', $message, false, $extraContent);
    }

    /**
     * Return a 502 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function BadGateway(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(502, 'The server, while acting as a gateway or proxy, received an invalid response from the upstream server it accessed in attempting to fulfill the request.', $message, false, $extraContent);
    }

    /**
     * Return a 503 response.
     *
     * @return void Returns a void so nothing it will die!
     */
    public static function ServiceUnavailable(?string $message, ?array $extraContent): void
    {
        self::sendManualResponse(503, 'The server is currently unable to handle the request due to temporary overloading or maintenance of the server.', $message, false, $extraContent);
    }

    /**
     * Send a manual response.
     *
     * @param int $code The HTTP status code
     * @param string|null $error The error message
     * @param string|null $message The message
     * @param bool|null $success If the request was successful
     * @param array|null $extraContent Extra content to send
     */
    public static function sendManualResponse(int $code, ?string $error, ?string $message, ?bool $success, ?array $extraContent): void
    {
        $response = [
            'code' => $code,
            'error' => $error,
            'message' => $message,
            'success' => $success,
            'core' => [
                'debug_os' => SYSTEM_OS_NAME,
                'debug_os_kernel' => SYSTEM_KERNEL_NAME,
                'debug_name' => 'MythicalDash',
                'debug_debug' => APP_DEBUG,
                'debug_version' => APP_VERSION,
                'debug_telemetry' => TELEMETRY,
                'debug' => [
                    'useRedis' => REDIS_ENABLED,
                    'rateLimit' => [
                        'enabled' => REDIS_ENABLED,
                        'limit' => RATE_LIMIT,
                    ],
                ],
            ],
        ];

        if (!$extraContent == null) {
            $response = array_merge($response, $extraContent);
        }

        http_response_code($code);
        exit(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public static function getPost(string $key): string
    {
        return $_POST[$key] ?? '';
    }

    /**
     * Get a POST value with null safety.
     *
     * @param string $key The key to look up
     *
     * @return string|null Returns null if the key doesn't exist
     */
    public static function getPostOrNull(string $key): ?string
    {
        return $_POST[$key] ?? null;
    }

    public static function getGet(string $key): string
    {
        return $_GET[$key] ?? '';
    }

    /**
     * Get a GET value with null safety.
     *
     * @param string $key The key to look up
     *
     * @return string|null Returns null if the key doesn't exist
     */
    public static function getGetOrNull(string $key): ?string
    {
        return $_GET[$key] ?? null;
    }

    public static function getPut(string $key): string
    {
        $data = self::getRequestData();

        return $data[$key] ?? '';
    }

    /**
     * Get a PUT value with null safety.
     *
     * @param string $key The key to look up
     *
     * @return string|null Returns null if the key doesn't exist
     */
    public static function getPutOrNull(string $key): ?string
    {
        $data = self::getRequestData();

        return $data[$key] ?? null;
    }

    public static function getDelete(string $key): string
    {
        $data = self::getRequestData();

        return $data[$key] ?? '';
    }

    /**
     * Get a DELETE value with null safety.
     *
     * @param string $key The key to look up
     *
     * @return string|null Returns null if the key doesn't exist
     */
    public static function getDeleteOrNull(string $key): ?string
    {
        $data = self::getRequestData();

        return $data[$key] ?? null;
    }

    public static function getPatch(string $key): string
    {
        $data = self::getRequestData();

        return $data[$key] ?? '';
    }

    /**
     * Get a PATCH value with null safety.
     *
     * @param string $key The key to look up
     *
     * @return string|null Returns null if the key doesn't exist
     */
    public static function getPatchOrNull(string $key): ?string
    {
        $data = self::getRequestData();

        return $data[$key] ?? null;
    }

    public static function getPostInt(string $key): int
    {
        return intval($_POST[$key] ?? 0);
    }

    /**
     * Get a POST value as integer with null safety.
     *
     * @param string $key The key to look up
     *
     * @return int|null Returns null if the key doesn't exist
     */
    public static function getPostIntOrNull(string $key): ?int
    {
        return isset($_POST[$key]) ? intval($_POST[$key]) : null;
    }

    public static function getGetInt(string $key): int
    {
        return intval($_GET[$key] ?? 0);
    }

    /**
     * Get a GET value as integer with null safety.
     *
     * @param string $key The key to look up
     *
     * @return int|null Returns null if the key doesn't exist
     */
    public static function getGetIntOrNull(string $key): ?int
    {
        return isset($_GET[$key]) ? intval($_GET[$key]) : null;
    }

    public static function getPutInt(string $key): int
    {
        $data = self::getRequestData();

        return intval($data[$key] ?? 0);
    }

    /**
     * Get a PUT value as integer with null safety.
     *
     * @param string $key The key to look up
     *
     * @return int|null Returns null if the key doesn't exist
     */
    public static function getPutIntOrNull(string $key): ?int
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? intval($data[$key]) : null;
    }

    public static function getDeleteInt(string $key): int
    {
        $data = self::getRequestData();

        return intval($data[$key] ?? 0);
    }

    /**
     * Get a DELETE value as integer with null safety.
     *
     * @param string $key The key to look up
     *
     * @return int|null Returns null if the key doesn't exist
     */
    public static function getDeleteIntOrNull(string $key): ?int
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? intval($data[$key]) : null;
    }

    public static function getPatchInt(string $key): int
    {
        $data = self::getRequestData();

        return intval($data[$key] ?? 0);
    }

    /**
     * Get a PATCH value as integer with null safety.
     *
     * @param string $key The key to look up
     *
     * @return int|null Returns null if the key doesn't exist
     */
    public static function getPatchIntOrNull(string $key): ?int
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? intval($data[$key]) : null;
    }

    public static function getPostFloat(string $key): float
    {
        return floatval($_POST[$key] ?? 0);
    }

    /**
     * Get a POST value as float with null safety.
     *
     * @param string $key The key to look up
     *
     * @return float|null Returns null if the key doesn't exist
     */
    public static function getPostFloatOrNull(string $key): ?float
    {
        return isset($_POST[$key]) ? floatval($_POST[$key]) : null;
    }

    public static function getGetFloat(string $key): float
    {
        return floatval($_GET[$key] ?? 0);
    }

    /**
     * Get a GET value as float with null safety.
     *
     * @param string $key The key to look up
     *
     * @return float|null Returns null if the key doesn't exist
     */
    public static function getGetFloatOrNull(string $key): ?float
    {
        return isset($_GET[$key]) ? floatval($_GET[$key]) : null;
    }

    public static function getPutFloat(string $key): float
    {
        $data = self::getRequestData();

        return floatval($data[$key] ?? 0);
    }

    /**
     * Get a PUT value as float with null safety.
     *
     * @param string $key The key to look up
     *
     * @return float|null Returns null if the key doesn't exist
     */
    public static function getPutFloatOrNull(string $key): ?float
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? floatval($data[$key]) : null;
    }

    public static function getDeleteFloat(string $key): float
    {
        $data = self::getRequestData();

        return floatval($data[$key] ?? 0);
    }

    /**
     * Get a DELETE value as float with null safety.
     *
     * @param string $key The key to look up
     *
     * @return float|null Returns null if the key doesn't exist
     */
    public static function getDeleteFloatOrNull(string $key): ?float
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? floatval($data[$key]) : null;
    }

    public static function getPatchFloat(string $key): float
    {
        $data = self::getRequestData();

        return floatval($data[$key] ?? 0);
    }

    /**
     * Get a PATCH value as float with null safety.
     *
     * @param string $key The key to look up
     *
     * @return float|null Returns null if the key doesn't exist
     */
    public static function getPatchFloatOrNull(string $key): ?float
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? floatval($data[$key]) : null;
    }

    public static function getPostBool(string $key): bool
    {
        return boolval($_POST[$key] ?? false);
    }

    /**
     * Get a POST value as boolean with null safety.
     *
     * @param string $key The key to look up
     *
     * @return bool|null Returns null if the key doesn't exist
     */
    public static function getPostBoolOrNull(string $key): ?bool
    {
        return isset($_POST[$key]) ? boolval($_POST[$key]) : null;
    }

    public static function getGetBool(string $key): bool
    {
        return boolval($_GET[$key] ?? false);
    }

    /**
     * Get a GET value as boolean with null safety.
     *
     * @param string $key The key to look up
     *
     * @return bool|null Returns null if the key doesn't exist
     */
    public static function getGetBoolOrNull(string $key): ?bool
    {
        return isset($_GET[$key]) ? boolval($_GET[$key]) : null;
    }

    public static function getPutBool(string $key): bool
    {
        $data = self::getRequestData();

        return boolval($data[$key] ?? false);
    }

    /**
     * Get a PUT value as boolean with null safety.
     *
     * @param string $key The key to look up
     *
     * @return bool|null Returns null if the key doesn't exist
     */
    public static function getPutBoolOrNull(string $key): ?bool
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? boolval($data[$key]) : null;
    }

    public static function getDeleteBool(string $key): bool
    {
        $data = self::getRequestData();

        return boolval($data[$key] ?? false);
    }

    /**
     * Get a DELETE value as boolean with null safety.
     *
     * @param string $key The key to look up
     *
     * @return bool|null Returns null if the key doesn't exist
     */
    public static function getDeleteBoolOrNull(string $key): ?bool
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? boolval($data[$key]) : null;
    }

    public static function getPatchBool(string $key): bool
    {
        $data = self::getRequestData();

        return boolval($data[$key] ?? false);
    }

    /**
     * Get a PATCH value as boolean with null safety.
     *
     * @param string $key The key to look up
     *
     * @return bool|null Returns null if the key doesn't exist
     */
    public static function getPatchBoolOrNull(string $key): ?bool
    {
        $data = self::getRequestData();

        return isset($data[$key]) ? boolval($data[$key]) : null;
    }

    /**
     * Get the request data from the input stream for non-GET/POST requests.
     *
     * @return array The parsed request data
     */
    private static function getRequestData(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
            $input = file_get_contents('php://input');
            $data = [];

            // Parse the input as JSON if Content-Type is application/json
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode($input, true) ?? [];
            } else {
                // Parse as form data
                parse_str($input, $data);
            }

            return $data;
        }

        return [];
    }
}
