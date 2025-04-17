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
 * The main router class.
 */
class Router
{
    /** @var string Base url */
    private string $base_path;

    /** @var string Current relative url */
    private string $path;

    /** @var array<Route> Currently registered routes */
    private array $routes = [];

    /** @var array<string,array<Route>> Route cache indexed by HTTP method */
    private array $routesByMethod = [];

    /** @var array<string> Current middleware group stack */
    private array $middlewareStack = [];

    /**
     * Constructor.
     *
     * @param string $base_path the index url
     */
    public function __construct(string $base_path = '')
    {
        $this->base_path = $base_path;

        // Optimize path extraction
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = urldecode(parse_url($requestUri, PHP_URL_PATH));
        $this->path = substr($path, strlen($base_path));
    }

    /**
     * Start a middleware group.
     *
     * @param array $middlewares Middleware names to apply to all routes in the group
     * @param callable $callback Function containing route definitions
     *
     * @return self For method chaining
     */
    public function middleware(array $middlewares, callable $callback): self
    {
        // Save the current middleware stack
        $previousStack = $this->middlewareStack;

        // Add new middlewares to the stack
        $this->middlewareStack = array_merge($this->middlewareStack, $middlewares);

        // Execute the callback with route definitions
        $callback($this);

        // Restore the previous middleware stack
        $this->middlewareStack = $previousStack;

        return $this;
    }

    /**
     * Add a route.
     *
     * @param string $expr Route expression
     * @param callable $callback Function to execute when route matches
     * @param array|string|null $methods HTTP methods allowed
     *
     * @return self For method chaining
     */
    public function all(string $expr, callable $callback, array|string|null $methods = null): self
    {
        // Apply middleware to the callback if any middleware is in the stack
        if (!empty($this->middlewareStack)) {
            $callback = Middleware::apply($callback, $this->middlewareStack);
        }

        $route = new Route($expr, $callback, $methods);
        $this->routes[] = $route;

        // Cache routes by method for faster lookup
        $routeMethods = is_array($methods) ? $methods : ($methods ? [$methods] : ['GET', 'POST', 'HEAD', 'PUT', 'DELETE']);

        foreach ($routeMethods as $method) {
            $this->routesByMethod[$method][] = $route;
        }

        return $this;
    }

    /**
     * Alias for all.
     *
     * @return self For method chaining
     */
    public function add(string $expr, callable $callback, ?array $methods = null): self
    {
        return $this->all($expr, $callback, $methods);
    }

    /**
     * Add a route for GET requests.
     *
     * @return self For method chaining
     */
    public function get(string $expr, callable $callback): self
    {
        return $this->all($expr, $callback, 'GET');
    }

    /**
     * Add a route for POST requests.
     *
     * @return self For method chaining
     */
    public function post(string $expr, callable $callback): self
    {
        return $this->all($expr, $callback, 'POST');
    }

    /**
     * Add a route for HEAD requests.
     *
     * @return self For method chaining
     */
    public function head(string $expr, callable $callback): self
    {
        return $this->all($expr, $callback, 'HEAD');
    }

    /**
     * Add a route for PUT requests.
     *
     * @return self For method chaining
     */
    public function put(string $expr, callable $callback): self
    {
        return $this->all($expr, $callback, 'PUT');
    }

    /**
     * Add a route for DELETE requests.
     *
     * @return self For method chaining
     */
    public function delete(string $expr, callable $callback): self
    {
        return $this->all($expr, $callback, 'DELETE');
    }

    /**
     * Test all routes until any of them matches.
     *
     * @throws RouteNotFoundException if the route doesn't match with any of the registered routes
     *
     * @return mixed Result of the matched route callback
     */
    public function route()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // First try to match routes by current HTTP method (faster)
        if (isset($this->routesByMethod[$requestMethod])) {
            foreach ($this->routesByMethod[$requestMethod] as $route) {
                if ($route->matches($this->path)) {
                    return $route->exec();
                }
            }
        }

        // If no method-specific routes matched, try all routes
        foreach ($this->routes as $route) {
            if ($route->matches($this->path)) {
                return $route->exec();
            }
        }

        throw new RouteNotFoundException("No routes matching {$this->path}");
    }

    /**
     * Get the current url or the url to a path.
     *
     * @param string|null $path The path
     *
     * @return string The url
     */
    public function url(?string $path = null): string
    {
        if ($path === null) {
            $path = $this->path;
        }

        return $this->base_path . $path;
    }

    /**
     * Redirect from one url to another.
     *
     * @param string $from_path From path
     * @param string $to_path To path
     * @param int $code The http redirect code
     *
     * @return self For method chaining
     */
    public function redirectFrom(string $from_path, string $to_path, int $code = 302): self
    {
        return $this->all($from_path, function () use ($to_path, $code) {
            http_response_code($code);
            header("Location: {$to_path}");

            return true;
        });
    }

    /**
     * Redirect to another url.
     *
     * @param string $to_path The path
     * @param int $code The http redirect code
     *
     * @return self For method chaining
     */
    public function redirectTo(string $to_path, int $code = 302): self
    {
        return $this->redirectFrom($this->path, $to_path, $code);
    }
}
