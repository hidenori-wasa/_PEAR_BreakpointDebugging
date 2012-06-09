<?php

chdir(__DIR__ . '/../../../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

class ShmopTest1
{

    private $shmopId;

    function __construct()
    {
        $this->shmopId = shmop_open(1234, 'c', 0600, 10);
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
        switch ('shmop') {
            case 'shmop':
                $sharedMemoryID = shmop_open(333, 'w', 0, 0);
                for ($count = 0; $count < 5000; $count++) {
                    while (true) {
                        shmop_write($sharedMemoryID, '1', 0);
                        if (shmop_read($sharedMemoryID, 1, 1) === '1') {
                            shmop_write($sharedMemoryID, '0', 0);
                            continue;
                        }
                        $this->_incrementSheredMemory();
                        shmop_write($sharedMemoryID, '0', 0);
                        break;
                    }
                }
                shmop_close($sharedMemoryID);
                break;
            default:
                assert(false);
        }
        var_dump(shmop_read($this->shmopId, 0, 10) + 0, microtime(true) - $start);
    }

}

$ShmopTest1 = new ShmopTest1();
$ShmopTest1->testLock();
?>
