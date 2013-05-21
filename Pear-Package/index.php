<?php

require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(true); // Checks the execution mode.

B::assert(false);

?>
