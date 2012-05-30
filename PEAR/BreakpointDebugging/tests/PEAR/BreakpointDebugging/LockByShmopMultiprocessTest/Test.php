<?php

chdir(__DIR__ . '/../../../../../../');
require_once './BreakpointDebugging_MySetting.php';

class Test
{

    private $shmopId;

    function __construct()
    {
        $this->shmopId = shmop_open(1234, 'w', 0600, 10);
    }

    function __destruct()
    {
        shmop_close($this->shmopId);
    }

    private function _incrementSheredMemory()
    {
        $tmpCount = shmop_read($this->shmopId, 0, 10);
        $tmpCount++;
        // Wait 0.01 seconds.
        usleep(10000);
        shmop_write($this->shmopId, sprintf('0x%08X', $tmpCount), 0);
    }

    function testLock()
    {
        // Extend maximum execution time.
        set_time_limit(300);
        $start = microtime(true);
        switch ('BreakpointDebugging_LockByFileExisting1') {
            case 'BreakpointDebugging_LockByFileExisting1':
                $lockByFileExisting1 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
                for ($count = 0; $count < 625; $count++) {
                    $lockByFileExisting1->lock();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting1->unlock();
                }
                break;
            case 'fopen':
                restore_error_handler();
                @unlink(__DIR__ . '/LockFlag.file');
                for ($count = 0; $count < 625; $count++) {
                    while (!($pFile = @fopen(__DIR__ . '/LockFlag.file', 'x+b')));
                    $this->_incrementSheredMemory();
                    fclose($pFile);
                    while (!@unlink(__DIR__ . '/LockFlag.file'));
                }
                set_error_handler('BreakpointDebugging::errorHandler', -1);
                break;
            case 'flock':
                $pFile = fopen(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt', 'r+b');
                for ($count = 0; $count < 625; $count++) {
                    flock($pFile, LOCK_EX);
                    $this->_incrementSheredMemory();
                    flock($pFile, LOCK_UN);
                }
                break;
            case 'BreakpointDebugging_LockByShmop1':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
                for ($count = 0; $count < 625; $count++) {
                    $lockByShmop1->lock();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                }
                break;
            case 'BreakpointDebugging_LockByShmop2':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
                $lockByShmop2 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir2/FileForLockByFileExisting.txt'); // Error.
                for ($count = 0; $count < 625; $count++) {
                    $lockByShmop1->lock();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->lock(); // Error.
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                }
                break;
            default:
                assert(false);
        }
        var_dump(shmop_read($this->shmopId, 0, 10) + 0, microtime(true) - $start);
    }

}

$Test = new Test();
$Test->testLock();
?>
