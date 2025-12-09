<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\TestClasses\MethodProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MethodSignatureBuilderTest extends TestCase
{
    public static function provideSignatures(): array
    {
        $provider = new MethodProvider();
        return $provider->provideMethods();
    }

    /**
     * @dataProvider provideSignatures
     */
    public function testMethodSignature(string $class, string $method, string $expectedSignature)
    {
        $reflectionMethod = new ReflectionMethod($class, $method);
        $builder = new MethodSignatureBuilder();

        $this->assertEquals($expectedSignature, $builder->build($reflectionMethod));
    }
}
