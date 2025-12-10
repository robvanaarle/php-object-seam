<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\Code\Exceptions\AttributeArgWithObjectDefaultValueUnsupported;
use PHPObjectSeam\Exception;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;
use Reflector;

class MethodBuilder
{
    protected $attributeBuilder;
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'ignore_attributes_with_object_default_values' => false,
        ], $config);

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
        return $this->getReflectorAttributes($reflectionMethod)
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

    protected function getPropertyHookMethidSignature(ReflectionMethod $reflectionMethod, string $type): CodeBlock
    {
        $code = new CodeBlock();
        $code->add($type);

        $reflectionClass = $reflectionMethod->getDeclaringClass();
        $parameters = $this->getParameterDeclarations($reflectionMethod, $reflectionClass);

        if ($parameters->lineCount() > 0) {
            $code->add('(')
                ->mergeInline($parameters)
                ->add(')');
        }

        return $code;
    }

    public function buildPropertyHook(\ReflectionProperty $reflectionProperty, array $bodies): CodeBlock
    {
        $code = new CodeBlock();

        //  Property hooks are only supported on PHP 8.4+
        if (!method_exists($reflectionProperty, 'getHooks') || !method_exists($reflectionProperty, 'getType')) {
            return $code;
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $code->add('public ')
            ->add($this->getType($reflectionProperty->getType(), $reflectionClass))
            ->add(' ')
            ->add('$' . $reflectionProperty->getName())
            ->addLine("{");

        foreach ($reflectionProperty->getHooks() as $type => $hookMethod) {
            $hook = new CodeBlock();

            $hook->merge($this->getPropertyHookMethidSignature($hookMethod, $type));
            $hook->addLine('{')
                ->mergeIndented($bodies[$type])
                ->addLine('}');

            $code->mergeIndented($hook);
        }

        $code->addLine("}");

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

        $parameters = $this->getReflectorAttributes($reflectionParameter);

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

    /**
     * @param ReflectionMethod|ReflectionParameter $reflector
     */
    protected function getReflectorAttributes(Reflector $reflector): CodeBlock
    {
        $code = new CodeBlock();

        // getAttributes is only available in PHP 8.0 and later
        if (!method_exists($reflector, 'getAttributes')) {
            return $code;
        }

        foreach ($reflector->getAttributes() as $attribute) {
            try {
                $code->merge($this->attributeBuilder->build($attribute));
            } catch (AttributeArgWithObjectDefaultValueUnsupported $e) {
                if ($this->config['ignore_attributes_with_object_default_values']) {
                    // skip attribute
                    continue;
                }

                throw new AttributeArgWithObjectDefaultValueUnsupported(
                    $e->getMessage() . "  To skip this attribute in the ObjectSeam, " .
                    "pass 'ignore_attributes_with_object_default_values' with true as config.",
                    $e->getCode(),
                    $e
                );
            }
        }

        return $code;
    }
}
