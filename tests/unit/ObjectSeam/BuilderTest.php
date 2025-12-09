<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\TestClasses\AbstractCUT;
use PHPObjectSeam\TestClasses\PropertyHookCUT;
use PHPObjectSeam\TestClasses\ReadonlyCUT;
use PHPObjectSeam\TestClasses\TestCUT;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testObjectSeamIsBuiltWithoutCallingConstructor()
    {
        $builder = new Builder(TestCUT::class);
        $cut = $builder->build();

        $this->assertInstanceOf(ObjectSeam::class, $cut);
        $this->assertInstanceOf(TestCUT::class, $cut);
        $this->assertEquals('default', $cut->value);
        $this->assertFalse($cut->originalConstructorCalled);
    }

    public function testPublicMethodInvocationExecutesOriginal()
    {
        $builder = new Builder(TestCUT::class);
        $cut = $builder->build();

        $this->assertEquals('publicMethodResult: default;foo', $cut->publicMethod('foo'));
    }

    public function testPublicStaticMethodInvocationExecutesOriginal()
    {
        $builder = new Builder(TestCUT::class);
        $cut = $builder->build();

        $this->assertEquals('publicStaticMethodResult: default;foo', $cut::publicStaticMethod('foo'));
    }

    public function testProtectedMethodInvocationExecutesOriginal()
    {
        $builder = new Builder(TestCUT::class);
        $cut = $builder->build();

        $this->assertEquals('protectedMethodResult: default;foo', $cut->callProtectedMethod('foo'));
    }

    public function testProtectedStaticMethodInvocationExecutesOriginal()
    {
        $builder = new Builder(TestCUT::class);
        $cut = $builder->build();

        $this->assertEquals('protectedStaticMethodResult: default;foo', $cut::callProtectedStaticMethod('foo'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testPublicPropertyHookInvocationsExecutesOriginals()
    {
        /* @phpstan-ignore class.notFound */
        $builder = new Builder(PropertyHookCUT::class);
        $cut = $builder->build();

        // ingnore class.notFound does not work?!?!
        /* @phpstan-ignore-next-line */
        $cut->publicX = 10;

        /* @phpstan-ignore class.notFound */
        $this->assertEquals(60, $cut->publicX);
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testProtectedPropertyHookInvocationsExecutesOriginals()
    {
        /* @phpstan-ignore class.notFound */
        $builder = new Builder(PropertyHookCUT::class);
        $cut = $builder->build();

        /* @phpstan-ignore class.notFound */
        $cut->callPropertyHookSet('protectedX', 10);

        /* @phpstan-ignore class.notFound */
        $this->assertEquals(15, $cut->callPropertyHookGet('protectedX'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testPrivatePropertyHookInvocationsExecutesOriginals()
    {
        /* @phpstan-ignore class.notFound */
        $builder = new Builder(PropertyHookCUT::class);
        $cut = $builder->build();

        /* @phpstan-ignore class.notFound */
        $cut->callPropertyHookSet('privateX', 10);

        /* @phpstan-ignore class.notFound */
        $this->assertEquals(5, $cut->callPropertyHookGet('privateX'));
    }

    public static function provideCUTClasses(): array
    {
        $classes = [
            [AbstractCUT::class],
        ];

        if (PHP_VERSION_ID >= 80200) {
            /* @phpstan-ignore class.notFound */
            $classes[] = [ReadonlyCUT::class];
        }

        return $classes;
    }

    /**
     * @dataProvider provideCUTClasses
     */
    public function testObjectSeamCanBeCreatedForClass(string $class)
    {
        $builder = new Builder($class);
        $objectSeam = $builder->build();

        $this->assertInstanceOf($class, $objectSeam);
    }
}
