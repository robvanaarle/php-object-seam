<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

class AttributeClassNameParam
{
    #[\AttributeWithAttributeClassNameParam(AttributeClassNameParam::class)]
    public function method(): void
    {
    }
}
