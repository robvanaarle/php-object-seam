<?php

namespace PHPObjectSeam\Code;

use PHPObjectSeam\Exception;

class AttributeBuilder
{
    /* @phpstan-ignore class.notFound */
    public function build(\ReflectionAttribute $attribute): string
    {
        /* @phpstan-ignore class.notFound */
        $name = $attribute->getName();
        /* @phpstan-ignore class.notFound */
        $args = $attribute->getArguments();

        if (!empty($args)) {
            $argParts = [];

            foreach ($args as $key => $value) {
                // objects are not supported, as it's impossible to get the original code representation
                if (is_object($value)) {
                    throw new Exception(
                        "Attribute arguments of type object are not supported: (" . get_class($value) . ")"
                    );
                }

                // Encode argument value safely
                $encoded = var_export($value, true);

                // Named argument?
                if (is_string($key)) {
                    $argParts[] = "{$key}: {$encoded}";
                } else {
                    $argParts[] = $encoded;
                }
            }

            $argString = implode(', ', $argParts);
            return "#[{$name}({$argString})]";
        } else {
            return "#[{$name}]";
        }
    }
}
