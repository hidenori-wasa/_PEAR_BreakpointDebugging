<?php

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(false);

$_BreakpointDebugging->displayVerification('ini_get', array('include_path'));
$_BreakpointDebugging->displayVerification('realpath', array('C:\xampp/htdocs\Pear-Package\index.php'));

?>
