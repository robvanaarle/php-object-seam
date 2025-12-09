<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

class ArrayParam
{
    #[\AttributeWithArrayParam([1, "2", 3.0, true, null, [4, 5], 'six' => 6])]
    public function method(): void
    {
    }
}
