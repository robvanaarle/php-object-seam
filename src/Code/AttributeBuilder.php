<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\Code\Exceptions\AttributeArgWithObjectDefaultValueUnsupported;

class AttributeBuilder
{
    /* @phpstan-ignore class.notFound */
    public function build(\ReflectionAttribute $attribute): CodeBlock
    {
        /* @phpstan-ignore class.notFound */
        $name = $attribute->getName();
        /* @phpstan-ignore class.notFound */
        $args = $attribute->getArguments();

        $code = new CodeBlock();

        if (!empty($args)) {
            $arguments = new CodeBlock();
            foreach ($args as $key => $value) {
                // objects are not supported, as it's impossible to get the original code representation
                if (is_object($value)) {
                    throw new AttributeArgWithObjectDefaultValueUnsupported(
                        "Attribute arguments with default value of type object are not supported by PHP Object Seam " .
                        "(attribute #[{$name}], argument {$key}, type " . get_class($value) . ")."
                    );
                }

                // Encode argument value safely
                $argument = new CodeBlock(explode("\n", var_export($value, true)));

                // Named argument?
                if (is_string($key)) {
                    $argument->prepend($key . ': ');
                }

                if ($arguments->lineCount() > 0) {
                    $arguments->add(', ');
                }
                $arguments->mergeInline($argument);
            }

            $code->add("#[{$name}(")
                ->mergeInline($arguments)
                ->add(')]');
        } else {
            $code->addLine("#[{$name}]");
        }

        return $code;
    }

    protected function mergeArgPart(array $argParts, array $argPart): array
    {
        if (count($argParts) === 0) {
            $argParts = $argPart;
        } else {
            $argParts[count($argParts) - 1] .= ', ' . $argPart[0];

            if (count($argPart) > 1) {
                $argParts = array_merge($argParts, array_slice($argPart, 1));
            }
        }

        return $argParts;
    }
}
