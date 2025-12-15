<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

class WithoutParams
{
    #[\AttributeWithoutParams]
    public function method(): void
    {
    }
}
