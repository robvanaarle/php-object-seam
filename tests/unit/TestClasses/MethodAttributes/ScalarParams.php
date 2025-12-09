<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

class ScalarParams
{
    #[\AttributeWithScalarParams('stringValue', 42, 3.14, true, false, null)]
    public function method(): void
    {
    }
}
