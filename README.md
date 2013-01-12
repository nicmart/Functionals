# Functionals

Functionals is a simple library that provides a set of functionals written in php.

[![Build Status](https://secure.travis-ci.org/nicmart/Functionals.png?branch=master)](http://travis-ci.org/nicmart/Functionals)

## What do you mean with "functionals"?

Here I use the term "functional" to denote [Higher-Order functions](http://en.wikipedia.org/wiki/Higher-order_function),
i.e. functions that take other functions as input and return a function as output.

## Install

The best way to install Functionals is [through composer](http://getcomposer.org).

Just create a composer.json file for your project:

```JSON
{
    "require": {
        "nicmart/functionals": "dev-master"
    }
}
```

Then you can run these two commands to install it:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

or simply run `composer install` if you have have already [installed the composer globally](http://getcomposer.org/doc/00-intro.md#globally).

Then you can include the autoloader, and you will have access to the library classes:

```php
<?php
require 'vendor/autoload.php';

use Functionals\Functionals;
```

## Usage

### Composition of functions

If you have two functions `f : A → B` and `g: B → C` their composition is a function `h: A → C` that maps `x` to `f(g(x))`.

In Functionals you can compose an arbitrary number of
[php callables](http://it1.php.net/manual/en/language.types.callable.php) through `Functionals::compose()`:

```php
$sum = function($a, $b) { return $a + $b; };
$half = function($n) { return $n/2; };

$middle = Functionals::compose($half, $sum);

echo $middle(10, 16); //Prints 13
echo $middle(-10, 10); //Prints 0
```

You can compose an arbitrary long list of functions, and they can be any callable:

```php
$beautifyString = Functionals::compose(
    function($s){ return str_replace('bad', 'good', $s); },
    'ucfirst',
    'strtolower',
    'trim'
);

echo $beautifyString('   i\'m a reAlly Bad writTen STRING');
//prints "I'm a really good written string"
```

### Partial
A [partial application](http://en.wikipedia.org/wiki/Partial_application) of a function in several variables is obtained
by fixing some arguments of the function and get a function of the remaining arguments.

For example, if you have a function `f : X x Y → B`, and you fix a `x` in `X`, then you get the partial
function `g: Y → B` that maps `y` to `f(x,y)`.

In Functionals you can obtain a partial function application with the method `Functionals::partial()`:

```php
$sum = function($a, $b) { return $a + $b; };
$next = Functionals::partial($sum, array(1));

$next(2);  // 3
$next(10); // 11
```

You can fix arguments in any position, specifying the right index for the fixed arguments array:

```php
$if = function($condition, $ifTrue, $ifFalse) { return $condition ? $ifTrue : $ifFalse; };

$boolDump = Functionals::partial($if, array( 1 => 'TRUE!', 2 => 'FALSE!'))

$bool(true);  // TRUE!
$next(false); // FALSE!
```

### Currying

To get a [curried version](http://en.wikipedia.org/wiki/Currying) of a function in several variable, use
the `Functionals::curry` method:

```php
$sum = function($a, $b, $c) { return $a + $b + $c; };
$a = Functionals::curry($sum);
$b = $a(1);
$c = $b(2);

$c(10);  // 13
$c(101); // 104
```

### Combine

Given a set of functions `f, g, h, ...` that act on the same domain,
a combined version of that functions is the function

    x → array(f(x), g(x), h(x), ...)

In functionals you can easily combine functions with `Functionals::combine`:

```php
$stringVersions = Functionals::combine('strtolower', 'strtoupper', 'ucfirst');

$stringVersions('hElLo'); // array('hello', 'HELLO', 'HElLo')
```

Tests
-----

    $ phpunit

License
-------
MIT, see LICENSE.

