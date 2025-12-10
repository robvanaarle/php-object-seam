<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\Exception;
use PHPObjectSeam\TestClasses\MethodProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MethodBuilderTest extends TestCase
{
    /**
     * @dataProvider provideSupportedDeclarations
     */
    public function testBuildMethodDeclaration(string $class, string $method, string $expectedDeclaration)
    {
        // Suppress deprecated warnings for methods that use deprecated features, e.g.
        // public function method(int $arg = null) in PHP 8.4 (should be ?int $arg = null)
        // This package is for legacy code, so we need to support such cases in the tests.

        // Suppress deprecations with a custom error handler for just the duration of the ReflectionMethod creation.
        // Setting error reporting level without E_DEPRECATED does not work, as the PHPUnit error handler does not
        // respect the error reporting level.
        $previousErrorHandler = set_error_handler(
            function ($severity, $message, $file, $line) use (&$previousErrorHandler) {
                if ($severity & E_DEPRECATED) {
                    // Ignore deprecated warnings
                    return true;
                }
                return $previousErrorHandler ? $previousErrorHandler($severity, $message, $file, $line) : false;
            }
        );

        try {
            $reflectionMethod = new ReflectionMethod($class, $method);
        } finally {
            // Remove custom error handler
            restore_error_handler();
        }

        $builder = new MethodBuilder();

        $this->assertEquals($expectedDeclaration, $builder->buildDeclaration($reflectionMethod)->toString());
    }

    /**
     * @dataProvider provideUnsupportedDeclarations
     */
    public function testBuildMethodDeclarationFail(string $class, string $method)
    {
        if ($method === 'noOpSkipMethod') {
            $this->markTestSkipped('No unsupported methods provided by the MethodProvider.');
        }

        static::expectException(Exception::class);

        // Suppress deprecated warnings for methods that use deprecated features, e.g.
        // public function method(int $arg = null) in PHP 8.4 (should be ?int $arg = null)
        // This package is for legacy code, so we need to support such cases in the tests.

        // Suppress deprecations with a custom error handler for just the duration of the ReflectionMethod creation.
        // Setting error reporting level without E_DEPRECATED does not work, as the PHPUnit error handler does not
        // respect the error reporting level.
        $previousErrorHandler = set_error_handler(
            function ($severity, $message, $file, $line) use (&$previousErrorHandler) {
                if ($severity & E_DEPRECATED) {
                    // Ignore deprecated warnings
                    return true;
                }
                return $previousErrorHandler ? $previousErrorHandler($severity, $message, $file, $line) : false;
            }
        );

        try {
            $reflectionMethod = new ReflectionMethod($class, $method);
        } finally {
            // Remove custom error handler
            restore_error_handler();
        }

        $builder = new MethodBuilder();

        $builder->buildDeclaration($reflectionMethod);
    }

    public static function provideSupportedDeclarations(): array
    {
        $provider = new MethodProvider();
        return $provider->provideSupportedMethods();
    }

    public static function provideUnsupportedDeclarations(): array
    {
        $provider = new MethodProvider();
        $declarations = $provider->provideUnsupportedMethods();
        if (count($declarations) === 0) {
            // PHPUnit requires at least one data set for data providers
            $declarations[] = [\stdClass::class, 'noOpSkipMethod'];
        }
        return $declarations;
    }
}
