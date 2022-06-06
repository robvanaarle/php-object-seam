<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\TestClasses\AbstractCUT;
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

    public function testObjectSeamCanBeCreatedOfAbstractClassWithAbstractMethods()
    {
        $builder = new Builder(AbstractCUT::class);
        $objectSeam = $builder->build();

        $this->assertInstanceOf(AbstractCUT::class, $objectSeam);
    }
}
