<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class StaticResult
{
    public function method(): static
    {
        return $this;
    }
}
