<?php

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

//require_once './PEAR/BreakpointDebugging/tests/PEAR/BreakpointDebugging/lockByShmopTest.php';
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

require_once './PEAR/BreakpointDebugging/tests/PEAR/BreakpointDebugging/LockByFileExistingMultiprocessTest/Initialization.php';
require_once './PEAR/BreakpointDebugging/tests/PEAR/BreakpointDebugging/LockByFileExistingMultiprocessTest/Test.php';
exit;


require_once './PEAR/BreakpointDebugging/tests/PEAR/BreakpointDebugging/LockByShmopMultiprocessTest/Initialization.php';
require_once './PEAR/BreakpointDebugging/tests/PEAR/BreakpointDebugging/LockByShmopMultiprocessTest/Test.php';
exit;


assert(false);
exit;


$uniqueIdOfMultiprocess = uniqid('', true);
$pFile = fopen('./test.txt', 'w+b');
chmod('./test.txt', 0600);

$startTime = microtime(true);
for ($count = 0; $count < 100; $count++) {
    rewind($pFile);
    fwrite($pFile, $uniqueIdOfMultiprocess);
    fflush($pFile);

    rewind($pFile);
    $uniqueIdOfMultiprocess = fread($pFile, 2048);
}
var_dump(microtime(true) - $startTime); // 0.0055360794067383 0.0043759346008301 0.0054390430450439
fclose($pFile);
exit;


$uniqueIdOfMultiprocess = uniqid('', true);
$startTime = microtime(true);
for ($count = 0; $count < 100; $count++) {
    file_put_contents('./test.txt', $uniqueIdOfMultiprocess);
    chmod('./test.txt', 0600);
    $uniqueIdOfMultiprocess = file_get_contents('./test.txt');
}
var_dump(microtime(true) - $startTime); // 0.44909811019897 0.45199489593506 0.35120701789856
exit;


restore_error_handler();
$pFile = fopen('./Test.txt', 'w+b');
chmod('./Test.txt', 0600);
stream_set_write_buffer($pFile, 0);
$start = microtime(true);
for ($count = 0; $count < 100000; $count++) {
    fseek($pFile, 0);
    fwrite($pFile, sprintf('0x%08X', $count));
    fseek($pFile, 0);
    $return = hexdec(fread($pFile, 10));
    //$return = (int) fread($pFile, 10);
    assert($return === $count);
}
echo microtime(true) - $start;
// fwrite       3.1231918334961 2.8318340778351
// fread        2.1043789386749 2.0163748264313
// @mkdir       9.4771120548248 9.2996070384979
// shmop_write  0.5433170795440 0.52619481086731
// shmop_read   0.5166440010070 0.55775690078735

fclose($pFile);
set_error_handler('BreakpointDebugging::errorHandler', -1);
exit;
?>
