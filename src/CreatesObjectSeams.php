<?php

namespace PHPObjectSeam;

use PHPObjectSeam\ObjectSeam\Builder;

trait CreatesObjectSeams
{
    /**
     * @template TSeamedObject of object
     * @param class-string<TSeamedObject> $class
     * @return ObjectSeam&TSeamedObject
     */
    public function createObjectSeam(string $class): ObjectSeam
    {
        $builder = new Builder($class);
        return $builder->build();
    }
}
