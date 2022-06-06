<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class SelfType
{
    public function method(self $arg)
    {
    }
}
