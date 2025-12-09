<?php

namespace PHPObjectSeam\TestClasses;

class MethodProvider
{
    public function provideMethods(): array
    {
        $cases = [
            // PHP 7.0+
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\NameOnly::class,
                'method' => 'method',
                'expectedSignature' => 'public function method($arg)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\Scalar::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(bool $arg1, int $arg2, float $arg3, string $arg4)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\StringWithDefault::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(string $arg = "\'foo\\n")',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\IntWithDefaultNull::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(int $arg = null)',
                '_maxPHPVersionId' => 70100, // Different expected signature in PHP 7.1+
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\FloatWithDefaultConstant::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(float $arg = self::FOO)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\ArrayByReference::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(array &$arg)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\Splat::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(...$arg)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\Classname::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(\Exception $arg)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\ArrayWithDefault::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(array $arg = array (' . "\n" .
                    '  0 => \'\\\'foo' . "\n" .
                    '\',' . "\n" .
                    '))',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\BoolWithDefault::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(bool $arg = false)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\SelfType::class,
                'method' => 'method',
                'expectedSignature' =>
                    'public function method(\PHPObjectSeam\TestClasses\ArgumentSignatures\SelfType $arg)',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\NoResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method()',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\StringResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): string',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\ClassnameResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): \Exception',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\ParentResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): \PHPObjectSeam\TestClasses\TestCUT',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\ParentResultInParent::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): \PHPObjectSeam\TestClasses\TestCUT',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult::class,
                'method' => 'method',
                'expectedSignature' =>
                    'public function method(): \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult',
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\SelfResultInParent::class,
                'method' => 'method',
                'expectedSignature' =>
                    'public function method(): \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult',
            ],

            // PHP 7.1+
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\VoidResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): void',
                '_minPHPVersionId' => 70100,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\NullableStringResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): ?string',
                '_minPHPVersionId' => 70100,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\NullableIntWithDefaultNull::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(?int $arg = null)',
                '_minPHPVersionId' => 70100,
            ],
            [
                // Different expected signature than in php 7.0 due to nullable type
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\IntWithDefaultNull::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(?int $arg = null)',
                '_minPHPVersionId' => 70100,
            ],

            // PHP 8.0+
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\Union::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(int|float $arg)',
                '_minPHPVersionId' => 80000,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\NullableUnion::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(int|float|null $arg)',
                '_minPHPVersionId' => 80000,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\ConstructorPropertyPromotion::class,
                'method' => '__construct',
                'expectedSignature' => 'public function __construct(public ?string $arg = null)',
                '_minPHPVersionId' => 80000,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\MixedResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): mixed',
                '_minPHPVersionId' => 80000,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\UnionResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): int|float',
                '_minPHPVersionId' => 80000,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\NullableUnionResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): int|float|null',
                '_minPHPVersionId' => 80000,
            ],

            // PHP 8.1+
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\Intersection::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(\Iterator&\Countable $arg)',
                '_minPHPVersionId' => 80100,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\InInitializer::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(\DateTime $arg = new \DateTime)',
                '_minPHPVersionId' => 80100,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\ReadonlyProperty::class,
                'method' => '__construct',
                'expectedSignature' => 'public function __construct(public readonly string $arg)',
                '_minPHPVersionId' => 80100,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\NeverResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): never',
                '_minPHPVersionId' => 80100,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\IntersectionResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): \Iterator&\Countable',
                '_minPHPVersionId' => 80100,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\StaticResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): static',
                '_minPHPVersionId' => 80100,
            ],

            // PHP 8.2+
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\TrueType::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(true $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\FalseType::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(false $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\NullType::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(null $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ArgumentSignatures\DNF::class,
                'method' => 'method',
                'expectedSignature' => 'public function method((\Iterator&\Countable)|null $arg)',
                '_minPHPVersionId' => 80200,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\TrueResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): true',
                '_minPHPVersionId' => 80200,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\FalseResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): false',
                '_minPHPVersionId' => 80200,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\NullResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): null',
                '_minPHPVersionId' => 80200,
            ],
            [
                'class' => \PHPObjectSeam\TestClasses\ResultSignatures\DNFResult::class,
                'method' => 'method',
                'expectedSignature' => 'public function method(): (\Iterator&\Countable)|null',
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
