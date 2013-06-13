# Functionals [![Build Status](https://secure.travis-ci.org/nicmart/Functionals.png?branch=master)](http://travis-ci.org/nicmart/Functionals)

Functionals is a simple library that provides a set of functionals written in php.

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

## What's new (2013-06-13)
### Diagonalization!

It happens that you have a set indexed by two integers, and you want to completely traverse it using only
one index (do you remember the proof of countability of [rational numbers](http://en.wikipedia.org/wiki/Rational_number)?
Then this function can help you, giving a complete enumeration of the set. This is done using the inverse of the
[Cantor's pairing function](http://en.wikipedia.org/wiki/Pairing_function).

More formally, you have a function
`f : N x N → A`,
where `N` is the set of natural numbers, and A is another set. What you get is a function
`g : N → A`,
such that for each natural numbers `l` and `m` there exists an unique `n` such that
`f (l, m) = g(n)`
and the range of `f` is the same of the range of `g`.

 Example:
```php
$couples = function($x, $y) { return [$x, $y]; };
$diagonalized = Functionals::diagonalize($couples);
$diagonalized(0);  // [0, 0]
$diagonalized(1);  // [1, 0]
$diagonalized(2);  // [0, 1]
$diagonalized(3);  // [2, 0]
...
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

### Piping
Piping is like composition, but arguments are given in the reverse order, like in a UNIX pipeline.
```php
$sum = function($a, $b) { return $a + $b; };
$half = function($n) { return $n/2; };

$middle = Functionals::compose($sum, $half);

echo $middle(10, 16); //Prints 13
echo $middle(-10, 10); //Prints 0
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

$boolDump(true);  // TRUE!
$boolDump(false); // FALSE!
```

### Currying and uncurrying

To get a [curried version](http://en.wikipedia.org/wiki/Currying) of a function in several variables, use
the `Functionals::curry` method:

```php
$sum = function($a, $b, $c) { return $a + $b + $c; };
$a = Functionals::curry($sum);
$b = $a(1);
$c = $b(2);

$c(10);  // 13
$c(101); // 104
```

You can also uncurry a function. This time you have to specify the number of the original function arguments:

```php
$uncurried = Functionals::uncurry($a, 3);

$uncurried(5, 7, 11);    //23
```

### Combine and uncombine

Given a set of functions `f, g, h, ...` that act on the same domain,
a combined version of that functions is the function

    x → array(f(x), g(x), h(x), ...)

In Functionals you can easily combine callables with `Functionals::combine`:

```php
$stringVersions = Functionals::combine('strtolower', 'strtoupper', 'ucfirst');

$stringVersions('hElLo'); // array('hello', 'HELLO', 'HElLo')
```

Conversely, you can uncombine a function that returns array values. In this case you have to
specify the number of items in the array values:

```php
$ops = function($a, $b) { return array($a + $b, $a * $b, $a - $b); };

list($sum, $multiplication, $difference) = Functionals::uncombine($ops, 3);

$sum(10, 5);            // 15
$multiplication(10, 5); // 50
$difference(10, 5);     // 5
```

### Args to array and array to args

You can convert a function that accept several arguments to a function that accept a single array
 argument through the functional `Functionals::args_to_array()`.

 For example, if you have the function in two variables `f(x, y)` with this functional you obtain
 the function (in pseudo-code)

     Functionals::args_to_array(f) : array(x, y) → f(x, y)

 In php:

 ```php
 $sum = function($a, $b) { return $a + $b; };
 $sum2 = Functionals::args_to_array($sum);

 $sum2(array(2, 10)); // 12
 ```

 This functional can be useful
 in conjunction with composition, since the functions in a composition chain that are
 not in the last position can recieve only one argument:

 ```php
 $sum = function() { return array_sum(func_get_args()); };
 $numbersUntil = function($n) {
     $numbers = array();
     for ($i = 0; $i <= $n; $i++)
         $numbers[] = $i;
     return $numbers;
 };

 $sumUntil = Functionals::compose(
     Functionals::args_to_array($sum),
     $numbersUntil
 );

 $sumUntil(1); // 1
 $sumUntil(5); // 15
 $sumUntil(100); // 5050 (=100 + 101 / 2)
 ```

The inverse of the previous functional is `Functionals::array_to_args()`:

 ```php
 $sum = function(array $numbers) { return array_sum($numbers); };

 $sum2 = Functionals::array_to_args($sum);

 $sum2(1, 2, 3); //6
 $sum2(10, 20, 3); //33
 ```

### Diagonalization
See the What's new section.

TODO
-----

* Some security checks on inputs and array sizes
* Performance considerations
* Give more useful/amusing examples?
* Automatically detect end of chain for uncurrying

Tests
-----

    $ phpunit

License
-------
MIT, see LICENSE.

