<?php
/**
 * This file is part of Functionals
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace Functionals;

/**
 * Class FunctionBuilder
 * @package Functionals
 */
class FunctionBuilder
{
    /**
     * @param $template
     * @return callable
     */
    public static function sprintf($template)
    {
        return function ($x) use ($template) {
            return sprintf($template, $x);
        };
    }

    /**
     * @param $x
     * @return callable
     */
    public static function constant($x)
    {
        return function () use ($x) {
            return $x;
        };
    }
} 