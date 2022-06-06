<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class NullableIntWithDefaultNull
{
    public function method(?int $arg = null)
    {
    }
}
