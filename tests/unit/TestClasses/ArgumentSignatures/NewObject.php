<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class NewObject
{
    public function method(\DateTime $arg = new \DateTime())
    {
    }
}
