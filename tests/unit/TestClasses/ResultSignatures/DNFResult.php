<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class DNFResult
{
    //phpcs:disable PSR12.Functions.ReturnTypeDeclaration.SpaceBeforeReturnType
    public function method(): (\Iterator & \Countable) | null
    {
    }
}
