<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\ObjectSeam;
use PHPUnit\Framework\TestCase;

class ObjectSeamTraitTest extends TestCase
{
    public function testBreakInReturnsBreakerInstanceHoldByBreaker()
    {
        // Uses the ObjectSeamTrait without using an excluded TestClass, so PHPstan will not report this trait is unused
        $objectSeam = new class implements ObjectSeam {
            use ObjectSeamTrait;
        };

        $seam = new Seam($objectSeam, new ClassSeam(get_class($objectSeam)));
        Seam::setInstance($objectSeam, $seam);

        $this->assertSame($seam, $objectSeam->seam());
    }
}
