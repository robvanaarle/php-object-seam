<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

const MY_GLOBAL_CONSTANT = 'globalValue';

class GlobalConstantParam
{
    #[\AttributeWithGlobalConstantParam(MY_GLOBAL_CONSTANT)]
    public function method(): void
    {
    }
}
