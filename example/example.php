<?php
/**
 * This file is part of Functionals
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

use Functionals\FluentFunctionBuilder;

include "../vendor/autoload.php";

$b = new FluentFunctionBuilder();

$f = $b
    ->constant("ah")
    ->andThen($b->sprintf("This was the string: %s"))
    ->andThen("strtoupper")
    ->andThen($b->sprintf("Capitalized: <<<%s>>>"))
    ->compose($b->constant("asdadsasd"))
;

echo $f();