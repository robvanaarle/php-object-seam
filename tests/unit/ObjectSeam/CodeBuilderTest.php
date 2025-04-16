<?php

// phpcs:disable Generic.Files.LineLength

namespace PHPObjectSeam\Code;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\ObjectSeam\Seam;
use PHPObjectSeam\ObjectSeam\ClassSeam;
use PHPObjectSeam\ObjectSeam\CodeBuilder;
use PHPObjectSeam\ObjectSeam\ObjectSeamTrait;
use PHPObjectSeam\TestClasses\MethodProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CodeBuilderTest extends TestCase
{
    public function provideMethods(): array
    {
        $provider = new MethodProvider();
        return $provider->provideMethods();
    }

    /**
     * @dataProvider provideMethods
     */
    public function testCodeIsValid(string $class, string $signature, string $function)
    {
        $objectSeamClass = 'TestClass' . md5((string)rand());
        $builder = new CodeBuilder($objectSeamClass, $class);
        $code = $builder->build();

        eval($code);
        $reflectionClass = new ReflectionClass($objectSeamClass);
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $this->assertInstanceOf($objectSeamClass, $instance);
    }

    public function provideClasses(): array
    {
        $objectSeamInterface = ObjectSeam::class;
        $objectSeamTrait = ObjectSeamTrait::class;
        $seamClass = Seam::class;
        $classSeam = ClassSeam::class;

        $result = [];

        // method: no result type
        $class = '__CodeBuilderTest_Class01';
        $objectSeamClass = $class . '_ObjectSeam';
        $classCode = <<<CODE
class $class
{
    public function method() { }
}
CODE;
        $objectSeamCode = <<<CODE
class $objectSeamClass extends $class implements $objectSeamInterface
{
    use $objectSeamTrait;

    public function method()
    {
        return $seamClass::getInstance(\$this)->call(__FUNCTION__, ...func_get_args());
    }
}
CODE;
        $result['method: no result type'] = [$class, $classCode, $objectSeamClass, $objectSeamCode];

        // method: void result type
        if (PHP_VERSION_ID >= 70100) {
            $class = '__CodeBuilderTest_Class02';
            $objectSeamClass = $class . '_ObjectSeam';
            $classCode = <<<CODE
class $class
{
    public function method(): void { }
}
CODE;
            $objectSeamCode = <<<CODE
class $objectSeamClass extends $class implements $objectSeamInterface
{
    use $objectSeamTrait;

    public function method(): void
    {
        $seamClass::getInstance(\$this)->call(__FUNCTION__, ...func_get_args());
    }
}
CODE;
            $result['method: void result type'] = [$class, $classCode, $objectSeamClass, $objectSeamCode];
        }

        // method: never result type
        if (PHP_VERSION_ID >= 80100) {
            $class = '__CodeBuilderTest_Class03';
            $objectSeamClass = $class . '_ObjectSeam';
            $classCode = <<<CODE
class $class
{
    public function method(): never { while(true) {  } }
}
CODE;
            $objectSeamCode = <<<CODE
class $objectSeamClass extends $class implements $objectSeamInterface
{
    use $objectSeamTrait;

    public function method(): never
    {
        $seamClass::getInstance(\$this)->call(__FUNCTION__, ...func_get_args());
    }
}
CODE;
            $result['method: never result type'] = [$class, $classCode, $objectSeamClass, $objectSeamCode];
        }

        // static method: no result type
        $class = '__CodeBuilderTest_Class11';
        $objectSeamClass = $class . '_ObjectSeam';
        $classCode = <<<CODE
class $class
{
    public static function method() { }
}
CODE;
        $objectSeamCode = <<<CODE
class $objectSeamClass extends $class implements $objectSeamInterface
{
    use $objectSeamTrait;

    public static function method()
    {
        return $classSeam::getInstance(__CLASS__)->call(__FUNCTION__, ...func_get_args());
    }
}
CODE;


        $result['static method: no result type'] = [$class, $classCode, $objectSeamClass, $objectSeamCode];


        // static method: void result type
        if (PHP_VERSION_ID >= 70100) {
            $class = '__CodeBuilderTest_Class12';
            $objectSeamClass = $class . '_ObjectSeam';
            $classCode = <<<CODE
class $class
{
    public static function method(): void { }
}
CODE;
            $objectSeamCode = <<<CODE
class $objectSeamClass extends $class implements $objectSeamInterface
{
    use $objectSeamTrait;

    public static function method(): void
    {
        $classSeam::getInstance(__CLASS__)->call(__FUNCTION__, ...func_get_args());
    }
}
CODE;
            $result['static method: void result type'] = [$class, $classCode, $objectSeamClass, $objectSeamCode];
        }

        // static method: never result type
        if (PHP_VERSION_ID >= 80100) {
            $class = '__CodeBuilderTest_Class13';
            $objectSeamClass = $class . '_ObjectSeam';
            $classCode = <<<CODE
class $class
{
    public static function method(): never { while(true) {  } }
}
CODE;
            $objectSeamCode = <<<CODE
class $objectSeamClass extends $class implements $objectSeamInterface
{
    use $objectSeamTrait;

    public static function method(): never
    {
        $classSeam::getInstance(__CLASS__)->call(__FUNCTION__, ...func_get_args());
    }
}
CODE;
            $result['static method: never result type'] = [$class, $classCode, $objectSeamClass, $objectSeamCode];
        }


        return $result;
    }

    /**
     * @dataProvider provideClasses
     */
    public function testGeneratedCode($class, $classCode, $objectSeamClass, $expectedObjectSeamCode)
    {
        eval($classCode);

        $builder = new CodeBuilder($objectSeamClass, $class);
        $code = $builder->build();

        $this->assertEquals($expectedObjectSeamCode, $code);
    }
}
