<?php

namespace PHPObjectSeam\TestClasses\ArgumentSignatures;

class StaticClosureAttributeParam
{
    public function method(
        #[\AttributeWithParams(
            static function ($value) {
                return $value;
            }
        )]
        $param
    ): void {
    }
}
