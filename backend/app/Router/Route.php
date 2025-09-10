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
 * A class representing a registered route. Each route is composed of a regular expression,
 * an array of allowed methods, and a callback function to execute if it matches.
 */
class Route
{
    /** @var string The regular expression */
    private string $expr;
    /** @var callable The callback function */
    private $callback;
    /** @var array The matches of, which will be the arguments of the callback */
    private array $matches = [];
    /** @var array<string> HTTP methods allowed for this route */
    private array $methods = ['GET', 'POST', 'HEAD', 'PUT', 'DELETE'];
    /** @var string|null Cached request method */
    private static ?string $requestMethod = null;

    /**
     * Constructor.
     *
     * @param string $expr regular expression to test against
     * @param callable $callback function executed if route matches
     * @param string|array|null $methods methods allowed
     */
    public function __construct(string $expr, callable $callback, $methods = null)
    {
        // Allow an optional trailing backslash
        $this->expr = '#^' . $expr . '/?$#';
        $this->callback = $callback;

        if ($methods !== null) {
            $this->methods = is_array($methods) ? $methods : [$methods];
        }

        // Cache the request method to avoid repeated access to $_SERVER
        if (self::$requestMethod === null) {
            self::$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }
    }

    /**
     * See if route matches with path.
     */
    public function matches(string $path): bool
    {
        // First check HTTP method as it's faster than regex matching
        if (!in_array(self::$requestMethod, $this->methods, true)) {
            return false;
        }

        return preg_match($this->expr, $path, $this->matches) === 1;
    }

    /**
     * Execute the callback.
     * The matches function needs to be called before this and return true.
     * We don't take the first match since it's the whole path.
     *
     * @return mixed The result of the callback execution
     */
    public function exec()
    {
        // Optimize by using array_slice only once
        $params = array_slice($this->matches, 1);

        return call_user_func_array($this->callback, $params);
    }
}
