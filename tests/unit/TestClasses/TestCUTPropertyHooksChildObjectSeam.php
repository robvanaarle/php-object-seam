<?php

namespace PHPObjectSeam\TestClasses;

use PHPObjectSeam\ObjectSeam;
use PHPObjectSeam\TestClasses\TestCUT;
use PHPObjectSeam\ObjectSeam\Seam;

class TestCUTPropertyHooksChildObjectSeam extends TestCUTPropertyHooksChild implements ObjectSeam
{
    use ObjectSeam\ObjectSeamTrait;
}
