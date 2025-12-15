<?php

namespace PHPObjectSeam\ObjectSeam;

use PHPObjectSeam\Code\Exceptions\AttributeArgWithObjectDefaultValueUnsupported;
use PHPObjectSeam\CreatesObjectSeams;
use PHPObjectSeam\TestClasses\MethodAttributes\StaticClosureParam;
use PHPObjectSeam\TestClasses\TestCUT;
use PHPUnit\Framework\TestCase;

class CreatesObjectSeamsTraitTest extends TestCase
{
    use CreatesObjectSeams;

    public function testCreateObjectSeamReturnsObjectSeamInstance()
    {
        $objectSeam = $this->createObjectSeam(TestCUT::class);
        $this->assertInstanceOf(TestCUT::class, $objectSeam);
    }

    /**
     * @requires PHP >= 8.5
     */
    public function testAttributeWithClosureIsIgnored()
    {
        $objectSeam = $this->createObjectSeam(StaticClosureParam::class, [
            'ignore_attributes_with_object_default_values' => true,
        ]);
        $this->assertInstanceOf(StaticClosureParam::class, $objectSeam);
    }
    /**
     * @requires PHP >= 8.5
     */
    public function testAttributeWithClosureFails()
    {
        $this->expectException(AttributeArgWithObjectDefaultValueUnsupported::class);
        $this->createObjectSeam(StaticClosureParam::class);
    }
}
