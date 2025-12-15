<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\CreatesObjectSeams;
use PHPObjectSeam\Exception;
use PHPObjectSeam\TestClasses\TestCUTChildObjectSeam;
use PHPObjectSeam\TestClasses\TestCUTObjectSeam;
use PHPObjectSeam\TestClasses\TestCUTPropertyHooks;
use PHPObjectSeam\TestClasses\TestCUTPropertyHooksChildObjectSeam;
use PHPObjectSeam\TestClasses\TestCUTPropertyHooksObjectSeam;
use PHPUnit\Framework\TestCase;

class SeamTest extends TestCase
{
    protected $objectSeam;

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
            /* $this refers to the object seam, not this test class as PHPstan thinks */
            /* @phpstan-ignore property.notFound */
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
            /* $this refers to the object seam, not this test class as PHPstan thinks */
            /* @phpstan-ignore property.notFound */
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

    /**
     * @requires PHP >= 8.4
     */
    public function testPublicPropertyHooksCanBeCalled()
    {
        $objectSeam = new TestCUTPropertyHooksObjectSeam();
        /* @phpstan-ignore class.notFound */
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooks::class));

        $seam->call('$publicX::set', 10);
        $this->assertEquals(60, $seam->call('$publicX::get'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testPublicPropertyHooksInParentCanBeCalled()
    {
        $objectSeam = new TestCUTPropertyHooksChildObjectSeam();
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooksChildObjectSeam::class));

        $seam->call('$publicX::set', 10);
        $this->assertEquals(60, $seam->call('$publicX::get'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testProtectedPropertyHooksCanBeCalled()
    {
        $objectSeam = new TestCUTPropertyHooksObjectSeam();
        /* @phpstan-ignore class.notFound */
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooks::class));

        $seam->call('$protectedX::set', 10);
        $this->assertEquals(15, $seam->call('$protectedX::get'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testProtectedPropertyHooksInParentCanBeCalled()
    {
        $objectSeam = new TestCUTPropertyHooksChildObjectSeam();
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooksChildObjectSeam::class));

        $seam->call('$protectedX::set', 10);
        $this->assertEquals(15, $seam->call('$protectedX::get'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testPrivatePropertyHooksCanBeCalled()
    {
        $objectSeam = new TestCUTPropertyHooksObjectSeam();
        /* @phpstan-ignore class.notFound */
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooks::class));

        $seam->call('$privateX::set', 10);
        $this->assertEquals(5, $seam->call('$privateX::get'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testPublicPropertyHookCanBeOverridden()
    {
        $objectSeam = new TestCUTPropertyHooksObjectSeam();
        /* @phpstan-ignore class.notFound */
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooks::class));

        $seam->override('$publicX::get', function () {
            /* @phpstan-ignore property.notFound */
            return $this->temp;
        })->override('$publicX::set', function (int $value) {
            /* @phpstan-ignore property.notFound */
            $this->temp = $value;
        });

        $seam->call('$publicX::set', 123);
        $this->assertEquals(123, $seam->call('$publicX::get'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testProtectedPropertyHookCanBeOverridden()
    {
        $objectSeam = new TestCUTPropertyHooksObjectSeam();
        /* @phpstan-ignore class.notFound */
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooks::class));

        $seam->override('$protectedX::get', function () {
            /* @phpstan-ignore property.notFound */
            return $this->temp;
        })->override('$protectedX::set', function (int $value) {
            /* @phpstan-ignore property.notFound */
            $this->temp = $value;
        });

        $seam->call('$protectedX::set', 456);
        $this->assertEquals(456, $seam->call('$protectedX::get'));
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testPrivatePropertyGetHookCannotBeOverridden()
    {
        $this->expectException(Exception::class);

        $objectSeam = new TestCUTPropertyHooksObjectSeam();
        /* @phpstan-ignore class.notFound */
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooks::class));

        $seam->override('$privateX::get', function () {
            /* @phpstan-ignore property.notFound */
            return $this->temp;
        });
    }

    /**
     * @requires PHP >= 8.4
     */
    public function testPrivatePropertySetHookCannotBeOverridden()
    {
        $this->expectException(Exception::class);

        $objectSeam = new TestCUTPropertyHooksObjectSeam();
        /* @phpstan-ignore class.notFound */
        $seam = new Seam($objectSeam, new ClassSeam(TestCUTPropertyHooks::class));

        $seam->override('$privateX::set', function (int $value) {
            /* @phpstan-ignore property.notFound */
            $this->temp = $value;
        });
    }
}
