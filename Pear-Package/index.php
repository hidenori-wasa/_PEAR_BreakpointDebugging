<?php

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(false);
class NativeClass extends \BreakpointDebugging_OverrideClass
{

}

?>
