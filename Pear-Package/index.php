<?php

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

$command = new PHPUnit_TextUI_Command;
echo '<pre>';
echo $command->run(
array ('phpunit_of_dummy_command', 'C:\xampp\htdocs\Pear-Package\tests\PEAR/BreakpointDebuggingTest.php')
, true);
echo '</pre>';

?>
