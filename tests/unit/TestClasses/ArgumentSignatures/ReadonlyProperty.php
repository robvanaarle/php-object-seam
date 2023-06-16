<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class ReadonlyProperty
{
    public function __construct(public readonly string $arg)
    {
    }
}
