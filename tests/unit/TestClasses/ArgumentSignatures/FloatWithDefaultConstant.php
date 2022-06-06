<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class FloatWithDefaultConstant
{
    public function method(float $arg = self::FOO)
    {
    }
}
