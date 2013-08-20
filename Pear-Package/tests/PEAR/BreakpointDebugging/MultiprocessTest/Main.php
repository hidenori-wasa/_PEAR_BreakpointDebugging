<?php

use \BreakpointDebugging as B;

class Tests_PEAR_BreakpointDebugging_MultiprocessTest_Main
{
    private function _initializeCounter($shmopKey)
    {
        B::assert(extension_loaded('shmop'), 101);
        // Allocate shared memory area.
        $shmopId = shmop_open($shmopKey, 'c', 0600, 10);

        if ($shmopId === false) {
            B::exitForError('Failed "shmop_open()".');
        }
        // Initialize shared memory.
        $result = shmop_write($shmopId, '0x00000000', 0);
        if ($result === false) {
            B::exitForError('Failed "shmop_write()".');
        }
        shmop_close($shmopId);
    }

    function test($shmopKey, $className)
    {
        $this->_initializeCounter($shmopKey);

        $pHandles = array ();
        for ($count = 0; $count < 8; $count++) {
            // Creates and runs a test process.
            $pHandles[] = popen('php ./tests/PEAR/BreakpointDebugging/MultiprocessTest/Lock.php ' . $shmopKey . ' ' . $className, 'r');
        }

        $results = array ();
        foreach ($pHandles as $pHandle) {
            while (!feof($pHandle)) {
                // Gets a result.
                $results[] = fgets($pHandle);
            }
        }

        foreach ($pHandles as $pHandle) {
            // Deletes a test process.
            pclose($pHandle);
        }

        if (max($results) !== '1000') {
            // Displays error.
            foreach ($results as $result) {
                echo $result;
            }
            return false;
        }
        return true;
    }

}

?>
