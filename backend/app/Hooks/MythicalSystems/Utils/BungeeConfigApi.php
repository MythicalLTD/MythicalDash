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
