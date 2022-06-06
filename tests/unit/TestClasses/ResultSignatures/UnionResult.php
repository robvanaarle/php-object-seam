<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class UnionResult
{
    public function method(): int|float
    {
        return 42;
    }
}
