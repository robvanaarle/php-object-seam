<?php

namespace PHPObjectSeam\TestClasses\MethodAttributes;

class StaticClosureParam
{
    #[\AttributeWithStaticClosureParam(static function (int $a = 42) {
        return 'staticClosureValue';
    })]
    public function method(): void
    {
    }
}
