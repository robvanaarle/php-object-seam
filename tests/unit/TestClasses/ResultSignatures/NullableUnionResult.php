<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class NullableUnionResult
{
    public function method(): int|float|null
    {
        return 42;
    }
}
