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

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use MythicalDash\Services\Pterodactyl\Wings\WingsClient;

class FileManager extends WingsClient
{
    /**
     * List files in a directory.
     *
     * @param string $serverId Server identifier
     * @param string $directory Directory path
     *
     * @return array List of files and directories
     */
    public function listFiles(string $serverId, string $directory = '/'): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/files/list", [
            'query' => ['directory' => $directory],
        ]);
    }

    /**
     * Get contents of a file.
     *
     * @param string $serverId Server identifier
     * @param string $file File path
     *
     * @return array File contents and metadata
     */
    public function getFileContents(string $serverId, string $file): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/files/contents", [
            'query' => ['file' => $file],
        ]);
    }

    /**
     * Write contents to a file.
     *
     * @param string $serverId Server identifier
     * @param string $file File path
     * @param string $contents File contents
     *
     * @return array Response data
     */
    public function writeFileContents(string $serverId, string $file, string $contents): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/files/write", [
            'query' => ['file' => $file],
            'body' => $contents,
        ]);
    }

    /**
     * Rename/move a file or directory.
     *
     * @param string $serverId Server identifier
     * @param string $from Source path
     * @param string $to Destination path
     *
     * @return array Response data
     */
    public function rename(string $serverId, string $from, string $to): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/files/rename", [
            'json' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    /**
     * Copy a file or directory.
     *
     * @param string $serverId Server identifier
     * @param string $location Path to copy
     *
     * @return array Response data
     */
    public function copy(string $serverId, string $location): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/files/copy", [
            'json' => ['location' => $location],
        ]);
    }

    /**
     * Delete files or directories.
     *
     * @param string $serverId Server identifier
     * @param array $files Array of file paths to delete
     *
     * @return array Response data
     */
    public function delete(string $serverId, array $files): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/files/delete", [
            'json' => ['files' => $files],
        ]);
    }

    /**
     * Create a compressed archive.
     *
     * @param string $serverId Server identifier
     * @param array $files Files to compress
     * @param string $root Root directory
     *
     * @return array Response data
     */
    public function compress(string $serverId, array $files, string $root): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/files/compress", [
            'json' => [
                'files' => $files,
                'root' => $root,
            ],
        ]);
    }

    /**
     * Extract a compressed archive.
     *
     * @param string $serverId Server identifier
     * @param string $file Archive file path
     *
     * @return array Response data
     */
    public function decompress(string $serverId, string $file): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/files/decompress", [
            'json' => ['file' => $file],
        ]);
    }
}
