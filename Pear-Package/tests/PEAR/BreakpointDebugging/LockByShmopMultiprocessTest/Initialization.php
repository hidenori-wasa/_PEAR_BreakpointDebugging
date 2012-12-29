<?php

chdir(__DIR__ . '/../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

class Initialization
{
    function __construct()
    {
        B::assert(extension_loaded('shmop'), 1);
        // Allocate shared memory area.
        $shmopId = shmop_open(1234, 'c', 0600, 10);
        // Initialize shared memory.
        shmop_write($shmopId, '0x00000000', 0);
        shmop_close($shmopId);

        $sharedMemoryID = shmop_open(333, 'c', 0600, 2);
        shmop_write($sharedMemoryID, '00', 0);
        shmop_close($sharedMemoryID);

        echo 'Initialization is OK.';
    }

}

new \Initialization();

?>
