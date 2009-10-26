<?php
/**
 * Preparser
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package    Preparser
 * @subpackage Tests
 * @copyright  Copyright (c) 2009 Valentin Golev (http://va1en0k.net/)
 * @license    New BSD License
 * @version    $Id$
 */
 
function ret_arr() {
    return array(1, 2, 3);
}
function ret_anything($lol) {
    return $lol;
}

$arr = ret_arr();
 
assert( ret_arr()[2] == $arr[2] );

assert( array('a' => 1, 'b' => 2)['a'] == 1 );

// Fuck da parser!

assert( ret_anything(array(1, 2, 3))[1] == 2 );
assert( array(1, 2, 3)[ ret_anything(2) ] == 3 );
assert( array(1, 2, array(1, 2, 3)[1])[1] == 2 );
assert( ret_anything(   array(1, 2, ret_anything(array(1, 2, 3))[2] ) )[1] == 2 );
assert( array(1, 2, 3)[ret_anything(array(1, 2, 3)[0])] == 2 );
assert( ret_anything( array(1,   2, ret_anything(4), ret_anything(array(1, 2, 3))[1])[ ret_anything(array(1, 2, 3))[ret_anything(array(1, 2, 3))[1]] ]) == 2 );

// vars

$var1 = 1;
$var2 = array(1, 2, 3);

assert( ret_anything($var2)[$var1] == 2 );
assert( ret_anything($var2)[ret_anything($var2) [ ret_anything($var1)]] == 3 );


// methods
class Lol {
    public $lol;
    public $ar = array(1, 2, 3);
    public static $sar = array(1, 2, 3);
    function get_lol() {
        return $this->lol;
    }
    function get_ar() { 
        return array(0, 1, 2);
    }
    static function get_sar() { 
        return array(0, 1, 2);
    }
}
$lol = new Lol;
$lol->lol = new Lol;

assert( $lol->get_ar()[1] == 1 );
assert( $lol->lol->get_ar()[1] == 1 );
assert( $lol->ar[1] == 2 );
assert( Lol::$sar[1] == 2 );
assert( Lol::get_sar()[1] == 1 );
assert( $lol->get_lol()->get_ar()[1] == 1 );
assert( $lol->get_lol()->get_ar()[ $lol->get_lol()->get_ar()[ $lol->get_lol()->get_ar()[ $lol->get_lol()->get_ar()[ 1 ] ] ] ] == 1 ); 

// namespaces?

require_once 'dereferencing\ns.php';

assert( Ololo\Olol::get_sar()[1] == 1 );
assert( Ololo\oret_arr()[2] == $arr[2] );

echo 'Array dereferencing testing is done';

