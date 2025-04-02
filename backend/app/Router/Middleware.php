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
