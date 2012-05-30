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
        $start = microtime(true);
        switch ('LockByFileExisting1') {
            case 'fopen':
                // Extend maximum execution time.
                set_time_limit(300);
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
            case 'LockByFileExisting1':
                $lockByFileExisting1 = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
                for ($count = 0; $count < 625; $count++) {
                    $lockByFileExisting1->lock();
                    $this->_incrementSheredMemory();
                    $lockByFileExisting1->unlock();
                }
                break;
            case 'LockByFileExisting2':
                $lockByFileExisting1 = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
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
                $lockByFileExisting1 = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
                $lockByFileExisting2 = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/SomethingDir2/FileForLockByFileExisting.txt');
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
                $lockByFileExisting1 = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
                $lockByFileExisting1->lock();
                $lockByFileExisting1->unlock();
                $lockByFileExisting1->unlock(); // Error.
                break;
            case 'LockByFileExisting5':
                $lockByFileExisting1 = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
                $lockByFileExisting1->lock(); // Error.
                break;
            case 'LockByFileExisting6':
                $lockByFileExisting1 = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/SomethingDir/FileForLockByFileExisting.txt');
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
