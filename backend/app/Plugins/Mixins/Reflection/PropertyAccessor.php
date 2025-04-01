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
 * Trait for intercepting property access.
 *
 * This trait can be used in your class to intercept property access
 * and apply overrides dynamically without modifying the original properties.
 */
trait PropertyAccessor
{
    /**
     * Magic method to intercept property access.
     *
     * @param string $property The property name
     *
     * @return mixed The property value
     */
    public function __get(string $property)
    {
        // Get the property value with overrides applied
        return ClassPatcher::getPropertyValue($this, $property);
    }

    /**
     * Magic method to intercept property assignment.
     *
     * @param string $property The property name
     * @param mixed $value The value to assign
     *
     * @return void
     */
    public function __set(string $property, $value)
    {
        // Override the property in the class patcher
        ClassPatcher::overrideProperty(get_class($this), $property, $value);
    }

    /**
     * Magic method to check if a property is set.
     *
     * @param string $property The property name
     *
     * @return bool Whether the property is set
     */
    public function __isset(string $property)
    {
        try {
            $value = ClassPatcher::getPropertyValue($this, $property);

            return isset($value);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
