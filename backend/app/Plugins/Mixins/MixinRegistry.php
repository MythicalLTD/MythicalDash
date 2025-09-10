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

namespace MythicalDash\Plugins\Mixins;

use MythicalDash\App;

/**
 * Registry for system mixins.
 *
 * This class handles registering all built-in and custom mixins
 * during application startup.
 */
class MixinRegistry
{
    /**
     * Register all built-in and custom mixins.
     *
     * This should be called during application startup.
     */
    public static function registerMixins(): void
    {
        $logger = App::getInstance(true)->getLogger();
        $logger->debug('Registering mixins...');

        try {
            // Register built-in mixins
            self::registerBuiltInMixins();

            // Register custom mixins
            self::registerCustomMixins();

            $mixins = MixinManager::getRegisteredMixins();
            $logger->debug('Registered ' . count($mixins) . ' mixins: ' . implode(', ', $mixins));

        } catch (\Throwable $e) {
            $logger->error('Failed to register mixins: ' . $e->getMessage());
        }
    }

    /**
     * Register built-in mixins.
     */
    private static function registerBuiltInMixins(): void
    {
        $builtInMixins = [

        ];

        foreach ($builtInMixins as $mixinClass) {
            MixinManager::registerMixin($mixinClass);
        }
    }

    /**
     * Register custom mixins from the mixins directory.
     */
    private static function registerCustomMixins(): void
    {
        $logger = App::getInstance(true)->getLogger();
        $mixinsDir = dirname(__DIR__) . '/Mixins/Custom';

        if (!is_dir($mixinsDir)) {
            // Custom mixins directory doesn't exist yet
            return;
        }

        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($mixinsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $className = self::getClassNameFromFile($file->getPathname());
                    if ($className) {
                        MixinManager::registerMixin($className);
                    }
                }
            }
        } catch (\Throwable $e) {
            $logger->error('Failed to load custom mixins: ' . $e->getMessage());
        }
    }

    /**
     * Get the fully qualified class name from a file.
     *
     * @param string $file The file path
     *
     * @return string|null The class name or null if not found
     */
    private static function getClassNameFromFile(string $file): ?string
    {
        $logger = App::getInstance(true)->getLogger();

        try {
            $content = file_get_contents($file);
            if (!$content) {
                return null;
            }

            // Extract namespace
            $namespaceMatches = [];
            if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches) !== 1) {
                return null;
            }

            // Extract class name
            $classMatches = [];
            if (preg_match('/class\s+(\w+)/', $content, $classMatches) !== 1) {
                return null;
            }

            $namespace = trim($namespaceMatches[1]);
            $className = trim($classMatches[1]);

            $fullClassName = $namespace . '\\' . $className;

            // Check if the class exists and implements the mixin interface
            if (class_exists($fullClassName) && is_subclass_of($fullClassName, MythicalDashMixin::class)) {
                return $fullClassName;
            }

            return null;
        } catch (\Throwable $e) {
            $logger->error('Failed to get class name from file ' . $file . ': ' . $e->getMessage());

            return null;
        }
    }
}
