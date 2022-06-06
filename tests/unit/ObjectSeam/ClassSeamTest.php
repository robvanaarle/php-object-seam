<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\Exception;
use PHPObjectSeam\TestClasses\TestCUTObjectSeam;
use PHPUnit\Framework\TestCase;

class ClassSeamTest extends TestCase
{
    protected function createClassSeam()
    {
        TestCUTObjectSeam::$staticValue = 'default';
        return new ClassSeam(TestCUTObjectSeam::class);
    }

    public function testPublicStaticMethodCanBeCalled()
    {
        $classSeam = $this->createClassSeam();
        $this->assertEquals(
            'publicStaticMethodResult: default;foo',
            $classSeam->call('publicStaticMethod', 'foo')
        );
    }

    public function testProtectedStaticMethodCanBeCalled()
    {
        $classSeam = $this->createClassSeam();
        $this->assertEquals(
            'protectedStaticMethodResult: default;foo',
            $classSeam->call('protectedStaticMethod', 'foo')
        );
    }

    public function testPrivateStaticMethodCannotBeCalled()
    {
        $this->expectException(Exception::class);

        $classSeam = $this->createClassSeam();
        $classSeam->call('privateStaticMethod', 'foo');
    }

    public function testPublicStaticMethodCanBeOverridden()
    {
        $classSeam = $this->createClassSeam();

        $classSeam->override('publicStaticMethod', function () {
            return 'overridden';
        });

        $this->assertEquals(
            'overridden',
            $classSeam->call('publicStaticMethod', 'foo')
        );
    }

    public function testProtectedStaticMethodCanBeOverridden()
    {
        $classSeam = $this->createClassSeam();

        $classSeam->override('protectedStaticMethod', function () {
            return 'overridden';
        });

        $this->assertEquals(
            'overridden',
            $classSeam->call('protectedStaticMethod', 'foo')
        );
    }

    public function testPrivateStaticMethodCannotBeOverridden()
    {
        $this->expectException(Exception::class);

        $classSeam = $this->createClassSeam();

        $classSeam->override('privateStaticMethod', function () {
            return 'overridden';
        });
    }

    public function testMethodCanBeOverriddenWithResultValue()
    {
        $classSeam = $this->createClassSeam();
        $classSeam->override('publicStaticMethod', 'string value');

        $this->assertEquals(
            'string value',
            $classSeam->call('publicStaticMethod', 'foo')
        );
    }

    public function testCallIsNotCapturedWhenNotEnabled()
    {
        $seam = $this->createClassSeam();

        $seam->call('publicStaticMethod', 'foo');
        $this->assertEmpty($seam->getCapturedCalls('publicStaticMethod'));
    }

    public function testCallIsNotCapturedWhenDisabledAfterEnabling()
    {
        $seam = $this->createClassSeam();
        $seam->captureCalls('publicStaticMethod');
        $seam->captureCalls('publicStaticMethod', false);

        $seam->call('publicStaticMethod', 'foo');
        $this->assertEmpty($seam->getCapturedCalls('publicStaticMethod'));
    }

    public function testCallIsCapturedWhenEnabled()
    {
        $seam = $this->createClassSeam();
        $seam->captureCalls('publicStaticMethod');

        $seam->call('publicStaticMethod', 'foo');
        $this->assertEquals([['foo']], $seam->getCapturedCalls('publicStaticMethod'));
    }

    public function testCannotCaptureCallsForNonStaticMethod()
    {
        $this->expectException(Exception::class);

        $seam = $this->createClassSeam();
        $seam->captureCalls('publicMethod');
    }

    public function testCannotCaptureCallsForPrivateMethod()
    {
        $this->expectException(Exception::class);

        $seam = $this->createClassSeam();
        $seam->captureCalls('privateStaticMethod');
    }

    public function testCapturedCallsOnObjectSeamsOfSameClassDoNotMix()
    {
        $seam1 = $this->createClassSeam();
        $seam1->captureCalls('publicStaticMethod');

        $seam2 = $this->createClassSeam();
        $seam2->captureCalls('publicStaticMethod');

        $seam1->call('publicStaticMethod', 'foo');
        $seam2->call('publicStaticMethod', 'bar');

        $this->assertEquals([['foo']], $seam1->getCapturedCalls('publicStaticMethod'));
        $this->assertEquals([['bar']], $seam2->getCapturedCalls('publicStaticMethod'));
    }
}
