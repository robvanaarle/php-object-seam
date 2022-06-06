<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class ArrayWithDefault
{
    public function method(array $arg = ["'foo\n"])
    {
    }
}
