<?php

chdir(__DIR__ . '/../../../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

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
        $start = microtime(true);
        switch ('LockByFileExisting1') {
            case 'fopen':
                // Extend maximum execution time.
                set_time_limit(300);
                restore_error_handler();
                @unlink(BreakpointDebugging::$workDir . '/LockFlag.file');
                for ($count = 0; $count < 625; $count++) {
                    while (!($pFile = @fopen(BreakpointDebugging::$workDir . '/LockFlag.file', 'x+b')));
                    chmod(BreakpointDebugging::$workDir . '/LockFlag.file', 0600);
                    $this->_incrementSheredMemory();
                    fclose($pFile);
                    while (!@unlink(BreakpointDebugging::$workDir . '/LockFlag.file'));
                }
                set_error_handler('BreakpointDebugging::errorHandler', -1);
                break;
            case 'LockByFileExisting1':
                $lockByFileExisting1 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt', 60, 300, 10000);
                for ($count = 0; $count < 625; $count++) {
                    $lockByFileExisting1->lock();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting1->unlock();
                }
                break;
            case 'LockByFileExisting2':
                $lockByFileExisting1 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt', 5, 10, 10000);
                for ($count = 0; $count < 125; $count++) {
                    $lockByFileExisting1->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting1->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting1->unlock();
                }
                break;
            case 'LockByFileExisting3':
                $lockByFileExisting1 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt', 5, 10, 10000);
                $lockByFileExisting2 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir2/FileForLockByFileExisting.txt', 5, 10, 10000);
                for ($count = 0; $count < 125; $count++) {
                    $lockByFileExisting1->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting2->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting2->unlock();
                }
                break;
            case 'LockByFileExisting4':
                $lockByFileExisting1 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt', 5, 10);
                $lockByFileExisting1->lock();
                $lockByFileExisting1->unlock();
                $lockByFileExisting1->unlock(); // Error.
                break;
            case 'LockByFileExisting5':
                $lockByFileExisting1 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt', 5, 10);
                $lockByFileExisting1->lock(); // Error.
                break;
            case 'LockByFileExisting6':
                $lockByFileExisting1 = \BreakpointDebugging_LockByFileExisting::singleton(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt', 5, 10);
                $lockByFileExisting1->unlock(); // Error.
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
