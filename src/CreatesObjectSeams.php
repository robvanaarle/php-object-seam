<?php

namespace PHPObjectSeam;

use PHPObjectSeam\ObjectSeam\Builder;

trait CreatesObjectSeams
{
    public function createObjectSeam(string $class): ObjectSeam
    {
        $builder = new Builder($class);
        return $builder->build();
    }
}
