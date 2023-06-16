<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class InInitializer
{
    public function method(\DateTime $arg = new \DateTime())
    {
    }
}
