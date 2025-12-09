<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

class ObjectInstantiationParam
{
    #[\AttributeWithObjectInstantiationParam(new \DateTime())]
    public function method(): void
    {
    }
}
