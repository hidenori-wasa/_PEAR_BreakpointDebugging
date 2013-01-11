<?php

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(false);

ini_set('include_path', './PEAR/;./;C:\xampp\php\PEAR\\');
$includePaths = $_BreakpointDebugging->displayVerification('ini_get', array ('include_path'));
$includePaths = $_BreakpointDebugging->displayVerification('explode', array (';', $includePaths));
foreach ($includePaths as $includePath) {
    $_BreakpointDebugging->displayVerification('realpath', array ("$includePath/PHPUnit/Framework/TestCase.php"));
}

?>
