<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class Intersection
{
    public function method(\Iterator & \Countable $arg)
    {
    }
}
