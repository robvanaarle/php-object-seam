<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\TestClasses\TestCUTObjectSeam;
use PHPUnit\Framework\TestCase;

class ObjectSeamTraitTest extends TestCase
{
    public function testBreakInReturnsBreakerInstanceHoldByBreaker()
    {
        $objectSeam = new TestCUTObjectSeam();

        $seam = new Seam($objectSeam, new ClassSeam(TestCUTObjectSeam::class));
        Seam::setInstance($objectSeam, $seam);

        $this->assertSame($seam, $objectSeam->seam());
    }
}
