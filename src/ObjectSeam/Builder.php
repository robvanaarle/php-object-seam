<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\ObjectSeam;
use ReflectionClass;

class Builder
{
    /**
     * @template TSeamedObject
     *
     * @phpstan-var class-string<TSeamedObject> $class
     */
    protected $class;

    /**
     * @template TSeamedObject
     *
     * @phpstan-param class-string<TSeamedObject> $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function buildObjectSeamClass(): string
    {
        $seamClass = $this->getUniqueClassName();
        $code = $this->getCode($seamClass);
        eval($code);
        return $seamClass;
    }

    /**
     * @template TSeamedObject
     * @phpstan-param class-string<TSeamedObject> $class
     * @phpstan-return ObjectSeam<TSeamedObject>
     */
    public function build(): ObjectSeam
    {
        $seamClass = $this->buildObjectSeamClass();
        $reflectionClass = new ReflectionClass($seamClass);

        $instance = $reflectionClass->newInstanceWithoutConstructor();

        return $instance;
    }

    protected function getUniqueClassName(): string
    {
        $reflectionClass = new ReflectionClass($this->class);

        do {
            $seamClass = '__PHPObjectSeam_ObjectSeam_' . md5(rand()) . '_' . $reflectionClass->getShortName();
        } while (class_exists($seamClass, false));

        return $seamClass;
    }

    protected function getCode(string $seamClass): string
    {
        $codeBuilder = new CodeBuilder($seamClass, $this->class);
        return $codeBuilder->build();
    }
}
