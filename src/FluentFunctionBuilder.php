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
 * Class FluentFunctionBuilder
 * @package Functionals
 *
 * @method FluentFunction sprintf(string $template)
 * @method FluentFunction constant(mixed $value)
 */
class FluentFunctionBuilder
{
    /**
     * @var FunctionBuilder
     */
    private $functionBuilder;

    /**
     * @param FunctionBuilder $functionBuilder
     */
    public function __construct(FunctionBuilder $functionBuilder = null)
    {
        $this->functionBuilder = $functionBuilder ?: new FunctionBuilder();
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->functionBuilder, $name)) {
            throw new \InvalidArgumentException("Method $name is not defined in the Function Builder");
        }

        return new FluentFunction(call_user_func_array(
            array($this->functionBuilder, $name),
            $arguments
        ));
    }
}