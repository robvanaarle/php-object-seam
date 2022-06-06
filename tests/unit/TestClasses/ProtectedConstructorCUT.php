<?php

namespace PHPObjectSeam\TestClasses;

class ProtectedConstructorCUT
{
    public $constructedBy = 'no constructor';
    public $originalConstructorCalled = false;

    protected function __construct()
    {
        $this->constructedBy = 'original constructor';
        $this->originalConstructorCalled = true;
    }
}
