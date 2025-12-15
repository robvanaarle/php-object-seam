<?php

namespace PHPObjectSeam\TestClasses;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\TestClasses\TestCUT;
use PHPObjectSeam\ObjectSeam\Seam;

class TestCUTPropertyHooksObjectSeam extends TestCUTPropertyHooks implements ObjectSeam
{
    use ObjectSeam\ObjectSeamTrait;
}
