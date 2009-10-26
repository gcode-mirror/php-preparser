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

// Tests if _once really works as '_once':
require_once 'once/only.php';
require_once 'once/only.php';
include_once 'once/only.php';

echo 'Requiring _once OK.';