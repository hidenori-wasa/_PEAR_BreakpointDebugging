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
        $lockByMkdir1 = new \BreakpointDebugging_LockByMkdir('./SomethingDir/FileWhichWantsToLock.txt');
        $lockByMkdir2 = new \BreakpointDebugging_LockByMkdir('./SomethingDir/FileWhichWantsToLock2.txt');
        $lockByMkdir3 = new \BreakpointDebugging_LockByMkdir('./SomethingDir3/FileWhichWantsToLock.txt');

        for ($count = 0; $count < 100; $count++) {
            $lockByMkdir2->lock();
            $this->_incrementSheredMemory();
            $this->_incrementSheredMemory();
            $lockByMkdir1->lock();
            $this->_incrementSheredMemory();
            $lockByMkdir1->unlock();
            $this->_incrementSheredMemory();
            $lockByMkdir3->lock();
            $this->_incrementSheredMemory();
            $lockByMkdir1->lock();
            $this->_incrementSheredMemory();
            $lockByMkdir1->unlock();
            $this->_incrementSheredMemory();
            $lockByMkdir2->unlock();
            $this->_incrementSheredMemory();
            $lockByMkdir1->lock();
            $this->_incrementSheredMemory();
            $lockByMkdir1->unlock();
            $this->_incrementSheredMemory();
            $lockByMkdir3->unlock();
        }
        echo hexdec(shmop_read($this->shmopId, 0, 10));
    }

}

$Test = new Test();
$Test->testLock();
?>
