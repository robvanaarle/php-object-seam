<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

class ClassConstantParam
{
    public const MY_CLASS_CONSTANT = 'classValue';

    #[\AttributeWithClassConstantParam(self::MY_CLASS_CONSTANT)]
    public function method(): void
    {
    }
}
