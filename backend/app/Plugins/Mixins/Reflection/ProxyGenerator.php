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

use MythicalDash\App;

/**
 * Class for generating dynamic proxy classes.
 *
 * This class provides functionality to create proxy classes at runtime
 * that can intercept and modify behavior of existing classes.
 */
class ProxyGenerator
{
    /** @var array Cache of generated proxy classes */
    private static array $proxyCache = [];

    /**
     * Create a proxy class for a target class.
     *
     * @param string $targetClass The class to proxy
     * @param array $interceptMethods Methods to intercept (true for all methods)
     * @param array $interceptProperties Properties to intercept (true for all properties)
     *
     * @return string The proxy class name
     */
    public static function createProxy(string $targetClass, $interceptMethods = true, $interceptProperties = true): string
    {
        $logger = App::getInstance(true)->getLogger();

        // Check if proxy already exists in cache
        if (isset(self::$proxyCache[$targetClass])) {
            return self::$proxyCache[$targetClass];
        }

        try {
            $reflection = new \ReflectionClass($targetClass);
            $namespace = $reflection->getNamespaceName();
            $className = $reflection->getShortName();
            $proxyClassName = $className . 'Proxy' . substr(md5(uniqid()), 0, 8);
            $fullProxyClassName = $namespace . '\\' . $proxyClassName;

            // Generate proxy class code
            $code = self::generateProxyCode($reflection, $proxyClassName, $interceptMethods, $interceptProperties);

            // Evaluate the code to create the class
            eval($code);

            // Store in cache
            self::$proxyCache[$targetClass] = $fullProxyClassName;

            $logger->debug("Generated proxy class {$fullProxyClassName} for {$targetClass}");

            return $fullProxyClassName;

        } catch (\Throwable $e) {
            $logger->error("Failed to create proxy for {$targetClass}: " . $e->getMessage());

            return $targetClass; // Return original class on failure
        }
    }

    /**
     * Create an instance of a proxy class.
     *
     * @param string $targetClass The target class
     * @param array $constructorArgs Constructor arguments
     * @param mixed $interceptMethods Methods to intercept
     * @param mixed $interceptProperties Properties to intercept
     *
     * @return object The proxy instance
     */
    public static function createProxyInstance(string $targetClass, array $constructorArgs = [], $interceptMethods = true, $interceptProperties = true): object
    {
        $proxyClass = self::createProxy($targetClass, $interceptMethods, $interceptProperties);

        return new $proxyClass(...$constructorArgs);
    }

    /**
     * Generate proxy class code.
     *
     * @param \ReflectionClass $reflection The reflection class
     * @param string $proxyClassName The proxy class name
     * @param mixed $interceptMethods Methods to intercept
     * @param mixed $interceptProperties Properties to intercept
     *
     * @return string The generated code
     */
    private static function generateProxyCode(\ReflectionClass $reflection, string $proxyClassName, $interceptMethods, $interceptProperties): string
    {
        $namespace = $reflection->getNamespaceName();
        $className = $reflection->getShortName();

        $code = "namespace {$namespace};\n\n";

        // Add use statements for traits
        $code .= "use MythicalDash\\Plugins\\Mixins\\Reflection\\MethodInterceptor;\n";
        $code .= "use MythicalDash\\Plugins\\Mixins\\Reflection\\PropertyAccessor;\n\n";

        // Define the proxy class
        $code .= "class {$proxyClassName} extends {$className} {\n";

        // Include traits
        if ($interceptMethods === true || (is_array($interceptMethods) && !empty($interceptMethods))) {
            $code .= "    use MethodInterceptor;\n";
        }

        if ($interceptProperties === true || (is_array($interceptProperties) && !empty($interceptProperties))) {
            $code .= "    use PropertyAccessor;\n";
        }

        // Constructor to initialize parent
        $code .= "\n    public function __construct(...\$args) {\n";
        $code .= "        parent::__construct(...\$args);\n";
        $code .= "    }\n";

        // If specific methods are intercepted, override them
        if (is_array($interceptMethods)) {
            foreach ($interceptMethods as $methodName) {
                if ($reflection->hasMethod($methodName)) {
                    $method = $reflection->getMethod($methodName);

                    // Skip if method is final or private
                    if ($method->isFinal() || $method->isPrivate()) {
                        continue;
                    }

                    $params = [];
                    foreach ($method->getParameters() as $param) {
                        $paramStr = '';
                        if ($param->hasType()) {
                            $paramStr .= $param->getType()->getName() . ' ';
                        }

                        $paramStr .= '$' . $param->getName();

                        if ($param->isDefaultValueAvailable()) {
                            $paramStr .= ' = ' . var_export($param->getDefaultValue(), true);
                        }

                        $params[] = $paramStr;
                    }

                    $paramsStr = implode(', ', $params);
                    $returnType = $method->hasReturnType() ? ': ' . $method->getReturnType()->getName() : '';

                    $code .= "\n    public function {$methodName}({$paramsStr}){$returnType} {\n";
                    $code .= "        return \\MythicalDash\\Plugins\\Mixins\\Reflection\\ClassPatcher::executeMethod(\$this, '{$methodName}', func_get_args());\n";
                    $code .= "    }\n";
                }
            }
        }

        $code .= "}\n";

        return $code;
    }
}
