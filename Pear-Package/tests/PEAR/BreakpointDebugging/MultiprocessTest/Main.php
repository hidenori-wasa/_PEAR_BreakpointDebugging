<?php

use \BreakpointDebugging as B;

class tests_PEAR_BreakpointDebugging_MultiprocessTest_Main
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

        $fullFilePath = __DIR__ . '/Lock.php';
        $pPipes = array ();
        for ($count = 0; $count < 8; $count++) {
            // Creates and runs a test process.
            if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // For Windows.
                $pPipe = popen('php.exe -f ' . $fullFilePath . ' -- ' . $shmopKey . ' ' . $className, 'r');
                if ($pPipe === false) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
                }
            } else { // For Unix.
                // Searches "php" command path because "apache" may be super user.
                $whereIsPhp = `whereis php`;
                $whichPhp = array ();
                preg_match('`/.*$`xX', $whereIsPhp, $whichPhp);
                // "&" is the background execution of command.
                $pPipe = popen($whichPhp[0] . ' -f ' . $fullFilePath . ' -- ' . $shmopKey . ' ' . $className . ' &', 'r');
                if ($pPipe === false) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
                }
                // Executes command to asynchronization.
                if (!stream_set_blocking($pPipe, 0)) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "stream_set_blocking($pPipe, 0)".');
                }
            }
            $pPipes[] = $pPipe;
        }

        $results = array ();
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            foreach ($pPipes as $pPipe) {
                $results[] = stream_get_contents($pPipe);
            }
        } else {
            foreach ($pPipes as $pPipe) {
                // Waits until command execution end.
                if (!stream_set_blocking($pPipe, 1)) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "stream_set_blocking($pPipe, 1)".');
                }
                // Gets command's stdout.
                $results[] = stream_get_contents($pPipe);
            }
        }

        foreach ($pPipes as $pPipe) {
            // Deletes a test process.
            pclose($pPipe);
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
