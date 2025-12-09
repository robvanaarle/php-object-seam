<?php

namespace PHPObjectSeam\TestClasses;

class MethodProvider
{
    public function provideMethods(): array
    {
        /**
         * Each case is an array with:
         * - class name
         * - method name
         * - expected method signature string
         * - optional _minPHPVersionId (inclusive)
         * - optional _maxPHPVersionId (exclusive)
         */
        $cases = [
            // PHP 7.0+
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\NameOnly::class,
                'method',
                'public function method($arg)',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Scalar::class,
                'method',
                'public function method(bool $arg1, int $arg2, float $arg3, string $arg4)',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\StringWithDefault::class,
                'method',
                'public function method(string $arg = "\'foo\\n")',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\IntWithDefaultNull::class,
                'method',
                'public function method(int $arg = null)',
                '_maxPHPVersionId' => 70100, // Different expected signature in PHP 7.1+
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\FloatWithDefaultConstant::class,
                'method',
                'public function method(float $arg = self::FOO)',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\ArrayByReference::class,
                'method',
                'public function method(array &$arg)',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Splat::class,
                'method',
                'public function method(...$arg)',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Classname::class,
                'method',
                'public function method(\Exception $arg)',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\ArrayWithDefault::class,
                'method',
                'public function method(array $arg = array (' . "\n" .
                    '  0 => \'\\\'foo' . "\n" .
                    '\',' . "\n" .
                    '))',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\BoolWithDefault::class,
                'method',
                'public function method(bool $arg = false)',
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\SelfType::class,
                'method',
                'public function method(\PHPObjectSeam\TestClasses\ArgumentSignatures\SelfType $arg)',
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\NoResult::class,
                'method',
                'public function method()',
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\StringResult::class,
                'method',
                'public function method(): string',
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\ClassnameResult::class,
                'method',
                'public function method(): \Exception',
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\ParentResult::class,
                'method',
                'public function method(): \PHPObjectSeam\TestClasses\TestCUT',
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\ParentResultInParent::class,
                'method',
                'public function method(): \PHPObjectSeam\TestClasses\TestCUT',
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult::class,
                'method',
                'public function method(): \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult',
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\SelfResultInParent::class,
                'method',
                'public function method(): \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult',
            ],

            // PHP 7.1+
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\VoidResult::class,
                'method',
                'public function method(): void',
                '_minPHPVersionId' => 70100,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\NullableStringResult::class,
                'method',
                'public function method(): ?string',
                '_minPHPVersionId' => 70100,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\NullableIntWithDefaultNull::class,
                'method',
                'public function method(?int $arg = null)',
                '_minPHPVersionId' => 70100,
            ],
            [
                // Different expected signature than in php 7.0 due to nullable type
                \PHPObjectSeam\TestClasses\ArgumentSignatures\IntWithDefaultNull::class,
                'method',
                'public function method(?int $arg = null)',
                '_minPHPVersionId' => 70100,
            ],

            // PHP 8.0+
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Union::class,
                'method',
                'public function method(int|float $arg)',
                '_minPHPVersionId' => 80000,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\NullableUnion::class,
                'method',
                'public function method(int|float|null $arg)',
                '_minPHPVersionId' => 80000,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\ConstructorPropertyPromotion::class,
                '__construct',
                'public function __construct(public ?string $arg = null)',
                '_minPHPVersionId' => 80000,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Attribute::class,
                'method',
                "public function method(\n"
                . "#[AttributeWithoutParams]\n"
                . "#[AttributeWithParams('value', 123, NULL, true, false, array (\n"
                . "  0 => 1,\n"
                . "  1 => 2,\n"
                . "  2 => 3,\n"
                . "))]\n"
                . '$param): void',
                '_minPHPVersionId' => 80000,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\MixedResult::class,
                'method',
                'public function method(): mixed',
                '_minPHPVersionId' => 80000,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\UnionResult::class,
                'method',
                'public function method(): int|float',
                '_minPHPVersionId' => 80000,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\NullableUnionResult::class,
                'method',
                'public function method(): int|float|null',
                '_minPHPVersionId' => 80000,
            ],
            [
                // Other attribute test cases are in AttributeBuilderTest
                \PHPObjectSeam\TestClasses\MethodAttributes\ArrayParam::class,
                'method',
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
                . "))]\n"
                . 'public function method(): void',
                '_minPHPVersionId' => 80000,
            ],

            // PHP 8.1+
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Intersection::class,
                'method',
                'public function method(\Iterator&\Countable $arg)',
                '_minPHPVersionId' => 80100,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\InInitializer::class,
                'method',
                'public function method(\DateTime $arg = new \DateTime)',
                '_minPHPVersionId' => 80100,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\ReadonlyProperty::class,
                '__construct',
                'public function __construct(public readonly string $arg)',
                '_minPHPVersionId' => 80100,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\NeverResult::class,
                'method',
                'public function method(): never',
                '_minPHPVersionId' => 80100,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\IntersectionResult::class,
                'method',
                'public function method(): \Iterator&\Countable',
                '_minPHPVersionId' => 80100,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\StaticResult::class,
                'method',
                'public function method(): static',
                '_minPHPVersionId' => 80100,
            ],

            // PHP 8.2+
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\TrueType::class,
                'method',
                'public function method(true $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\FalseType::class,
                'method',
                'public function method(false $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\NullType::class,
                'method',
                'public function method(null $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\DNF::class,
                'method',
                'public function method((\Iterator&\Countable)|null $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\TrueResult::class,
                'method',
                'public function method(): true',
                '_minPHPVersionId' => 80200,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\FalseResult::class,
                'method',
                'public function method(): false',
                '_minPHPVersionId' => 80200,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\NullResult::class,
                'method',
                'public function method(): null',
                '_minPHPVersionId' => 80200,
            ],
            [
                \PHPObjectSeam\TestClasses\ResultSignatures\DNFResult::class,
                'method',
                'public function method(): (\Iterator&\Countable)|null',
                '_minPHPVersionId' => 80200,
            ],
        ];

        // Get cases for current PHP version only
        $cases = array_filter($cases, function ($case) {
            $min = $case['_minPHPVersionId'] ?? 70000;
            $max = $case['_maxPHPVersionId'] ?? PHP_INT_MAX;

            return PHP_VERSION_ID >= $min && PHP_VERSION_ID < $max;
        });

        // Remove min/max keys
        return array_map(function ($case) {
            unset($case['_minPHPVersionId'], $case['_maxPHPVersionId']);
            return $case;
        }, $cases);
    }
}
