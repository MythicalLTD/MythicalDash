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
