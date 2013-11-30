<?php

use \BreakpointDebugging as B;

class tests_PEAR_BreakpointDebugging_MultiprocessTest_Main
{
    private function _initializeCounter($shmopKey)
    {
        if (!extension_loaded('shmop')) {
            \PHPUnit_Framework_Assert::markTestSkipped();
        }
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

        if (array_key_exists('QUERY_STRING', $_SERVER)) {
            $modes = explode('&', $_SERVER['QUERY_STRING']);
        } else if (array_key_exists('argv', $_SERVER)
            && array_key_exists(0, $_SERVER['argv'])
        ) {
            $modes = explode('&', $_SERVER['argv'][0]);
        }
        foreach ($modes as $mode) {
            list($key, $value) = explode('=', $mode);
            if ($key === 'BREAKPOINTDEBUGGING_MODE') {
                break;
            }
        }

        for ($count = 0; $count < 2; $count++) {
            // Creates and runs a test process.
            if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // For Windows.
                $pPipe = popen('php.exe -f ' . $fullFilePath . ' -- ' . $mode . ' SHMOP_KEY=' . $shmopKey . ' CLASS_NAME=' . $className, 'r');
                if ($pPipe === false) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
                }
            } else { // For Unix.
                // "&" is the background execution of command.
                $pPipe = popen('php -f ' . $fullFilePath . ' -- ' . $mode . ' SHMOP_KEY=' . $shmopKey . ' CLASS_NAME=' . $className . ' &', 'r');
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

        if (max($results) !== '250') {
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
