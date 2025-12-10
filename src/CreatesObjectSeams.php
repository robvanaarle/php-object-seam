<?php

namespace PHPObjectSeam;

use PHPObjectSeam\ObjectSeam\Builder;

trait CreatesObjectSeams
{
    /**
     * @template TSeamedObject of object
     * @param class-string<TSeamedObject> $class
     * @param array{ignore_attributes_with_object_default_values?: bool } $config
     * @return ObjectSeam&TSeamedObject
     */
    public function createObjectSeam(string $class, array $config = []): ObjectSeam
    {
        $builder = new Builder($class, $config);
        return $builder->build();
    }
}
