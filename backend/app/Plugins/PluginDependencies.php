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

namespace MythicalDash\Plugins;

use MythicalDash\Plugins\Dependencies\ComposerDependencies;
use MythicalDash\Plugins\Dependencies\PhpVersionDependencies;
use MythicalDash\Plugins\Dependencies\MythicalDashDependencies;
use MythicalDash\Plugins\Dependencies\PhpExtensionDependencies;

class PluginDependencies
{
    public static function checkDependencies(array $dependencies): bool
    {
        $requirements = $dependencies['plugin']['dependencies'];
        foreach ($requirements as $dependency) {
            // Check if the requirement is a composer package
            if (strpos($dependency, 'composer=') === 0) {
                $composerVersion = substr($dependency, strlen('composer='));
                if (!ComposerDependencies::isInstalled($composerVersion)) {
                    return false;
                }
            }

            // Check if the requirement is a php version
            if (strpos($dependency, 'php=') === 0) {
                $phpVersion = substr($dependency, strlen('php='));
                if (!PhpVersionDependencies::isInstalled($phpVersion)) {
                    return false;
                }
            }

            // Check if the requirement is a php extension
            if (strpos($dependency, 'php-ext=') === 0) {
                $ext = substr($dependency, strlen('php-ext='));
                if (!PhpExtensionDependencies::isInstalled($ext)) {
                    return false;
                }
            }

            // Check if the requirement is a plugin
            if (strpos($dependency, 'plugin=') === 0) {
                $plugin = substr($dependency, strlen('plugin='));
                if (!MythicalDashDependencies::isInstalled($plugin)) {
                    return false;
                }
            }
        }

        return true;
    }
}
