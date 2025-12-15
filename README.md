# PHP Object Seam
*A lightweight toolkit for introducing object seams into legacy PHP code to make it testable - with minimal or no changes to the Class Under Test.*

Legacy PHP code can be difficult to extend and test because dependencies are tightly coupled and often hidden behind private methods, static helpers, or heavy constructors. Although refactoring is ideal, complexity and time constraints often prevent it. To safely add new features or fix bugs, automated tests must come first - but those same dependencies make adding tests hard.

In *Working Effectively with Legacy Code*, Michael Feathers defines a **seam** as *“a place to alter program behavior, without changing the code.”*  
This library provides an implementation of **object seams**, enabling you to:

- Invoke private/protected behavior
- Override methods, static methods and hooks at runtime
- Capture calls for assertions
- Instantiate objects without running their original constructors

All **without or minimal modifications to the original class**.

## Installation
`composer require --dev robvanaarle/php-object-seam:^1`

## Requirements
PHP >= 7.0. This package supports a wide range of PHP versions to accommodate legacy codebases.

## Example
```php
class TemperatureApi
{
    public function getCurrentTemperature(string $location): float
    {
        $weatherData = $this->getWeatherData($location);

        if ('unknown_location' === $weatherData['error']) {
            throw new \InvalidArgumentException("Unknown location: {$location}");
        }
        if (null !== $weatherData['error']) {
            throw new \RuntimeException("Weather API error: {$weatherData['error']}");
        }

        return $this->fahrenheitToCelsius($weatherData['current']['temp_f']);
    }

    private function fahrenheitToCelsius(float $fahrenheit): float
    {
        return ($fahrenheit - 32) * 5 / 9;
    }
    
    private function getWeatherData(string $location): array
    {
        $weatherData = json_decode(file_get_contents("http://api.weatherapi.com/v1/{$location}/current"), true);
        return $weatherData;
    }
}
```
Testing `TemperatureApi` is difficult because the only public method makes an actual HTTP request, which is problematic because it is slow, unreliable, and may incur costs. It should be refactored if possible, but when that is not an option, we can use object seams to test it.

Without modifying the class, we can use an object seam to call the private method `fahrenheitToCelsius()` directly to test it:

```php
public function testFahrenheitToCelsiusAtFreezingPoint(): void
{
    $api = $this->createObjectSeam(TemperatureApi::class);

    // Call the protected method via the seam.
    static::assertEquals(0.0, $api->seam()->call('fahrenheitToCelsius', 32.0));
}
```

The error handling of `getCurrentTemperature()` can be tested by first making a small change ('incision') to the `TemperatureApi` class: make `getWeatherData()` protected. This allows for overriding the method to return controlled data:

```php
public function testUnknownLocationThrowsException(): void
{
    $api = $this->createObjectSeam(TemperatureApi::class);
    // $api behaves exactly like TemperatureApi, but we can override behavior.

    // Override the getWeatherData method to simulate an unknown location.
    // Method must be protected or public to be overridden.
    $api->seam()->override('getWeatherData', function (string $location) {
        return ['error' => 'unknown_location'];
    });

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown location: Atlantis');

    $api->getCurrentTemperature('Atlantis');
}
```

## Features
These capabilities map directly to Feathers’ dependency-breaking techniques: 'Subclass and Make Public', 'Subclass and Override' and 'Expose Static Method'.

### Introspection & Invocation
- Call **protected/private methods**
- Call **protected static methods**
- Call **protected/private property** get/set hooks

### Behavior Overrides
- Override **public/protected methods**
- Override **public/protected static methods**
- Override **public/protected property hooks**

### Call Capturing
- Capture calls to public/protected methods
- Capture calls to public/protected static methods
- Capture calls to public/protected property hooks

### Object Construction
- Instantiate objects **without executing** the original constructor
- Provide a **custom constructor**
- Defer construction and call it later

### Developer Experience
- Autocomplete support in PhpStorm using `CreatesObjectSeams`
- Testing-framework agnostic

## Why Not Create Seams Manually?

Manually creating seams usually involves writing boilerplate subclasses or duplicated logic.  
PHP Object Seam provides:

- **Less code** - most seam logic is generated for you
- **Clearer test intent** - overrides are explicit
- **Faster test authoring**
- **Reusable, configurable seam instances** that can be adapted per test case

## Basic Usage in tests

