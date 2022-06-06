<?php

namespace PHPObjectSeam\TestClasses;

class TestCUT
{
    public static $staticValue = 'default';
    public $value = 'default';
    public $originalConstructorCalled = false;

    public function __construct()
    {
        $this->value = 'original constructor';
        $this->originalConstructorCalled = true;
    }

    public function publicMethod($arg): string
    {
        return 'publicMethodResult: ' . $this->value . ';' . $arg;
    }

    protected function protectedMethod($arg)
    {
        return 'protectedMethodResult: ' . $this->value . ';' . $arg;
    }

    private function privateMethod($arg)
    {
        return 'privateMethodResult: ' . $this->value . ';' . $arg;
    }

    public function callProtectedMethod($arg)
    {
        return $this->protectedMethod($arg);
    }

    public static function publicStaticMethod($arg)
    {
        return 'publicStaticMethodResult: ' . self::$staticValue . ';' . $arg;
    }

    protected static function protectedStaticMethod($arg)
    {
        return 'protectedStaticMethodResult: ' . self::$staticValue . ';' . $arg;
    }

    private static function privateStaticMethod($arg)
    {
        return 'privateStaticMethodResult: ' . self::$staticValue . ';' . $arg;
    }

    public static function callProtectedStaticMethod($arg)
    {
        return static::protectedStaticMethod($arg);
    }
}
