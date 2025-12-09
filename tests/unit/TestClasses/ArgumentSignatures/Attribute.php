<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class Attribute
{
    public function method(
        #[\AttributeWithoutParams]
        #[\AttributeWithParams('value', 123, null, true, false, [1, 2, 3])]
        $param
    ): void {
    }
}
