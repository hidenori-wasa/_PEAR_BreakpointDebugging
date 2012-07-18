<?php

chdir(__DIR__ . '/../../../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

class Initialization
{

//    private function _throwException($message)
//    {
//        throw new Exception($message);
//    }

    function __construct()
    {
//        if (!extension_loaded('shmop')) {
//            $this->_throwException('"Shmop" extension has been not loaded.');
//        }
        assert(extension_loaded('shmop'));
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

new Initialization();
?>
