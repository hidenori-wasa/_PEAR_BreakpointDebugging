<?php

require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(false); // Checks the execution mode.

$a = false;
$b = true;

if ($a === false
    && $b === true
) {
    $c = 1;
}

echo $c;
?>
