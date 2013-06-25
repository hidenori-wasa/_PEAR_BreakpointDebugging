<?php

require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(true); // Checks the execution mode.

var_dump($GLOBALS);
// Stores a variable. We must not store by serialization because serialization cannot store resource and array element reference variable.
$globalsStoring = $GLOBALS;
var_dump($GLOBALS);
// Restores variable. We must not restore by reference copy because variable ID changes.
$GLOBALS = $globalsStoring;
var_dump($GLOBALS);

?>
