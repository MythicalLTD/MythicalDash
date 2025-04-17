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

namespace MythicalDash\Plugins\Mixins\Reflection;

/**
 * Trait for intercepting method calls.
 *
 * This trait can be used in your class to intercept method calls
 * and apply patches dynamically without modifying the original methods.
 */
trait MethodInterceptor
{
    /**
     * Magic method to intercept method calls.
     *
     * @param string $method The method name
     * @param array $args The method arguments
     *
     * @return mixed The method return value
     */
    public function __call(string $method, array $args)
    {
        // Check if parent class has the method
        if (method_exists(get_parent_class($this), $method)) {
            // Call the parent method with patches applied
            return ClassPatcher::executeMethod($this, $method, $args);
        }

        // If method doesn't exist, throw an exception
        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    /**
     * Magic method to intercept static method calls.
     *
     * @param string $method The method name
     * @param array $args The method arguments
     *
     * @return mixed The method return value
     */
    public static function __callStatic(string $method, array $args)
    {
        // Get the current class
        $className = get_called_class();
        $parentClass = get_parent_class($className);

        // Check if parent class has the static method
        if (method_exists($parentClass, $method)) {
            // Call the parent static method - can't apply patches to static methods easily
            return $parentClass::$method(...$args);
        }

        // If method doesn't exist, throw an exception
        throw new \BadMethodCallException("Static method {$method} does not exist");
    }
}
