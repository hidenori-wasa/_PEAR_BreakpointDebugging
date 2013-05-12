<?php

chdir(__DIR__ . '/../../../../');
require_once './BreakpointDebugging_Including.php';

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
        echo 'Initialization is OK.';
    }

}

new \Initialization();

?>
