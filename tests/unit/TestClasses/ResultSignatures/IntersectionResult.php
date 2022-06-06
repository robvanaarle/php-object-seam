<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

class IntersectionResult
{
    public function method(): \Iterator & \Countable
    {
    }
}
