<?php

namespace PHPObjectSeam\TestClasses;

class MethodProvider
{
    public function provideMethods(): array
    {
        // base
        $methods = [
            [
                \PHPObjectSeam\TestClasses\ArgumentSignatures\NameOnly::class,
                'public function method($arg)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Scalar::class,
                'public function method(bool $arg1, int $arg2, float $arg3, string $arg4)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\StringWithDefault::class,
                'public function method(string $arg = "\'foo\\n")',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\IntWithDefaultNull::class,
                'public function method(int $arg = null)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\FloatWithDefaultConstant::class,
                'public function method(float $arg = self::FOO)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\ArrayByReference::class,
                'public function method(array &$arg)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Splat::class,
                'public function method(...$arg)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\Classname::class,
                'public function method(\Exception $arg)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\ArrayWithDefault::class,
                'public function method(array $arg = array (' . "\n" . '  0 => \'\\\'foo' . "\n" . '\',' . "\n" . '))',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\BoolWithDefault::class,
                'public function method(bool $arg = false)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ArgumentSignatures\SelfType::class,
                'public function method(\PHPObjectSeam\TestClasses\ArgumentSignatures\SelfType $arg)',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ResultSignatures\NoResult::class,
                'public function method()',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ResultSignatures\StringResult::class,
                'public function method(): string',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ResultSignatures\ClassnameResult::class,
                'public function method(): \Exception',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ResultSignatures\ParentResult::class,
                'public function method(): \PHPObjectSeam\TestClasses\TestCUT',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ResultSignatures\ParentResultInParent::class,
                'public function method(): \PHPObjectSeam\TestClasses\TestCUT',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult::class,
                'public function method(): \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult',
                'method'
            ],[
                \PHPObjectSeam\TestClasses\ResultSignatures\SelfResultInParent::class,
                'public function method(): \PHPObjectSeam\TestClasses\ResultSignatures\SelfResult',
                'method'
            ],
        ];

        if (PHP_VERSION_ID >= 70100) {
            $methods = array_merge($methods, [
                [
                    \PHPObjectSeam\TestClasses\ResultSignatures\VoidResult::class,
                    'public function method(): void',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\NullableStringResult::class,
                    'public function method(): ?string',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\NullableIntWithDefaultNull::class,
                    'public function method(?int $arg = null)',
                    'method'
                ],[
                    // overrides IntWithDefaultNull in base
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\IntWithDefaultNull::class,
                    'public function method(?int $arg = null)',
                    'method'
                ],
            ]);
        }

        if (PHP_VERSION_ID >= 80000) {
            $methods = array_merge($methods, [
                [
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\Union::class,
                    'public function method(int|float $arg)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\NullableUnion::class,
                    'public function method(int|float|null $arg)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\ConstructorPropertyPromotion::class,
                    'public function __construct(public ?string $arg = null)',
                    '__construct'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\MixedResult::class,
                    'public function method(): mixed',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\UnionResult::class,
                    'public function method(): int|float',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\NullableUnionResult::class,
                    'public function method(): int|float|null',
                    'method'
                ],
            ]);
        }

        if (PHP_VERSION_ID >= 80100) {
            $methods = array_merge($methods, [
                [
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\Intersection::class,
                    'public function method(\Iterator&\Countable $arg)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\InInitializer::class,
                    'public function method(\DateTime $arg = new \DateTime)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\ReadonlyProperty::class,
                    'public function __construct(public readonly string $arg)',
                    '__construct'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\NeverResult::class,
                    'public function method(): never',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\IntersectionResult::class,
                    'public function method(): \Iterator&\Countable',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\StaticResult::class,
                    'public function method(): static',
                    'method'
                ],
            ]);
        }

        if (PHP_VERSION_ID >= 80200) {
            $methods = array_merge($methods, [
                [
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\TrueType::class,
                    'public function method(true $arg)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\FalseType::class,
                    'public function method(false $arg)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\NullType::class,
                    'public function method(null $arg)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ArgumentSignatures\DNF::class,
                    'public function method((\Iterator&\Countable)|null $arg)',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\TrueResult::class,
                    'public function method(): true',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\FalseResult::class,
                    'public function method(): false',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\NullResult::class,
                    'public function method(): null',
                    'method'
                ],[
                    \PHPObjectSeam\TestClasses\ResultSignatures\DNFResult::class,
                    'public function method(): (\Iterator&\Countable)|null',
                    'method'
                ],
            ]);
        }

        // deduplicate
        $result = [];
        foreach ($methods as $method) {
            $result[$method[0]] = $method;
        }
        return array_values($result);
    }
}
