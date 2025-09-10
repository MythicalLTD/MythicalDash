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

namespace MythicalDash\Router;

/**
 * Middleware class for handling route middleware functionality.
 */
class Middleware
{
    /** @var array<string, callable[]> Registered middleware */
    private static array $middlewares = [];

    /**
     * Register a middleware.
     *
     * @param string $name The middleware name
     * @param callable $callback The middleware function
     */
    public static function register(string $name, callable $callback): void
    {
        self::$middlewares[$name] = $callback;
    }

    /**
     * Get a registered middleware.
     *
     * @param string $name The middleware name
     *
     * @return callable|null The middleware callback or null if not found
     */
    public static function get(string $name): ?callable
    {
        return self::$middlewares[$name] ?? null;
    }

    /**
     * Apply middlewares to a route callback.
     *
     * @param callable $callback The original route callback
     * @param array $middlewareNames Array of middleware names to apply
     *
     * @return callable The wrapped route callback
     */
    public static function apply(callable $callback, array $middlewareNames): callable
    {
        // Create a wrapper function to execute middleware chain
        return function () use ($callback, $middlewareNames) {
            // Create middleware stack
            $stack = function () use ($callback) {
                return call_user_func_array($callback, func_get_args());
            };

            // Wrap the callback with each middleware in reverse order
            foreach (array_reverse($middlewareNames) as $name) {
                $middleware = self::get($name);
                if ($middleware) {
                    // Create a new stack with this middleware
                    $previousStack = $stack;
                    $stack = function () use ($middleware, $previousStack) {
                        return $middleware($previousStack, func_get_args());
                    };
                }
            }

            // Execute the stack with the original arguments
            return call_user_func_array($stack, func_get_args());
        };
    }
}
