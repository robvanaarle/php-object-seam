<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class NullableUnion
{
    public function method(int|float|null $arg)
    {
    }
}
