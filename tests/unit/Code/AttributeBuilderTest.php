<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\Exception;
use PHPUnit\Framework\TestCase;

class AttributeBuilderTest extends TestCase
{
    /**
     * @dataProvider provideSupportedAttributes
     */
    public function testBuildAttribute(string $class, string $expectedAttribute)
    {
        $reflectionMethod = new \ReflectionMethod($class, 'method');
        $builder = new AttributeBuilder();

        /** @phpstan-ignore-next-line */
        $this->assertEquals($expectedAttribute, $builder->build($reflectionMethod->getAttributes()[0])->toString());
    }

    /**
     * @dataProvider provideUnsupportedAttributes
     */
    public function testBuildAttributeFail(string $class, string $expectedAttribute)
    {
        static::expectException(Exception::class);

        $reflectionMethod = new \ReflectionMethod($class, 'method');
        $builder = new AttributeBuilder();

        /** @phpstan-ignore-next-line */
        $builder->build($reflectionMethod->getAttributes()[0]);
    }

    public static function provideSupportedAttributes()
    {
        return static::filter([
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\WithoutParams::class,
                "#[AttributeWithoutParams]",
            ],
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\ScalarParams::class,
                "#[AttributeWithScalarParams('stringValue', 42, 3.14, true, false, NULL)]",
            ],
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\ArrayParam::class,
                "#[AttributeWithArrayParam(array (\n"
                . "  0 => 1,\n"
                . "  1 => '2',\n"
                . "  2 => 3.0,\n"
                . "  3 => true,\n"
                . "  4 => NULL,\n"
                . "  5 => \n"
                . "  array (\n"
                . "    0 => 4,\n"
                . "    1 => 5,\n"
                . "  ),\n"
                . "  'six' => 6,\n"
                . "))]",
            ],
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\GlobalConstantParam::class,
                "#[AttributeWithGlobalConstantParam('globalValue')]",
            ],
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\AttributeClassNameParam::class,
                "#[AttributeWithAttributeClassNameParam("
                 . "'PHPObjectSeam\\\\TestClasses\\\\MethodAttributes\\\\AttributeClassNameParam')]",
            ],
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\ClassConstantParam::class,
                "#[AttributeWithClassConstantParam('classValue')]",
            ],
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\ClassConstantParam::class,
                "#[AttributeWithClassConstantParam('classValue')]",
                '_minPHPVersionId' => 80100,
            ],
        ]);
    }

    public static function provideUnsupportedAttributes()
    {
        return static::filter([
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\ObjectInstantiationParam::class,
                "#[AttributeWithObjectInstantiationParam(new \\DateTime())]",
                '_minPHPVersionId' => 80100,
            ],
            [
                \PHPObjectSeam\TestClasses\MethodAttributes\StaticClosureParam::class,
                "#[AttributeWithStaticClosureParam(static function (int \$a = 42) {\n"
                . "    return 'staticClosureValue';\n"
                . "})]",
                '_minPHPVersionId' => 80500,
            ],
        ]);
    }

    protected static function filter(array $cases): array
    {
        // Get cases for current PHP version only
        $cases = array_filter($cases, function ($case) {
            $min = $case['_minPHPVersionId'] ?? 80000;

            return PHP_VERSION_ID >= $min;
        });

        // Remove min/max keys
        return array_map(function ($case) {
            unset($case['_minPHPVersionId']);
            return $case;
        }, $cases);
    }
}
