<?php

namespace PHPObjectSeam\TestClasses\ResultSignatures;

use PHPObjectSeam\TestClasses\TestCUT;

class ParentResult extends TestCUT
{
    public function method(): parent
    {
        return new TestCUT();
    }
}
