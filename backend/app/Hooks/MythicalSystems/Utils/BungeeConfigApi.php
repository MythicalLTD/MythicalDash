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

namespace MythicalDash\Hooks\MythicalSystems\Utils;

use Symfony\Component\Yaml\Yaml;

class BungeeConfigApi
{
    public string $fileName;
    private BungeeConfigApi $instance;

    public function __construct(string $fileName)
    {
        $this->instance = $this;
        $this->fileName = $fileName;
        if (!file_exists($fileName)) {
            throw new \Exception("File '$fileName' not found.");
        }
        $this->renameYAMLToYML();

        $this->checkSyntax($fileName);
    }

    /**
     * Get a string.
     *
     * @param string $key key
     */
    public function getString(string $key): ?string
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return is_string($value);
    }

    /**
     * Get an integer.
     *
     * @param string $key key
     *
     * @return bool|null The integer value
     */
    public function getInt(string $key): ?int
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return is_int($value);
    }

    /**
     * Get a boolean.
     *
     * @param string $key key
     *
     * @return bool|null The boolean value
     */
    public function getBool(string $key): ?bool
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return is_bool($value);
    }

    /**
     * Get an array.
     *
     * @param string $key key
     *
     * @return array|null The array value
     */
    public function getArray(string $key): ?array
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return is_array($value) ? $value : null;
    }

    /**
     * Get a value from the YAML file.
     *
     * @param string $key The key to get
     *
     * @return mixed The value
     */
    public function get(string $key): mixed
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Set a value in the YAML file.
     *
     * @param string $key The key to set
     * @param mixed $value The value to set
     */
    public function set(string $key, mixed $value): void
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return;
            }
        }
        $yaml[$key] = $value;
        file_put_contents($file, Yaml::dump($yaml));
    }

    /**
     * Set a string.
     *
     * @param string $key key
     * @param string $value value
     */
    public function setString(string $key, string $value): void
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return;
            }
        }
        $yaml[$key] = $value;
        file_put_contents($file, Yaml::dump($yaml));
    }

    /**
     * Set an integer.
     *
     * @param string $key key
     * @param int $value value
     */
    public function setInt(string $key, int $value): void
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return;
            }
        }
        $yaml[$key] = $value;
        file_put_contents($file, Yaml::dump($yaml));
    }

    /**
     * Set a boolean.
     *
     * @param string $key key
     * @param bool $value value
     */
    public function setBool(string $key, bool $value): void
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return;
            }
        }
        $yaml[$key] = $value;
        file_put_contents($file, Yaml::dump($yaml));
    }

    /**
     * Set an array.
     *
     * @param string $key key
     * @param array $value value
     */
    public function setArray(string $key, array $value): void
    {
        $file = __DIR__ . '/' . $this->fileName;
        $yaml = Yaml::parseFile($file);
        $keys = explode('.', $key);
        $value = $yaml;
        foreach ($keys as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return;
            }
        }
        $yaml[$key] = $value;
        file_put_contents($file, Yaml::dump($yaml));
    }

    /**
     * Get the instance of the BungeeConfigApi.
     *
     * @return BungeeConfigApi The instance of the BungeeConfigApi
     */
    public static function getInstance($fileName): BungeeConfigApi
    {
        self::$fileName = $fileName;

        return self::$instance;
    }

    /**
     * Check the syntax of the file!
     *
     * @param string $file The file to check
     */
    private function checkSyntax(string $file): void
    {
        $yaml = Yaml::parseFile($file);
        if ($yaml == null) {
            throw new \Exception('Language file syntax is invalid!');
        }
    }

    /**
     * Rename all YAML files to YML.
     */
    private function renameYAMLToYML(): void
    {
        $files = scandir(__DIR__);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'yaml') {
                $new_file = str_replace('.yaml', '.yml', $file);
                rename(__DIR__ . '/' . $file, __DIR__ . '/' . $new_file);
            }
        }
    }
}
