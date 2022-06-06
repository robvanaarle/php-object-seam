<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class NeverResult
{
    public function method(): never
    {
        while (true) {
        }
    }
}
