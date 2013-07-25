<?php

chdir(str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2));
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;

class Initialization
{
    function __construct()
    {
        B::assert(extension_loaded('shmop'), 101);
        // Allocate shared memory area.
        $shmopId = shmop_open(1234, 'c', 0600, 10);
        // Initialize shared memory.
        shmop_write($shmopId, '0x00000000', 0);
        shmop_close($shmopId);
        // Unlinks internal synchronization file.
        $internalLockFilePath = B::getStatic('$_workDir') . '/LockByFileExistingOfInternal.txt';
        if (is_file($internalLockFilePath)) {
            B::unlink(array ($internalLockFilePath));
        }
        // Unlinks synchronization file.
        $lockFileName = B::getStatic('$_workDir') . '/LockByFlock.txt';
        if (is_file($lockFileName)) {
            B::unlink(array ($lockFileName));
        }

        echo '<pre>Initialization is OK.' . PHP_EOL;
        echo 'Wait about 10 second until hard disk access stops.' . PHP_EOL;
        echo 'Then, close this window.' . PHP_EOL;
        echo 'Then, point location which tool tip does not display with mouse until the result is displayed.</pre>';
    }

}

new \Initialization();

?>
