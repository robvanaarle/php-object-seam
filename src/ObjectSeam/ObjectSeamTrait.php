<?php

namespace PHPObjectSeam\ObjectSeam;

trait ObjectSeamTrait
{
    public function seam(): Seam
    {
        return Seam::getInstance($this);
    }
}
