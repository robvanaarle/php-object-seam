<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class ConstructorPropertyPromotion
{
    public function __construct(public ?string $arg = null)
    {
    }
}
