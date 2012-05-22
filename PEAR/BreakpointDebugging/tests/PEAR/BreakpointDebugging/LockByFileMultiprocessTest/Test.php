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
        shmop_write($this->shmopId, sprintf('0x%08X', $tmpCount), 0);
    }

    function testLock()
    {
        $lockByFile1 = new \BreakpointDebugging_LockByFile(__DIR__ . '/SomethingDir/FileWhichWantsToLock.txt');
        $lockByFile2 = new \BreakpointDebugging_LockByFile(__DIR__ . '/SomethingDir/FileWhichWantsToLock2.txt');
        $lockByFile3 = new \BreakpointDebugging_LockByFile(__DIR__ . '/SomethingDir3/FileWhichWantsToLock.txt');

        switch ('BreakpointDebugging_LockByFile') {
            case 'flock':
                for ($count = 0; $count < 1000; $count++) {
                    flock($lockByFile1->_pUniqueId, LOCK_EX); // For debug.
                    $this->_incrementSheredMemory();
                    flock($lockByFile1->_pUniqueId, LOCK_UN); // For debug.
                }
                break;
            case 'BreakpointDebugging_LockByFile':
                for ($count = 0; $count < 1000; $count++) {
                    $lockByFile1->lock();
                    $this->_incrementSheredMemory();
                    $lockByFile1->unlock();
                }
                break;
            case 'BreakpointDebugging_LockByFile2':
                for ($count = 0; $count < 100; $count++) {
                    $lockByFile2->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByFile1->lock();
                    $this->_incrementSheredMemory();
                    $lockByFile1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByFile3->lock();
                    $this->_incrementSheredMemory();
                    $lockByFile1->lock();
                    $this->_incrementSheredMemory();
                    $lockByFile1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByFile2->unlock();
                    $this->_incrementSheredMemory();
                    $lockByFile1->lock();
                    $this->_incrementSheredMemory();
                    $lockByFile1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByFile3->unlock();
                }
                break;
            default:
                assert(false);
        }
        echo hexdec(shmop_read($this->shmopId, 0, 10));
    }

}

$Test = new Test();
$Test->testLock();
?>
