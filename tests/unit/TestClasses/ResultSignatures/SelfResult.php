<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class SelfResult
{
    public function method(): self
    {
        return $this;
    }
}
