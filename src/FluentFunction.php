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


class FluentFunction
{
    private $callable;

    /**
     * @param $callable
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
    }

    public function andThen($func)
    {
        if ($func instanceof self) {
            $func = $func->callable;
        }

        return new self(Functionals::pipe($this->callable, $func));
    }

    public function compose($func)
    {
        $fluentFunc = new FluentFunction($func);
        return $fluentFunc->andThen($this);
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->callable, func_get_args());
    }
}