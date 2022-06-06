<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class StringWithDefault
{
    public function method(string $arg = "'foo\n")
    {
    }
}
