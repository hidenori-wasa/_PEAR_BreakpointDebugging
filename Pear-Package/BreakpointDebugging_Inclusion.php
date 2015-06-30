<?php

/**
 * Sets pear setting directory name, and includes this package by it.
 *
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE:
 * Copyright (c) 2012-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
define('BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME', './BreakpointDebugging_PEAR_Setting/'); // We can change "PEAR" setting directory name.
define('BREAKPOINTDEBUGGING_WORK_DIR_NAME', './BreakpointDebugging_Work/'); // We can change "BreakpointDebugging" package work directory name.

require_once BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';

\BreakpointDebugging::assert(preg_match('`^ \./ .+ / $`xX', BREAKPOINTDEBUGGING_WORK_DIR_NAME) === 1);
