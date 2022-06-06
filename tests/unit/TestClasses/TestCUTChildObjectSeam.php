<?php

namespace PHPObjectSeam\TestClasses;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\TestClasses\TestCUT;
use PHPObjectSeam\ObjectSeam\Seam;

class TestCUTChildObjectSeam extends TestCUTChild implements ObjectSeam
{
    use ObjectSeam\ObjectSeamTrait;
}
