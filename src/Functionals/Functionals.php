<?php
/*
 * This file is part of Functionals.
 *
 * (c) 2013 Nicolò Martini
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Functionals;

/**
 * This class is a collection of common operations of functions, like compositions, partials and curring
 *
 * @package    Functionals
 * @author     Nicolò Martini <nicmartnic@gmail.com>
 */
class Functionals
{
    /**
     * Convert a function to another one that accepts only one array argument.
     * In this way, if $g = Functionals::args_to_array($f),
     * then $g(array($a,$b)) = $f($a, $b) for each $a, $b.
     *
     * @param callable $function
     * @return callable
     */
    public static function args_to_array($function)
    {
        return function(array $argumentsArray = array()) use ($function) {
            return call_user_func_array($function, $argumentsArray);
        };
    }

    /**
     * The inverse of @see Functionals::array_to_args
     *
     * @param callable $function
     * @return callable
     */
    public static function array_to_args($function)
    {
        return function() use($function) {
            return call_user_func($function, func_get_args());
        };
    }

    /**
     * Get a partial version of a function, i.e. fix some arguments and returns a function
     * of the remaining arguments.
     *
     * @param callable $function
     * @param array $fixedArgs The array of fixed args. The keys represent the arguments positions
     *                         in the main function arguments list
     * @return callable
     */
    public static function partial($function, array $fixedArgs = array())
    {
        return function() use($function, $fixedArgs) {
            $partialArgs = func_get_args();
            $fullArgs = array();
            $totalArgs = count($partialArgs) + count($fixedArgs);

            for ($i = 0; $i < $totalArgs; $i++) {
                $fullArgs[] = isset($fixedArgs[$i]) ? $fixedArgs[$i] : array_shift($partialArgs);
            }

            return call_user_func_array($function, $fullArgs);
        };
    }

    /**
     * Combine an arbitrary long list of functions into a single function that returns
     * an array in which the n-th element is the result of the n-th function.
     *
     * @param callable $func,... An arbitrary long list of functions
     *
     * @return callable
     */
    public static function combine(/* $func1, $func2, ... */)
    {
        $functions = func_get_args();

        foreach ($functions as $function) {
            static::validate($function);
        }

        return function() use ($functions) {
            $result = array();
            $args = func_get_args();

            foreach ($functions as $function) {
                $result[] = call_user_func_array($function, $args);
            }

            return $result;
        };
    }

    /**
     * The inverse of @see Functionals::combine. Here we have to specify the array length of the
     * original function results. If the function returns an array of length < $arrayLength,
     * then the generated functions at positions >= length will return a null value.
     *
     * @param callable $function A functions that returns arrays
     * @param int $arrayLength The length of the arrays returned by $function
     * @return array An array of callables
     * @throws \UnexpectedValueException
     */
    public static function uncombine($function, $arrayLength)
    {
        $functions = array();

        for ($i = 0; $i < $arrayLength; $i++) {
            $functions[] = function() use($function, $i) {
                $fullResult = call_user_func_array($function, func_get_args());

                if (!is_array($fullResult))
                    throw new \UnexpectedValueException('Functionals::uncombine requires that original function returns always an array.');

                return isset($fullResult[$i]) ? $fullResult[$i] : null;
            };
        }

        return $functions;
    }

    /**
     * Returns the composition of a list of functions.
     *
     * @param callable $function The leftmost function of the composition chain
     * @param callable $function,... An unlimited list of callables to compose
     *
     * @return callable
     */
    public static function compose(/* $function1, $function2, ... */)
    {
        $functions = func_get_args();

        if (count($functions) === 1) {
            static::validate($functions[0]);
            return $functions[0];
        }

        $firstFunction = array_shift($functions);

        static::validate($firstFunction);

        return function() use($firstFunction, $functions) {

            $partial = call_user_func_array(array('\Functionals\Functionals', 'compose'), $functions);

            return call_user_func($firstFunction, call_user_func_array($partial, func_get_args()));
        };
    }

    /**
     * Get a curried version of the function (@link http://en.wikipedia.org/wiki/Currying)
     * If $argsArgument is null, it tries to find the number of arguments through reflection. This of course
     * does not give the expected results when the function use undeclared arguments through func_get_args()
     *
     * @param callable $function
     * @param null $argsNumber
     * @return callable
     */
    public static function curry($function, $argsNumber = null)
    {
        if (is_null($argsNumber)) {
            $refFunction = new \ReflectionFunction($function);
            $argsNumber = $refFunction->getNumberOfParameters();
        }

        if ($argsNumber <= 1)
            return $function;

        return function($x) use ($function, $argsNumber) {
            return Functionals::curry(Functionals::partial($function, array($x)), $argsNumber - 1);
        };
    }

    /**
     * The inverse of @see Functionals::curry
     *
     * @param callable $curriedFunction
     * @param int $argsNumber
     * @return callable
     */
    public static function uncurry($curriedFunction, $argsNumber)
    {
        if ($argsNumber <= 1)
            return $curriedFunction;

        return function() use ($curriedFunction, $argsNumber) {
            $args = func_get_args();
            $x = array_shift($args);
            $f = call_user_func($curriedFunction, $x);

            return call_user_func_array(Functionals::uncurry($f, $argsNumber - 1), $args);
        };
    }

    private static function validate($function)
    {
        if (!is_callable($function))
            throw new \InvalidArgumentException('All functions managed by Functionals must be callable objects');
    }
}