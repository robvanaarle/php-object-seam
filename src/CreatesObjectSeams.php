<?php

namespace PHPObjectSeam;

use PHPObjectSeam\ObjectSeam\Builder;

trait CreatesObjectSeams
{
    /**
     * @template TSeamedObject
     * @phpstan-param class-string<TSeamedObject> $class
     * @phpstan-return ObjectSeam<TSeamedObject>
     */
    public function createObjectSeam(string $class): ObjectSeam
    {
        $builder = new Builder($class);
        return $builder->build();
    }
}
