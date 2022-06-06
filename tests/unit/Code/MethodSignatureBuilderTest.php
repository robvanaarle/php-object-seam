<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\TestClasses\MethodProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MethodSignatureBuilderTest extends TestCase
{
    public function provideSignatures(): array
    {
        $provider = new MethodProvider();
        return $provider->provideMethods();
    }

    /**
     * @dataProvider provideSignatures
     */
    public function testMethodSignature(string $classname, string $expected, string $function)
    {
        $reflectionMethod = new ReflectionMethod($classname, $function);
        $builder = new MethodSignatureBuilder();

        $this->assertEquals($expected, $builder->build($reflectionMethod));
    }
}
