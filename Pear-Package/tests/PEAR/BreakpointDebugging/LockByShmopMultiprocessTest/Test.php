<?php

chdir(__DIR__ . '/../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

class Test
{
    private $shmopId;

    function __construct()
    {
        $this->shmopId = shmop_open(1234, 'w', 0, 0);
    }

    function __destruct()
    {
        shmop_close($this->shmopId);
    }

    private function _incrementSheredMemory()
    {
        $tmpCount = shmop_read($this->shmopId, 0, 10);
        $tmpCount++;
        // Sleep 0.0001 seconds.
        usleep(100);
        shmop_write($this->shmopId, sprintf('0x%08X', $tmpCount), 0);
    }

    function testLock()
    {
        // Extend maximum execution time.
        set_time_limit(300);
        $start = microtime(true);
        $lockByShmop1 = &\BreakpointDebugging_LockByShmop::singleton(60, 300, 1000);
        for ($count = 0; $count < 125; $count++) {
            $lockByShmop1->lock();
            $this->_incrementSheredMemory();
            $lockByShmop1->unlock();
        }
        var_dump(shmop_read($this->shmopId, 0, 10) + 0, microtime(true) - $start);
    }

}

$Test = new \Test();
$Test->testLock();

?>