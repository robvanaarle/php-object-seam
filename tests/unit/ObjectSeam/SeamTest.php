<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\CreatesObjectSeams;
use PHPObjectSeam\Exception;
use PHPObjectSeam\TestClasses\TestCUTChild;
use PHPObjectSeam\TestClasses\TestCUTChildObjectSeam;
use PHPObjectSeam\TestClasses\TestCUTObjectSeam;
use PHPUnit\Framework\TestCase;

class SeamTest extends TestCase
{
    use CreatesObjectSeams;

    protected $objectSeam;

    public function testRob()
    {
        $cut = $this->createObjectSeam(TestCUTChild::class);

        $this->assertEquals(
            'protectedMethodResult: default;foo',
            $cut->seam()->call('protectedMethod', 'foo')
        );
    }

    protected function createSeam()
    {
        $this->objectSeam = new TestCUTObjectSeam();
        return new Seam($this->objectSeam, new ClassSeam(TestCUTObjectSeam::class));
    }

    public function testPublicMethodCanBeCalled()
    {
        $seam = $this->createSeam();
        $this->assertEquals(
            'publicMethodResult: original constructor;foo',
            $seam->call('publicMethod', 'foo')
        );
    }

    public function testPublicMethodInParentCanBeCalled()
    {
        $objectSeam = new TestCUTChildObjectSeam();
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTChildObjectSeam::class));

        $this->assertEquals(
            'publicMethodResult: original constructor;foo',
            $seam->call('publicMethod', 'foo')
        );
    }

    public function testProtectedMethodCanBeCalled()
    {
        $seam = $this->createSeam();
        $this->assertEquals(
            'protectedMethodResult: original constructor;foo',
            $seam->call('protectedMethod', 'foo')
        );
    }

    public function testProtectedMethodInParentCanBeCalled()
    {
        $objectSeam = new TestCUTChildObjectSeam();
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTChildObjectSeam::class));

        $this->assertEquals(
            'protectedMethodResult: original constructor;foo',
            $seam->call('protectedMethod', 'foo')
        );
    }

    public function testPrivateMethodCanBeCalled()
    {
        $seam = $this->createSeam();
        $this->assertEquals(
            'privateMethodResult: original constructor;foo',
            $seam->call('privateMethod', 'foo')
        );
    }

    public function testPublicMethodCanBeOverridden()
    {
        $seam = $this->createSeam();
        $seam->override('publicMethod', function () {
            return 'overridden';
        });
        $this->assertEquals(
            'overridden',
            $seam->call('publicMethod', 'foo')
        );
    }

    public function testProtectedMethodCanBeOverridden()
    {
        $seam = $this->createSeam();
        $seam->override('protectedMethod', function () {
            return 'overridden';
        });
        $this->assertEquals(
            'overridden',
            $seam->call('protectedMethod', 'foo')
        );
    }

    public function testPrivateMethodCannotBeOverridden()
    {
        $this->expectException(Exception::class);

        $seam = $this->createSeam();
        $seam->override('privateMethod', function () {
            return 'overridden';
        });
    }

    public function testMethodCanBeOverriddenWithResultValue()
    {
        $seam = $this->createSeam();
        $seam->override('publicMethod', 'string value');

        $this->assertEquals(
            'string value',
            $seam->call('publicMethod', 'foo')
        );
    }

    public function testCallConstructCallsOriginalConstructor()
    {
        $seam = $this->createSeam();
        $this->objectSeam->value = 'before original constructor';

        $seam->callConstruct();

        $this->assertEquals('original constructor', $this->objectSeam->value);
    }

    public function testCustomConstruct()
    {
        $seam = $this->createSeam();
        $this->assertEquals('original constructor', $this->objectSeam->value);

        $this->objectSeam->value = 'before custom constructor';
        $seam->customConstruct(function ($value) {
            $this->value = 'custom constructor: ' . $value;
        }, 'foo');

        $this->assertEquals('custom constructor: foo', $this->objectSeam->value);
    }

    public function testInvokingUnsetCustomConstructorHasNoEffect()
    {
        $seam = $this->createSeam();
        $this->assertEquals('original constructor', $this->objectSeam->value);

        $this->objectSeam->value = 'before custom constructor';

        $seam->callCustomConstructor('foo');
        $this->assertEquals('before custom constructor', $this->objectSeam->value);
    }

    public function testCustomConstructorIsCalled()
    {
        $seam = $this->createSeam();
        $this->assertEquals('original constructor', $this->objectSeam->value);

        $this->objectSeam->value = 'before custom constructor';
        $seam->setCustomConstructor(function ($value) {
            $this->value = 'custom constructor: ' . $value;
        });

        $seam->callCustomConstructor('foo');
        $this->assertEquals('custom constructor: foo', $this->objectSeam->value);
    }

    public function testCallIsNotCapturedWhenNotEnabled()
    {
        $seam = $this->createSeam();

        $seam->call('publicMethod', 'foo');
        $this->assertEmpty($seam->getCapturedCalls('publicMethod'));
    }

    public function testCallIsNotCapturedWhenDisabledAfterEnabling()
    {
        $seam = $this->createSeam();
        $seam->captureCalls('publicMethod');
        $seam->captureCalls('publicMethod', false);

        $seam->call('publicMethod', 'foo');
        $this->assertEmpty($seam->getCapturedCalls('publicMethod'));
    }

    public function testCallIsCapturedWhenEnabled()
    {
        $seam = $this->createSeam();
        $seam->captureCalls('publicMethod');

        $seam->call('publicMethod', 'foo');
        $this->assertEquals([['foo']], $seam->getCapturedCalls('publicMethod'));
    }

    public function testCannotCaptureCallsForStaticMethod()
    {
        $this->expectException(Exception::class);

        $seam = $this->createSeam();
        $seam->captureCalls('publicStaticMethod');
    }
}
