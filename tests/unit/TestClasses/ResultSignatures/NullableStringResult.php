<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class NullableStringResult
{
    public function method(): ?string
    {
        return 'test';
    }
}
