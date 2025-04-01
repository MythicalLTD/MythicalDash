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

use ReflectionClass;
use MythicalDash\App;

/**
 * Provides runtime class patching and modification capabilities.
 *
 * This class allows for dynamic modification of classes, methods, and properties
 * at runtime without modifying the original source code.
 */
class ClassPatcher
{
    /** @var array Cached reflection classes */
    private static array $reflectionCache = [];

    /** @var array Method patches by class and method name */
    private static array $methodPatches = [];

    /** @var array Class proxies by class name */
    private static array $classProxies = [];

    /** @var array Property overrides by class and property name */
    private static array $propertyOverrides = [];

    /**
     * Get a ReflectionClass instance for a class.
     *
     * @param string|object $class Class name or object instance
     *
     * @return \ReflectionClass The reflection class
     */
    public static function getReflectionClass($class): \ReflectionClass
    {
        $className = is_object($class) ? get_class($class) : $class;

        if (!isset(self::$reflectionCache[$className])) {
            self::$reflectionCache[$className] = new \ReflectionClass($className);
        }

        return self::$reflectionCache[$className];
    }

    /**
     * Apply a patch to a method.
     *
     * This method allows you to override, extend, or modify the behavior of a method
     * at runtime without changing the original class definition.
     *
     * @param string $className The class name
     * @param string $methodName The method name
     * @param \Closure $patchCallback The patch callback
     * @param string $patchType The patch type: 'before', 'after', 'around', or 'replace'
     *
     * @return bool True if successfully patched
     */
    public static function patchMethod(string $className, string $methodName, \Closure $patchCallback, string $patchType = 'around'): bool
    {
        $logger = App::getInstance(true)->getLogger();

        try {
            // Validate the patch type
            if (!in_array($patchType, ['before', 'after', 'around', 'replace'])) {
                $logger->warning("Invalid patch type: {$patchType}");

                return false;
            }

            // Check if the method exists
            $reflection = self::getReflectionClass($className);
            if (!$reflection->hasMethod($methodName)) {
                $logger->warning("Method {$methodName} does not exist in class {$className}");

                return false;
            }

            $method = $reflection->getMethod($methodName);

            // Store the patch
            $key = "{$className}::{$methodName}";
            if (!isset(self::$methodPatches[$key])) {
                self::$methodPatches[$key] = [];
            }

            self::$methodPatches[$key][] = [
                'callback' => $patchCallback,
                'type' => $patchType,
                'method' => $method,
            ];

            $logger->debug("Patched method {$className}::{$methodName} with {$patchType} patch");

            return true;

        } catch (\Throwable $e) {
            $logger->error("Failed to patch method {$className}::{$methodName}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Apply a patch before a method is executed.
     *
     * The callback receives the same arguments as the original method.
     *
     * @param string $className The class name
     * @param string $methodName The method name
     * @param \Closure $callback The callback to execute before the method
     *
     * @return bool True if successfully patched
     */
    public static function beforeMethod(string $className, string $methodName, \Closure $callback): bool
    {
        return self::patchMethod($className, $methodName, $callback, 'before');
    }

    /**
     * Apply a patch after a method is executed.
     *
     * The callback receives the original method's return value as the first argument,
     * followed by the original method arguments.
     *
     * @param string $className The class name
     * @param string $methodName The method name
     * @param \Closure $callback The callback to execute after the method
     *
     * @return bool True if successfully patched
     */
    public static function afterMethod(string $className, string $methodName, \Closure $callback): bool
    {
        return self::patchMethod($className, $methodName, $callback, 'after');
    }

    /**
     * Replace a method entirely.
     *
     * The callback will be used instead of the original method.
     *
     * @param string $className The class name
     * @param string $methodName The method name
     * @param \Closure $callback The replacement method
     *
     * @return bool True if successfully patched
     */
    public static function replaceMethod(string $className, string $methodName, \Closure $callback): bool
    {
        return self::patchMethod($className, $methodName, $callback, 'replace');
    }

    /**
     * Apply a patch around a method.
     *
     * The callback receives a callable for the original method as the first argument,
     * followed by the original method arguments. The callback is responsible for
     * calling the original method if needed.
     *
     * @param string $className The class name
     * @param string $methodName The method name
     * @param \Closure $callback The callback to execute around the method
     *
     * @return bool True if successfully patched
     */
    public static function aroundMethod(string $className, string $methodName, \Closure $callback): bool
    {
        return self::patchMethod($className, $methodName, $callback, 'around');
    }

    /**
     * Execute method with patches applied.
     *
     * This is used internally to apply the registered patches when a method is called.
     *
     * @param object $instance The object instance
     * @param string $methodName The method name
     * @param array $args The method arguments
     *
     * @return mixed The method return value
     */
    public static function executeMethod(object $instance, string $methodName, array $args)
    {
        $className = get_class($instance);
        $key = "{$className}::{$methodName}";

        // If no patches exist, just call the original method
        if (!isset(self::$methodPatches[$key]) || empty(self::$methodPatches[$key])) {
            return $instance->$methodName(...$args);
        }

        // Apply patches
        $result = null;
        $patches = self::$methodPatches[$key];

        // Execute 'before' patches
        foreach ($patches as $patch) {
            if ($patch['type'] === 'before') {
                $patch['callback']->call($instance, ...$args);
            }
        }

        // Execute 'around' or 'replace' patches
        $aroundExecuted = false;
        foreach ($patches as $patch) {
            if ($patch['type'] === 'around') {
                $aroundExecuted = true;

                // Create a callable for the original method
                $originalMethod = function (...$methodArgs) use ($instance, $methodName) {
                    // Skip all patches when calling the original from an around patch
                    return $instance->$methodName(...$methodArgs);
                };

                // Call the around patch with the original method and arguments
                $result = $patch['callback']->call($instance, $originalMethod, ...$args);
                break;
            } elseif ($patch['type'] === 'replace') {
                $aroundExecuted = true;
                $result = $patch['callback']->call($instance, ...$args);
                break;
            }
        }

        // If no around/replace patches were executed, call the original method
        if (!$aroundExecuted) {
            $result = $instance->$methodName(...$args);
        }

        // Execute 'after' patches
        foreach ($patches as $patch) {
            if ($patch['type'] === 'after') {
                $newResult = $patch['callback']->call($instance, $result, ...$args);
                // Allow after patches to modify the return value
                if ($newResult !== null) {
                    $result = $newResult;
                }
            }
        }

        return $result;
    }

    /**
     * Override a property value.
     *
     * @param string $className The class name
     * @param string $propertyName The property name
     * @param mixed $value The new value
     *
     * @return bool True if successfully overridden
     */
    public static function overrideProperty(string $className, string $propertyName, $value): bool
    {
        $logger = App::getInstance(true)->getLogger();

        try {
            // Check if the property exists
            $reflection = self::getReflectionClass($className);
            if (!$reflection->hasProperty($propertyName)) {
                $logger->warning("Property {$propertyName} does not exist in class {$className}");

                return false;
            }

            $property = $reflection->getProperty($propertyName);

            // Store the override
            $key = "{$className}::{$propertyName}";
            self::$propertyOverrides[$key] = [
                'value' => $value,
                'property' => $property,
            ];

            $logger->debug("Overrode property {$className}::{$propertyName}");

            return true;

        } catch (\Throwable $e) {
            $logger->error("Failed to override property {$className}::{$propertyName}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a property value with overrides applied.
     *
     * @param object $instance The object instance
     * @param string $propertyName The property name
     *
     * @return mixed The property value
     */
    public static function getPropertyValue(object $instance, string $propertyName)
    {
        $className = get_class($instance);
        $key = "{$className}::{$propertyName}";

        // If an override exists, use it
        if (isset(self::$propertyOverrides[$key])) {
            return self::$propertyOverrides[$key]['value'];
        }

        // Otherwise, get the actual property value
        $reflection = self::getReflectionClass($instance);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($instance);
    }

    /**
     * Create a proxy for a class.
     *
     * @param string $className The class name
     * @param array $methodOverrides Method overrides
     * @param array $propertyOverrides Property overrides
     *
     * @return string The proxy class name
     */
    public static function createClassProxy(string $className, array $methodOverrides = [], array $propertyOverrides = []): string
    {
        $logger = App::getInstance(true)->getLogger();

        try {
            $reflection = self::getReflectionClass($className);
            $proxyClassName = $className . 'Proxy' . uniqid();

            // Generate the proxy class
            $code = self::generateProxyClass($reflection, $proxyClassName, $methodOverrides, $propertyOverrides);

            // Evaluate the proxy class code
            eval($code);

            // Store the proxy class
            self::$classProxies[$className] = $proxyClassName;

            $logger->debug("Created proxy class {$proxyClassName} for {$className}");

            return $proxyClassName;

        } catch (\Throwable $e) {
            $logger->error("Failed to create proxy class for {$className}: " . $e->getMessage());

            return $className;
        }
    }

    /**
     * Get a proxy instance for a class.
     *
     * @param string $className The class name
     * @param array $constructorArgs Constructor arguments
     *
     * @return object The proxy instance
     */
    public static function getProxyInstance(string $className, array $constructorArgs = []): object
    {
        $logger = App::getInstance(true)->getLogger();

        try {
            // If a proxy exists, use it
            if (isset(self::$classProxies[$className])) {
                $proxyClass = self::$classProxies[$className];

                return new $proxyClass(...$constructorArgs);
            }

            // Otherwise, create a new instance of the original class
            return new $className(...$constructorArgs);

        } catch (\Throwable $e) {
            $logger->error("Failed to get proxy instance for {$className}: " . $e->getMessage());

            return new $className(...$constructorArgs);
        }
    }

    /**
     * Generate a proxy class.
     *
     * @param \ReflectionClass $reflection The reflection class
     * @param string $proxyClassName The proxy class name
     * @param array $methodOverrides Method overrides
     * @param array $propertyOverrides Property overrides
     *
     * @return string The proxy class code
     */
    private static function generateProxyClass(\ReflectionClass $reflection, string $proxyClassName, array $methodOverrides, array $propertyOverrides): string
    {
        $namespace = $reflection->getNamespaceName();
        $shortName = $reflection->getShortName();

        $code = "namespace {$namespace};\n\n";
        $code .= "class {$shortName}Proxy" . substr($proxyClassName, strrpos($proxyClassName, 'Proxy') + 5) . " extends {$shortName} {\n";

        // Add property overrides
        foreach ($propertyOverrides as $propertyName => $value) {
            $code .= "    private \${$propertyName}Override = " . var_export($value, true) . ";\n";

            // Add getter method
            $code .= '    public function get' . ucfirst($propertyName) . "() {\n";
            $code .= "        return \$this->{$propertyName}Override;\n";
            $code .= "    }\n";

            // Add setter method
            $code .= '    public function set' . ucfirst($propertyName) . "(\$value) {\n";
            $code .= "        \$this->{$propertyName}Override = \$value;\n";
            $code .= "    }\n";
        }

        // Add method overrides
        foreach ($methodOverrides as $methodName => $override) {
            $method = $reflection->getMethod($methodName);
            $parameters = [];

            foreach ($method->getParameters() as $param) {
                $paramStr = '';

                if ($param->hasType()) {
                    $typeName = $param->getType()->getName();
                    $paramStr .= $typeName . ' ';
                }

                $paramStr .= '$' . $param->getName();

                if ($param->isDefaultValueAvailable()) {
                    $defaultValue = $param->getDefaultValue();
                    $paramStr .= ' = ' . var_export($defaultValue, true);
                }

                $parameters[] = $paramStr;
            }

            $paramList = implode(', ', $parameters);
            $returnType = $method->hasReturnType() ? ': ' . $method->getReturnType()->getName() : '';

            $code .= "    public function {$methodName}({$paramList}){$returnType} {\n";
            $code .= "        // Method override for {$methodName}\n";
            $code .= "        {$override}\n";
            $code .= "    }\n";
        }

        $code .= "}\n";

        return $code;
    }
}
