<?php

namespace PHPObjectSeam\ObjectSeam;

use Closure;
use PHPObjectSeam\Exception;
use PHPObjectSeam\ObjectSeam;
use ReflectionMethod;

class Seam
{
    protected $objectSeam;
    protected $classSeam;
    protected $overrides = [];
    protected $customConstructor = null;
    protected $captureFunctions = [];
    protected $capturedCalls = [];

    protected static $instances = [];

    public function __construct(ObjectSeam $objectSeam, ClassSeam $classSeam)
    {
        $this->objectSeam = $objectSeam;
        $this->classSeam = $classSeam;
    }

    public static function getInstance(ObjectSeam $objectSeam)
    {
        if (!isset(static::$instances[spl_object_hash($objectSeam)])) {
            static::setInstance($objectSeam, new static(
                $objectSeam,
                ClassSeam::getInstance(get_class($objectSeam))
            ));
        }

        return static::$instances[spl_object_hash($objectSeam)];
    }

    public static function setInstance(ObjectSeam $objectSeam, Seam $seam)
    {
        static::$instances[spl_object_hash($objectSeam)] = $seam;
    }

    public function override(string $function, $resultOrClosure): self
    {
        $reflectionMethod = $this->getReflectionMethod($function);
        if ($reflectionMethod->isStatic()) {
            throw new Exception('Cannot override static method ' . $function . ', use overrideStatic instead');
        }

        if ($reflectionMethod->isPrivate()) {
            throw new Exception("Cannot override private method {$function}");
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
        if ($reflectionMethod->isStatic()) {
            throw new Exception('Cannot call static method ' . $function . ', use callStatic instead');
        }

        $this->captureCall($function, $args);

        $closure = $this->getClosure($function);
        return $closure(...$args);
    }

    protected function getClosure(string $function): Closure
    {
        if (isset($this->overrides[$function])) {
            return $this->overrides[$function]->bindTo($this->objectSeam, $this->objectSeam);
        }
        $reflectionMethod = $this->getReflectionMethod($function);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->getClosure($this->objectSeam);
    }

    public function callConstruct(...$args)
    {
        $this->call('__construct', ...$args);
    }

    public function customConstruct(Closure $closure, ...$args)
    {
        $this->setCustomConstructor($closure)
            ->callCustomConstructor(...$args);
    }

    public function setCustomConstructor(Closure $closure): self
    {
        $this->customConstructor = $closure;
        return $this;
    }

    public function callCustomConstructor(...$args)
    {
        if ($this->customConstructor === null) {
            return;
        }

        $closure = $this->customConstructor->bindTo($this->objectSeam, $this->objectSeam);
        $closure(...$args);
    }

    public function captureCalls(string $function, bool $enable = true): self
    {
        $reflectionMethod = $this->getReflectionMethod($function);
        if ($reflectionMethod->isStatic()) {
            throw new Exception(
                'Cannot capture calls for static method ' . $function . ', use captureStaticCalls instead'
            );
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

    protected function getReflectionMethod(string $function): ReflectionMethod
    {
        $reflectionMethod = new ReflectionMethod(get_parent_class($this->objectSeam), $function);
        return $reflectionMethod;
    }

    public function overrideStatic(string $function, $resultOrClosure): self
    {
        $this->classSeam->override($function, $resultOrClosure);
        return $this;
    }

    public function callStatic(string $function, ...$args)
    {
        return $this->classSeam->call($function, ...$args);
    }

    public function captureStaticCalls(string $function): self
    {
        $this->classSeam->captureCalls($function);
        return $this;
    }

    public function getCapturedStaticCalls(string $function): array
    {
        return $this->classSeam->getCapturedCalls($function);
    }
}
