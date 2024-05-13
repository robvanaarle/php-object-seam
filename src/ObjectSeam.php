<?php

namespace PHPObjectSeam;

use PHPObjectSeam\ObjectSeam\Seam;

/**
 * @template TSeamedObject of object
 * @mixin TSeamedObject
 */
interface ObjectSeam
{
    public function seam(): Seam;
}
