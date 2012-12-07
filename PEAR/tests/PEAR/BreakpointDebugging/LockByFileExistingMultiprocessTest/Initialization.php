<?php

chdir(__DIR__ . '/../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

class Initialization
{
    function __construct()
    {
        if(!extension_loaded('shmop')){
            exit('"shmop" extension has been not loaded.');
        }
        // Allocate shared memory area.
        $shmopId = shmop_open(1234, 'c', 0600, 10);
        // Initialize shared memory.
        shmop_write($shmopId, '0x00000000', 0);
        shmop_close($shmopId);
        echo 'Initialization is OK.';
    }

}

new \Initialization();

?>
