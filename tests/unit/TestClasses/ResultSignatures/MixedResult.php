<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class MixedResult
{
    public function method(): mixed
    {
        return 42;
    }
}
