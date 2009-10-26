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

$count = 0;
echo '<ol>';

// Tests require replacing
require 'mock.php';
include 'mock.php';
require('mock.php');
include('mock.php');
include  ('mock.php');
include
    'mock.php';
include
    'mock.php'    
    ;
echo '<li>Includes in included files OK.</li>';
$count++;



// Other tests
echo '<li>';
require 'once.php';
echo '</li>';
$count++;

echo '<li>';
require 'dereferencing.php';
echo '</li>';
$count++;

echo '</ol>';
echo 'All ' . $count . ' of tests have been runned.';