```php
class CurrencyApiTest
{
    use PHPObjectSeam\CreatesObjectSeams;
    
    public function testExample(): void
    {
        // Creates an CurrencyApi&ObjectSeam object - the constructor is not executed
        $api = $this->createObjectSeam(CurrencyApi::class);
        // $api behaves exactly like CurrencyApi, but we can override behavior.
        
        $api->seam()
          ->override('connect', fn ($username, $password) => 'dummy_token')
          ->customConstruct(function($arg1) {
              $this->url = 'http://www.dummy.url/' . $arg1;
          }, 'api/v1/');
          
        // do something with $api
        static::assertEquals('dummy_token', $api->getToken());
    }
}
```

## Usage Guide

### Call non-public method
```php
$foo = $this->createObjectSeam(Foo::class);  
$result = $foo->seam()->call('nonPublicMethod', $arg1, $arg2);
```

### Call protected static method
```php
$foo = $this->createObjectSeam(Foo::class);
$result = $foo->seam()->callStatic('protectedStaticMethod', $arg1, $arg2);
```

### Call non-public property hook
```php
$foo = $this->createObjectSeam(Foo::class);  
$foo->seam()->call('$nonPublicProperty::set', $value);
$value = $foo->seam()->call('$nonPublicProperty::get');
```

### Override public or protected method
Overridden methods are executed in the scope of the object seam class.

Override with a Closure:
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->override('publicOrProtectedMethod', function(int $arg1) {
    return $this->otherMethod($arg1) * 5;
});
```

Override with a result value:
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->override('publicOrProtectedMethod', 42);
```

### Override public or protected static method
Overridden static methods are executed in the scope of the object seam class.

Override with a Closure:
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->overrideStatic('publicOrProtectedStaticMethod', function(int $arg1) {
    return parent::protectedMethod($arg1) * 3;
});
```

Override with a result value:
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->overrideStatic('publicOrProtectedStaticMethod', 9);
```

### Override public or protected property hook
Overridden property hooks are executed in the scope of the object seam class.

Override with a Closure:
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->override('$publicOrProtectedProperty::set', function(int $value) {
    $this->value = $value * 2;
})->override('$publicOrProtectedProperty::get', function() {
    return $this->value + 10;
});
```

Override with a result value:
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->override('$publicOrProtectedProperty::get', 25);
```

### Instantiate an object with a custom constructor
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->customConstruct(function($arg1) {
    $this->url = 'http://www.dummy.url/' . $arg1;
}, 'api/v1/');
```

or set a custom constructor and call it later:
```php
// i.e. in the setup of your test
$this->foo = $this->createObjectSeam(Foo::class);
$this->foo->seam()->setCustomConstructor(function($arg1) {
    $this->url = 'http://www.dummy.url/' . $arg1;
});

// in a specific test case
$this->foo->callCustomConstructor('api/v1/');
```

### Call original constructor
Often there is no need for a custom constructor; in that case, the original constructor can be called.

```php
$foo = $this->createObjectSeam(Foo::class);
$foo->__construct('bar');

// or via the seam interface
$foo->seam()->call('__construct', 'bar');
$foo->seam()->callConstruct('bar');
```

### Capture and retrieve public and protected method calls
Capturing and retrieving calls allows for asserting that a method has been called and with which arguments.

```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->captureCalls('publicOrProtectedMethod');

// do something with $foo
$foo->methodThatUsesTheCapturingMethods();

$calls = $foo->seam()->getCapturedCalls('publicOrProtectedMethod');
// assert that $calls contains a certain combination of arguments.
```


### Capture and retrieve public and protected static method calls
Capturing and retrieving static calls allows for asserting that a method has been called and with which arguments.

```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->captureStaticCalls('publicOrProtectedStaticMethod');

// do something with $foo
$foo::methodThatUsesTheCapturingMethods();

$calls = $foo->seam()->getCapturedStaticCalls('publicOrProtectedStaticMethod');
// assert that $calls contains a certain combination of arguments.
```

### Capture and retrieve public and protected property hook calls
Capturing and retrieving calls allows for asserting that a method has been called and with which arguments.

```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->captureCalls('$publicOrProtectedProperty::get')
    ->captureCalls('$publicOrProtectedProperty::set');

// do something with $foo
$foo->methodThatUsesTheCapturingProperties();

$getCalls = $foo->seam()->getCapturedCalls('$publicOrProtectedProperty::get');
$setCalls = $foo->seam()->getCapturedCalls('$publicOrProtectedProperty::set');
// assert that $calls contains a certain combination of arguments.
```