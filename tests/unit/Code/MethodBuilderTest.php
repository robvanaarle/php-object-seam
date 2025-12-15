<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\Code\Exceptions\AttributeArgWithObjectDefaultValueUnsupported;
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

        $builder = new MethodBuilder([
            'ignore_attributes_with_object_default_values' => true,
        ]);

        $this->assertEquals($expectedDeclaration, $builder->buildDeclaration($reflectionMethod)->toString());
    }

    /**
     * @requires PHP >= 8.5
     * @dataProvider provideDeclarationsWithAttributeArgWithObjectAsDefaultValue
     */
    public function testBuildMethodDeclarationWithAttributeArgWithObjectAsDefaultValueFails(
        string $class,
        string $method
    ) {
        static::expectException(AttributeArgWithObjectDefaultValueUnsupported::class);

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

    public static function provideDeclarationsWithAttributeArgWithObjectAsDefaultValue(): array
    {
        return [
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\StaticClosureParam::class,
                'method'
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\StaticClosureAttributeParam::class,
                'method'
            ]
        ];
    }
}
