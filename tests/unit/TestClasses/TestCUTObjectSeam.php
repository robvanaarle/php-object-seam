<?php

namespace PHPObjectSeam\TestClasses;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\TestClasses\TestCUT;
use PHPObjectSeam\ObjectSeam\Seam;

class TestCUTObjectSeam extends TestCUT implements ObjectSeam
{
    use ObjectSeam\ObjectSeamTrait;
}
