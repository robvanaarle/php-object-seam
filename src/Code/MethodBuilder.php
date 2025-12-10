<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\Exception;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;

class MethodBuilder
{
    protected $attributeBuilder;

    public function __construct()
    {
        $this->attributeBuilder = new AttributeBuilder();
    }

    public function build(ReflectionMethod $reflectionMethod, CodeBlock $body): CodeBlock
    {
        $code = new CodeBlock();
        $code->merge($this->buildDeclaration($reflectionMethod))
            ->addLine('{')
            ->mergeIndented($body)
            ->addLine('}');

        return $code;
    }

    public function buildDeclaration(ReflectionMethod $reflectionMethod): CodeBlock
    {
        return $this->getMethodAttributes($reflectionMethod)
            ->merge($this->getMethodSignature($reflectionMethod));
    }

    protected function getMethodSignature(ReflectionMethod $reflectionMethod): CodeBlock
    {
        $code = new CodeBlock();

        $reflectionClass = $reflectionMethod->getDeclaringClass();
        $modifiers = $reflectionMethod->getModifiers();

        $modifiers &= ~ReflectionMethod::IS_ABSTRACT;
        $modifiersAndFunction = Reflection::getModifierNames($modifiers);
        $modifiersAndFunction[] = 'function';

        $code->add(implode(' ', $modifiersAndFunction))
            ->add(' ')
            ->add($reflectionMethod->getShortName() . '(')
            ->mergeInline($this->getParameterDeclarations($reflectionMethod, $reflectionClass))
            ->add(')');

        if ($reflectionMethod->hasReturnType()) {
            $code->add(': ')
                ->add($this->getType($reflectionMethod->getReturnType(), $reflectionClass));
        }

        return $code;
    }

    protected function getParameterDeclarations(
        ReflectionMethod $reflectionMethod,
        ReflectionClass $reflectionClass
    ): CodeBlock {
        $code = new CodeBlock();
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            if ($code->lineCount() > 0) {
                $code->add(', ');
            }
            $code->mergeInline(
                $this->getParameterDeclaration($reflectionParameter, $reflectionMethod, $reflectionClass)
            );
        }

