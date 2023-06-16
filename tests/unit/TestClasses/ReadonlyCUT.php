<?php

//phpcs:disable
namespace PHPObjectSeam\TestClasses;

readonly class ReadonlyCUT
{
    public function __construct(public string $arg)
    {
    }
}
