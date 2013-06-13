<?php
/*
 * This file is part of Functionals.
 *
 * (c) 2013 Nicolò Martini
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Functionals\Test;

/**
 * Unit tests for class Functionals
 *
 * @package    Functionals
 * @author     Nicolò Martini <nicmartnic@gmail.com>
 */
use Functionals\Functionals;

class FunctionalsTest extends \PHPUnit_Framework_TestCase
{
    public function testCompose()
    {
        $add = function ($a, $b) { return $a + $b; };
        $opposite = function($n) { return -$n; };
        $double = function($n) { return 2 * $n; };

        $composition = Functionals::compose($opposite, $double, $add);

        $this->assertEquals(-10, $composition(2, 3));
        $this->assertEquals(-30, $composition(5, 10));
        $this->assertEquals(8, $composition(-5, 1));
    }

    public function testPipe()
    {
        $add = function ($a, $b) { return $a + $b; };
        $opposite = function($n) { return -$n; };
        $double = function($n) { return 2 * $n; };

        $piped = Functionals::pipe($add, $double, $opposite);

        $this->assertEquals(-10, $piped(2, 3));
        $this->assertEquals(-30, $piped(5, 10));
        $this->assertEquals(8, $piped(-5, 1));
    }

    public function testComposeWithOneArgument()
    {
        $add = function ($a, $b) { return $a + $b; };

        $composition = Functionals::compose($add);

        $this->assertEquals(5, $composition(2, 3));
        $this->assertEquals(15, $composition(5, 10));
        $this->assertEquals(-4, $composition(-5, 1));
    }

    public function testArgsToArray()
    {
        $add = function ($a, $b) { return $a + $b; };

        $add2 = Functionals::args_to_array($add);

        $this->assertEquals($add(1,2), $add2(array(1, 2)));
        $this->assertEquals($add(3,24), $add2(array(3, 24)));
    }

    public function testArrayToArgs()
    {
        $add = function ($ary) { return $ary[0] + $ary[1]; };

        $add2 = Functionals::array_to_args($add);

        $this->assertEquals($add(array(1, 2)), $add2(1,2));
        $this->assertEquals($add(array(3, 24)), $add2(3,24));
    }

    public function testPartial()
    {
        $concat = function() { return implode(' ', func_get_args()); };

        $partial = Functionals::partial($concat, array(0 => '1)', 2 => '2)'));

        $this->assertEquals('1) first 2) second and third', $partial('first', 'second', 'and third'));
    }

    public function testCombine()
    {
        $identity = function($value) { return $value; };
        $double = function($value) { return 2 * $value; };
        $quadruple = function ($value) { return 4 * $value; };

        $combined = Functionals::combine($identity, $double, $quadruple);

        $this->assertEquals(array(1,2,4), $combined(1));
        $this->assertEquals(array(2,4,8), $combined(2));
        $this->assertEquals(array(10,20,40), $combined(10));
        $this->assertEquals(array(-3,-6,-12), $combined(-3));
    }

    public function testUncombine()
    {
        $f = function($a, $b) { return array($a + $b, $a - $b, $a * $b); };

        list($add, $minus, $times, $null) = Functionals::uncombine($f, 4);

        $this->assertEquals(10, $add(7, 3));
        $this->assertEquals(3, $add(1, 2));
        $this->assertEquals(10, $minus(15, 5));
        $this->assertEquals(3, $minus(6, 3));
        $this->assertEquals(18, $times(6, 3));
        $this->assertEquals(48, $times(6, 8));

        $this->assertNull($null(14, 52));
        $this->assertNull($null(0, 0));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testUncombineWhenOriginalFunctionDoesNotReturnsArray()
    {
        $f = function() { return 2; };

        $uncombined = Functionals::uncombine($f, 2);

        $g = $uncombined[0];
        $g('value');
    }

    public function testCurry()
    {
        $add = function ($a, $b, $c) { return $a + $b + $c; };

        $curried = Functionals::curry($add);

        $uncurried = function($a, $b, $c) use ($curried) {
            $func1 = $curried($a);
            $func2 = $func1($b);

            return $func2($c);
        };

        $this->assertEquals($add(1,2,3), $uncurried(1,2,3));
        $this->assertEquals($add(1,11,3), $uncurried(1,11,3));
        $this->assertEquals($add(28,2,99), $uncurried(28,2,99));
    }

    public function testUncurry()
    {
        $add = function($a, $b, $c) { return $a + $b + $c; };
        $add2 = Functionals::uncurry(Functionals::curry($add, 3), 3);

        $this->assertEquals($add(1,2,3), $add2(1,2,3));
        $this->assertEquals($add(11,21,3), $add2(11,21,3));
        $this->assertEquals($add(80,3,40), $add2(80,3,40));
    }

    public function testDiagonalize()
    {
        $f = function($x, $y) { return [$x, $y]; };

        $g = Functionals::diagonalize($f);

        $this->assertEquals([0, 0] , $g(0));
        $this->assertEquals([1, 0] , $g(1));
        $this->assertEquals([0, 1] , $g(2));
        $this->assertEquals([2, 0] , $g(3));
        $this->assertEquals([1, 1] , $g(4));
        $this->assertEquals([0, 2] , $g(5));
        $this->assertEquals([3, 0] , $g(6));
        $this->assertEquals([2, 1] , $g(7));
        $this->assertEquals([1, 2] , $g(8));
        $this->assertEquals([0, 3] , $g(9));

    }
}