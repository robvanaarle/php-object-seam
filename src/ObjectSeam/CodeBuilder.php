<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\Code\MethodSignatureBuilder;
use PHPObjectSeam\ObjectSeam;
use ReflectionClass;
use ReflectionMethod;

class CodeBuilder
{
    protected $objectSeamClass;
    protected $class;
    protected $methodSignatureBuilder;

    public function __construct(string $objectSeamClass, string $class)
    {
        $this->objectSeamClass = $objectSeamClass;
        $this->class = $class;
        $this->methodSignatureBuilder = new MethodSignatureBuilder();
    }

    public function build(): string
    {
        $code = [];
        $code[] = $this->getObjectSeamDefinition();
        $code[] = '{';

        $code[] = '    use ' . ObjectSeamTrait::class . ';';

        $code = array_merge($code, $this->getMethods());

        $code[] = '}';

        return implode("\n", $code);
    }

    protected function getObjectSeamDefinition(): string
    {
        return "class {$this->objectSeamClass} extends {$this->class} implements " . ObjectSeam::class;
    }

    protected function getMethods(): array
    {
        $code = [];

        $reflectionClass = new ReflectionClass($this->class);
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $code = array_merge($code, $this->getMethod($reflectionMethod));
        }

        return $code;
    }

    protected function getMethod(ReflectionMethod $reflectionMethod)
    {
        $code = [];

        if ($reflectionMethod->isPrivate()) {
            return $code;
        }

        $code[] = '';
        $code[] = '    ' . $this->methodSignatureBuilder->build($reflectionMethod);

        $return = 'return ';
        if ($this->shouldNotReturn($reflectionMethod)) {
            $return = '';
        }

        $call = 'call(__FUNCTION__, ...func_get_args())';
        if ($reflectionMethod->isStatic()) {
            $code[] = '    {';
            $code[] = '        ' . $return . ClassSeam::class . "::getInstance(__CLASS__)->$call;";
            $code[] = '    }';
        } else {
            $code[] = '    {';
            $code[] = '        ' . $return . Seam::class . "::getInstance(\$this)->$call;";
            $code[] = '    }';
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
        if (!class_exists(\ReflectionNamedType::class)) {
            $type = (string)$reflectionType;
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $type = $reflectionType->getName();
        }

        return in_array($type, ['void', 'never']);
    }
}
