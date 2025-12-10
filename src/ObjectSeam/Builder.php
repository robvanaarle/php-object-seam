<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\Code\ObjectSeamBuilder;
use PHPObjectSeam\ObjectSeam;
use ReflectionClass;

/**
 * @template TSeamedObject of object
 */
class Builder
{
    /**
     * @var class-string<TSeamedObject> $class
     */
    protected $class;

    /**
     * @var array{ignore_attributes_with_object_default_values?: bool } $config
     */
    protected $config;

    /**
     * @param class-string<TSeamedObject> $class
     * @param array{ignore_attributes_with_object_default_values?: bool } $config
     */
    public function __construct(string $class, array $config = [])
    {
        $this->class = $class;
        $this->config = $config;
    }

    public function buildObjectSeamClass(): string
    {
        $seamClass = $this->getUniqueClassName();
        $code = $this->getCode($seamClass);
        eval($code);
        return $seamClass;
    }

    /**
     * @return ObjectSeam&TSeamedObject
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
            $seamClass = '__PHPObjectSeam_ObjectSeam_' . md5((string)rand()) . '_' . $reflectionClass->getShortName();
        } while (class_exists($seamClass, false));

        return $seamClass;
    }

    protected function getCode(string $seamClass): string
    {
        $codeBuilder = new ObjectSeamBuilder($seamClass, $this->class, $this->config);
        return $codeBuilder->build()->toString();
    }
}
