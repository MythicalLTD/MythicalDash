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

/**
 * Base interface for all plugin mixins.
 *
 * Mixins provide reusable functionality that can be included in multiple plugins.
 * They allow for better code organization and reuse across the plugin ecosystem.
 */
interface MythicalDashMixin
{
    /**
     * Initialize the mixin with the plugin identifier.
     *
     * @param string $pluginIdentifier The identifier of the plugin using this mixin
     * @param array $config Optional configuration for the mixin
     */
    public function initialize(string $pluginIdentifier, array $config = []): void;

    /**
     * Get the unique identifier for this mixin.
     *
     * @return string The mixin identifier
     */
    public static function getMixinIdentifier(): string;

    /**
     * Get the version of this mixin.
     *
     * @return string The mixin version
     */
    public static function getMixinVersion(): string;
}