        return $code;
    }

    protected function getParameterDeclaration(
        ReflectionParameter $reflectionParameter,
        ReflectionMethod $reflectionMethod,
        ReflectionClass $reflectionClass
    ): CodeBlock {
        $code = new CodeBlock();
        $propertiesAndName = [];

        if ($reflectionMethod->getShortName() == '__construct') {
            $reflectionProperty = $this->findClassProperty($reflectionParameter->getName(), $reflectionClass);
            // $reflectionProperty is null when the parameter is not a class property, so checking if isPromoted() is
            // true is not needed
            if ($reflectionProperty !== null) {
                if ($reflectionProperty->isPublic()) {
                    $propertiesAndName[] = 'public';
                } elseif ($reflectionProperty->isProtected()) {
                    $propertiesAndName[] = 'protected';
                } elseif ($reflectionProperty->isPrivate()) {
                    $propertiesAndName[] = 'private';
                }

                // isReadOnly() is only available in PHP 8.1 and later, but promoted properties are available in PHP 8.0
                if (method_exists($reflectionProperty, 'isReadOnly') && $reflectionProperty->isReadOnly()) {
                    $propertiesAndName[] = 'readonly';
                }
            }
        }

        if ($reflectionParameter->hasType()) {
            $propertiesAndName[] = $this->getType($reflectionParameter->getType(), $reflectionClass);
        }

        $name = '$' . $reflectionParameter->getName();
        if ($reflectionParameter->isPassedByReference()) {
            $name = '&' . $name;
        }

        if ($reflectionParameter->isVariadic()) {
            $name = '...' . $name;
        }

        $propertiesAndName[] = $name;
        $code->add(implode(' ', $propertiesAndName));

        if ($reflectionParameter->isDefaultValueAvailable()) {
            $code->add(' = ');
            if ($reflectionParameter->isDefaultValueConstant()) {
                $code->add($reflectionParameter->getDefaultValueConstantName());
            } else {
                $defaultValue = $reflectionParameter->getDefaultValue();
                if (is_object($defaultValue)) {
                    $defaultValue = 'new \\' . get_class($defaultValue);
                } elseif (is_array($defaultValue)) {
                    $defaultValue = var_export($defaultValue, true);
                } else {
                    $defaultValue = json_encode($defaultValue);
                }
                $code->mergeInline(new CodeBlock(explode("\n", $defaultValue)));
            }
        }

        $parameters = $this->getParameterAttributes($reflectionParameter);

        // each parameter attribute should be on its own line
        if ($parameters->lineCount() > 0) {
            $parameters->prependLine();
        }

        return $parameters->merge($code);
    }

    protected function findClassProperty(string $name, ReflectionClass $reflectionClass)
    {
        $reflectionProperty = null;
        while ($reflectionClass && !$reflectionProperty) {
            $reflectionProperty = $reflectionClass->hasProperty($name)
                ? $reflectionClass->getProperty($name)
                : null;
            $reflectionClass = $reflectionClass->getParentClass();
        }
        return $reflectionProperty;
    }

    protected function getType(
        ReflectionType $reflectionType,
        ReflectionClass $reflectionClass,
        bool $suppressNull = false,
        bool $addIntersectionBrackets = false
    ): string {
        if (!class_exists(\ReflectionNamedType::class, false)) {
            $type = $this->getFQType($reflectionType, $reflectionClass);

            return $type;
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $type = $this->getFQType($reflectionType, $reflectionClass);

            if (!$suppressNull && $type !== 'mixed' && $type !== 'null' && $reflectionType->allowsNull()) {
                $type = '?' . $type;
            }

            return $type;
        } elseif (class_exists(\ReflectionUnionType::class) && $reflectionType instanceof \ReflectionUnionType) {
            $types = array_map(function (\ReflectionType $reflectionType) use ($reflectionClass) {
                return $this->getType($reflectionType, $reflectionClass, true, true);
            }, $reflectionType->getTypes());

            return implode('|', $types);
        } elseif (
            class_exists(\ReflectionIntersectionType::class)
            && $reflectionType instanceof \ReflectionIntersectionType
        ) {
            $types = array_map(function (\ReflectionType $reflectionType) use ($reflectionClass) {
                return $this->getType($reflectionType, $reflectionClass, true, true);
            }, $reflectionType->getTypes());

            $type = implode('&', $types);
            if ($addIntersectionBrackets) {
                $type = '(' . $type . ')';
            }
            return $type;
        } else {
            throw new Exception('Unknown ReflectionType: ' . get_class($reflectionType));
        }
    }

    protected function getFQType(ReflectionType $reflectionType, ReflectionClass $reflectionClass): string
    {
        if (!class_exists(\ReflectionNamedType::class, false)) {
            $fqType = (string)$reflectionType;
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $fqType = $reflectionType->getName();
        } else {
            throw new Exception('Invalid ReflectionType: ' . get_class($reflectionType));
        }

        if ($fqType === 'parent') {
            return '\\' . $reflectionClass->getParentClass()->getName();
        }

        if ($fqType === 'self') {
            return '\\' . $reflectionClass->getName();
        }

        if (!$reflectionType->isBuiltin() && !in_array($fqType, ['parent', 'self', 'static'])) {
            $fqType = '\\' . $fqType;
        }

        return $fqType;
    }

    protected function getMethodAttributes(ReflectionMethod $method): CodeBlock
    {
        $code = new CodeBlock();

        // getAttributes is only available in PHP 8.0 and later
        if (!method_exists($method, 'getAttributes')) {
            return $code;
        }

        foreach ($method->getAttributes() as $attribute) {
            $code->merge($this->attributeBuilder->build($attribute));
        }

        return $code;
    }

    protected function getParameterAttributes(ReflectionParameter $parameter): CodeBlock
    {
        $code = new CodeBlock();

        // getAttributes is only available in PHP 8.0 and later
        if (!method_exists($parameter, 'getAttributes')) {
            return $code;
        }

        $lines = [];
        foreach ($parameter->getAttributes() as $attribute) {
            $code->merge($this->attributeBuilder->build($attribute));
        }

        return $code;
    }
}
