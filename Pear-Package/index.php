<?php

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

$return = B::displayVerification('fopen', array ('./Work/test.bin', 'rb'));
var_dump($return);

?>
