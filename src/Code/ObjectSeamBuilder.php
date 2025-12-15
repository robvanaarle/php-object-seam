<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\ObjectSeam\ClassSeam;
use PHPObjectSeam\ObjectSeam\ObjectSeamTrait;
use PHPObjectSeam\ObjectSeam\Seam;
use ReflectionClass;
use ReflectionMethod;

class ObjectSeamBuilder
{
    protected $objectSeamClass;
    protected $class;
    protected $methodDeclarationBuilder;

    public function __construct(string $objectSeamClass, string $class, array $config = [])
    {
        $this->objectSeamClass = $objectSeamClass;
        $this->class = $class;
        $this->methodDeclarationBuilder = new MethodBuilder($config);
    }

    public function build(): CodeBlock
    {
        $code = new CodeBlock();
        $code->addLine($this->getObjectSeamDeclaration())
            ->addLine('{')
            ->addLineIndented('use ' . ObjectSeamTrait::class . ';')
            ->merge($this->getPropertyHooks())
            ->merge($this->getMethodDefinitions())
            ->addLine('}');

        return $code;
    }

    protected function getObjectSeamDeclaration(): string
    {
        $reflectionClass = new ReflectionClass($this->class);

        $readonly = '';
        if (method_exists($reflectionClass, 'isReadOnly') && $reflectionClass->isReadOnly()) {
            $readonly = 'readonly ';
        }

        return "{$readonly}class {$this->objectSeamClass} extends {$this->class} implements " . ObjectSeam::class;
    }

    protected function getMethodDefinitions(): CodeBlock
    {
        $code = new CodeBlock();

        $reflectionClass = new ReflectionClass($this->class);
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->isPrivate()) {
                continue;
            }
            $body = $this->getMethodBody($reflectionMethod);
            $method = $this->methodDeclarationBuilder->build($reflectionMethod, $body);

            $code->addLine()
                ->mergeIndented($method);
        }

        return $code;
    }

    protected function getPropertyHooks(): CodeBlock
    {
        $code = new CodeBlock();

        $reflectionClass = new ReflectionClass($this->class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            //  Property hooks are only supported on PHP 8.4+
            if (!method_exists($reflectionProperty, 'getHooks')) {
                continue;
            }

            $hooks = $reflectionProperty->getHooks();
            if (count($hooks) === 0) {
                continue;
            }

            $bodies = [];
            foreach ($hooks as $type => $hook) {
                $bodies[$type] = $this->getMethodBody($hook);
            }

            $hook = $this->methodDeclarationBuilder->buildPropertyHook($reflectionProperty, $bodies);
            $code->addLine()
                ->mergeIndented($hook);
        }

        return $code;
    }

    protected function getMethodBody(ReflectionMethod $reflectionMethod): CodeBlock
    {
        $code = new CodeBlock();

        $return = 'return ';
        if ($this->shouldNotReturn($reflectionMethod)) {
            $return = '';
        }

        $call = 'call(__FUNCTION__, ...func_get_args())';
        if ($reflectionMethod->isStatic()) {
            $code->addLine($return . ClassSeam::class . "::getInstance(__CLASS__)->$call;");
        } else {
            $code->addLine($return . Seam::class . "::getInstance(\$this)->$call;");
        }

        return $code;
    }

    protected function shouldNotReturn(ReflectionMethod $reflectionMethod): bool
    {
        if (!$reflectionMethod->hasReturnType()) {
            return false;
        }

        $reflectionType = $reflectionMethod->getReturnType();

        $type = 'other';
        if (!class_exists(\ReflectionNamedType::class, false)) {
            $type = (string)$reflectionType;
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $type = $reflectionType->getName();
        }

        return in_array($type, ['void', 'never']);
    }
}
