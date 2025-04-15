<?php

namespace PHPObjectSeam\Code;

use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;

class MethodSignatureBuilder
{
    public function build(ReflectionMethod $reflectionMethod): string
    {
        $reflectionClass = $reflectionMethod->getDeclaringClass();
        $modifiers = $reflectionMethod->getModifiers();

        $modifiers &= ~ReflectionMethod::IS_ABSTRACT;

        $definition = Reflection::getModifierNames($modifiers);
        $definition[] = 'function';

        $parameterSignatures = implode(', ', $this->getParameterSignatures($reflectionMethod, $reflectionClass));
        $functionSignature = $reflectionMethod->getShortName() . '(' . $parameterSignatures . ')';

        if ($reflectionMethod->hasReturnType()) {
            $functionSignature .= ': ';
            $functionSignature .= $this->getType($reflectionMethod->getReturnType(), $reflectionClass);
        }
        $definition[] = $functionSignature;

        return implode(' ', $definition);
    }

    protected function getParameterSignatures(
        ReflectionMethod $reflectionMethod,
        ReflectionClass $reflectionClass
    ): array {
        $parameters = [];
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameters[] = $this->getParameterSignature($reflectionParameter, $reflectionMethod, $reflectionClass);
        }
        return $parameters;
    }

    protected function getParameterSignature(
        ReflectionParameter $reflectionParameter,
        ReflectionMethod $reflectionMethod,
        ReflectionClass $reflectionClass
    ): string {
        $definition = [];

        if ($reflectionMethod->getShortName() == '__construct') {
            $reflectionProperty = $this->findClassProperty($reflectionParameter->getName(), $reflectionClass);
            // $reflectionProperty is null when the parameter is not a class property, so checking if isPromoted() is
            // true is not needed
            if ($reflectionProperty !== null) {
                if ($reflectionProperty->isPublic()) {
                    $definition[] = 'public';
                } elseif ($reflectionProperty->isProtected()) {
                    $definition[] = 'protected';
                } elseif ($reflectionProperty->isPrivate()) {
                    $definition[] = 'private';
                }

                if (PHP_VERSION_ID >= 80100 && $reflectionProperty->isReadOnly()) {
                    $definition[] = 'readonly';
                }
            }
        }

        if ($reflectionParameter->hasType()) {
            $definition[] = $this->getType($reflectionParameter->getType(), $reflectionClass);
        }

        $name = '$' . $reflectionParameter->getName();
        if ($reflectionParameter->isPassedByReference()) {
            $name = '&' . $name;
        }

        if ($reflectionParameter->isVariadic()) {
            $name = '...' . $name;
        }

        $definition[] = $name;

        if ($reflectionParameter->isDefaultValueAvailable()) {
            $definition[] = '=';
            if ($reflectionParameter->isDefaultValueConstant()) {
                $definition[] = $reflectionParameter->getDefaultValueConstantName();
            } else {
                $defaultValue = $reflectionParameter->getDefaultValue();
                if (is_object($defaultValue)) {
                    $defaultValue = 'new \\' . get_class($defaultValue);
                } elseif (is_array($defaultValue)) {
                    $defaultValue = var_export($defaultValue, true);
                } else {
                    $defaultValue = json_encode($defaultValue);
                }
                $definition[] = $defaultValue;
            }
        }

        return implode(' ', $definition);
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
        if (!class_exists(\ReflectionNamedType::class)) {
            $type = $this->getFQType($reflectionType, $reflectionClass);

            return $type;
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $type = $this->getFQType($reflectionType, $reflectionClass);

            if (!$suppressNull && $type !== 'mixed' && $type !== 'null' && $reflectionType->allowsNull()) {
                $type = '?' . $type;
            }

            return $type;
        } elseif ($reflectionType instanceof \ReflectionUnionType) {
            $types = array_map(function (\ReflectionType $reflectionType) use ($reflectionClass) {
                return $this->getType($reflectionType, $reflectionClass, true, true);
            }, $reflectionType->getTypes());

            return implode('|', $types);
        } elseif ($reflectionType instanceof \ReflectionIntersectionType) {
            $types = array_map(function (\ReflectionType $reflectionType) use ($reflectionClass) {
                return $this->getType($reflectionType, $reflectionClass, true, true);
            }, $reflectionType->getTypes());

            $type = implode('&', $types);
            if ($addIntersectionBrackets) {
                $type = '(' . $type . ')';
            }
            return $type;
        } else {
            throw new \Exception('Unknown ReflectionType: ' . get_class($reflectionType));
        }
    }

    protected function getFQType(ReflectionType $reflectionType, ReflectionClass $reflectionClass): string
    {
        if (!class_exists(\ReflectionNamedType::class)) {
            $fqType = (string)$reflectionType;
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $fqType = $reflectionType->getName();
        } else {
            throw new \Exception('Invalid ReflectionType: ' . get_class($reflectionType));
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
}
