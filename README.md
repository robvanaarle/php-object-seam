# PHP Object Seam
PHP Object Seam provides an easy way to create object seams in PHP. These are used in legacy code for breaking dependencies to make code testable with minimal changes to the Class Under Test.

Legacy code is hard to extend and maintain because of dependencies. Ideally it needs to be refactored, however there isn't always time for that. To be confident about new features or bug fixes to legacy code, automated tests need to be in place. But legacy code tends not to have (enough) automated tests. These are also hard to add, because of the same dependencies. To put tests in place, the dependencies have to be broken first by changing the code. These changes need to be tested as well, but that is difficult because of the same reasons.

In his book _Working Effectively with Legacy Code_ Michael Feathers defines a seam as _"a place to alter program behavior, without changing the code"_. This enables breaking of dependencies and adding automated tests with no or minimal change to the code. This library offers a way to create one of the seam types: object seams. With object seams it's possible to change the Object Under Test in automated tests and leave the code of the Class Under Test as is.


## Installation
`composer require --dev robvanaarle/php-object-seam ^1`

## Requirements
PHP >= 7.0

As legacy code often runs on older PHP versions, this package aims to support as many PHP versions as possible.

## Features
- Call protected and private methods
- Call protected static methods
- Override public and protected methods
- Override public and protected static methods
- Instantiate an object with a custom constructor
- Capture and retrieve public and protected method calls
- Capture and retrieve public and protected static method calls
- Autocomplete in PhpStorm when using the `CreatesObjectSeams` trait

This allows for the following dependency breaking techniques from the book _Working Effectively with Legacy Code_.
- Subclass and make public
- Subclass and override
- Expose static method

## Advantages over manually creating object seam code
- Less code: much of the required code is generated
- Faster to write
- More explicit about the intent to break dependencies: manually created object seam code tends to become fuzzy
- An `ObjectSeam` can be partially constructed before tests and altered (and even constructed) for specific test

## Basic Usage
Use the trait `PHPObjectSeam\CreatedObjectSeams` in your test class to create an `ObjectSeams`. An `ObjectSeam` is usually created for the Object Under Test. It can then be altered with no or minimal code changes to the Class Under Test, for example to call non-public methods or override method behaviour. A created `ObjectSeam` is unconstructed: the original constructor, `__construct`, has not been called. This allows for setting up an `ObjectSeam` that can be reused and customized by multiple tests.

```php
class FooTest
{
    use PHPObjectSeam\CreatesObjectSeams;
    
    public function testBar(): void
    {
        $foo = $this->createObjectSeam(Foo::class);
        // $foo has type Foo&PHPObjectSeam\ObjectSeam
        
        // Access seam through seam() to alter the behaviour of the object
        $foo->seam()
          ->override('connect', fn ($username, $password) => 'dummy_token')
          ->customConstruct(function($arg1) {
              $this->url = 'http://www.dummy.url/' . $arg1;
          }, 'api/v1/');
          
        // do something with $foo and perform an assertion
    }
}
```

## Usage

### Call non-public method
```php
$foo = $this->createObjectSeam(Foo::class);  
$result = $foo->seam()->call('nonPublicMethod', $arg1, $arg2);
```

This can be used for 'Subclass and make public'.

### Call protected static method
```php
$foo = $this->createObjectSeam(Foo::class);
$result = $foo->seam()->callStatic('protectedStaticMethod', $arg1, $arg2);
```

This can be used for 'Subclass and make public'.

### Override public or protected method
Overridden methods are executed in the scope of the object seam class.

Override with a Closure:
```php
$foo = $this->createObjectSeam(Foo::class);
$result = $foo->seam()->override('protectedMethod', function(int $arg1) {
  return $this->otherMethod($arg1) * 5;
});
```

Override with a result value:
```php
$foo = $this->createObjectSeam(Foo::class);
$result = $foo->seam()->override('protectedMethod', 42);
```

This can be used for 'Subclass and override' with the goal altering behaviour of a public or protected method.

### Override public or protected static method
Overridden static methods are executed in the scope of the object seam class.

Override with a Closure:
```php
$foo = $this->createObjectSeam(Foo::class);
$result = $foo->seam()->overrideStatic('protectedStaticMethod', function(int $arg1) {
  return parent::protectedMethod($arg1) * 3;
});
```

Override with a result value:
```php
$foo = $this->createObjectSeam(Foo::class);
$result = $foo->seam()->overrideStatic('protectedStaticMethod', 9);
```

This can be used for 'Subclass and override' with the goal altering behaviour of a public or protected static method.

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

'Expose static method' can be achieved by not using a constructor and by calling the desired method. There is then no need to make that method static.

```php
$this->foo = $this->createObjectSeam(Foo::class);
$this->foo->seam()->call('methodThatDoesNotUseThisKeyword');
```

### Call original constructor
Often there is no need for a custom constructor, the original constructor has to be called then.

```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->call('__construct', 'bar');
```

or use the helper method `callConstruct()` for this
```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->callConstruct('bar');
```

### Capture and retrieve public and protected method calls
Capturing and retrieving calls allows for asserting that a method has been called and with which arguments.

```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->captureCalls('publicOrProtectedMethod');

// do something with $foo
$foo->publicMethod();

$calls = $foo->seam()->getCapturedCalls('publicOrProtectedMethod');
// assert that $calls contains a certain combination of arguments.
```


### Capture and retrieve public and protected static method calls
Capturing and retrieving static calls allows for asserting that a method has been called and with which arguments.

```php
$foo = $this->createObjectSeam(Foo::class);
$foo->seam()->captureStaticCalls('publicOrProtectedStaticMethod');

// do something with $foo
$foo::publicMethod();

$calls = $foo->seam()->getCapturedStaticCalls('publicOrProtectedMethod');
// assert that $calls contains a certain combination of arguments.
```
