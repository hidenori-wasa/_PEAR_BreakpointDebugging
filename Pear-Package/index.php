<?php

require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(false); // Checks the execution mode.

    const TEST_CONST = 10;

var_dump(defined('TEST_CONST'));

?>
