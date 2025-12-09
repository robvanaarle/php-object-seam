<?php

namespace PHPObjectSeam\Code;

class AttributeBuilder
{
    /** @phpstan-ignore-next-line */
    public function build(\ReflectionAttribute $attribute): string
    {
        /** @phpstan-ignore-next-line */
        $name = $attribute->getName();
        /** @phpstan-ignore-next-line */
        $args = $attribute->getArguments();

        if (!empty($args)) {
            $argParts = [];

            foreach ($args as $key => $value) {
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
