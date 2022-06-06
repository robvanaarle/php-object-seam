<?php

namespace PHPObjectSeam\ObjectSeam;

use Closure;
use ReflectionMethod;
use PHPObjectSeam\Exception;

class ClassSeam
{
    public $objectSeamClass;
    protected $overrides = [];
    protected $captureFunctions = [];
    protected $capturedCalls = [];

    protected static $instances = [];

    public function __construct(string $objectSeamClass)
    {
        $this->objectSeamClass = $objectSeamClass;
    }

    public static function getInstance(string $objectSeamClass)
    {
        if (!isset(static::$instances[$objectSeamClass])) {
            static::setInstance($objectSeamClass, new static($objectSeamClass));
        }

        return static::$instances[$objectSeamClass];
    }

    public static function setInstance(string $objectSeamClass, ClassSeam $seam)
    {
        static::$instances[$objectSeamClass] = $seam;
    }

    public function override(string $function, $resultOrClosure): self
    {
        $reflectionMethod = $this->getReflectionMethod($function);

        if (!$reflectionMethod->isStatic()) {
            throw new Exception("Cannot override non-static method {$function}");
        }

        if ($reflectionMethod->isPrivate()) {
            throw new Exception("Cannot override private static method {$function}");
        }

        if ($resultOrClosure instanceof Closure) {
            $this->overrides[$function] = $resultOrClosure;
        } else {
            $this->overrides[$function] = function () use ($resultOrClosure) {
                return $resultOrClosure;
            };
        }

        return $this;
    }

    public function call(string $function, ...$args)
    {
        $reflectionMethod = $this->getReflectionMethod($function);

        if (!$reflectionMethod->isStatic()) {
            throw new Exception("Cannot call non-static method {$function}");
        }

        if ($reflectionMethod->isPrivate()) {
            throw new Exception("Cannot call private static method {$function}");
        }

        $this->captureCall($function, $args);
        $closure = $this->getClosure($function);
        return $closure(...$args);
    }

    public function captureCalls(string $function, bool $enable = true): self
    {
        $reflectionMethod = $this->getReflectionMethod($function);
        if (!$reflectionMethod->isStatic()) {
            throw new Exception("Cannot capture non-static method {$function}");
        }

        if ($reflectionMethod->isPrivate()) {
            throw new Exception("Cannot capture private static method {$function}");
        }

        $this->captureFunctions[$function] = $enable;
        return $this;
    }

    public function getCapturedCalls(string $function): array
    {
        if (isset($this->capturedCalls[$function])) {
            return $this->capturedCalls[$function];
        }

        return [];
    }

    protected function captureCall($function, array $args)
    {
        if (!isset($this->captureFunctions[$function]) || !$this->captureFunctions[$function]) {
            return;
        }

        if (!isset($this->capturedCalls[$function])) {
            $this->capturedCalls[$function] = [];
        }

        $this->capturedCalls[$function][] = $args;
    }

    protected function getClosure(string $function): Closure
    {
        if (isset($this->overrides[$function])) {
            return $this->overrides[$function]->bindTo(null, $this->objectSeamClass);
        }

        $closure = function (...$args) use ($function) {
            return parent::$function(...$args);
        };

        return $closure->bindTo(null, $this->objectSeamClass);
    }

    protected function getReflectionMethod(string $function): ReflectionMethod
    {
        $reflectionMethod = new ReflectionMethod(get_parent_class($this->objectSeamClass), $function);
        return $reflectionMethod;
    }
}
