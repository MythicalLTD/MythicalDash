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
