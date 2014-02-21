<?php

require_once __DIR__ . '/Counter.php';

use \BreakpointDebugging as B;

class Lock extends \Counter
{
    function testLock($className)
    {
        // Extend maximum execution time.
        set_time_limit(300);

        $pLock = &$className::singleton(60, 1000);
        for ($count = 0; $count < 125; $count++) {
            $pLock->lock();
            $this->incrementSheredMemory();
            $pLock->unlock();
        }

        $result = shmop_read($this->shmopId, 0, 10);
        if ($result === false) {
            B::exitForError('Failed "shmop_read()".');
        }

        echo $result + 0;
    }

}

$get = B::getStatic('$_get');
$pLock = new \Lock($get['SHMOP_KEY']);
$pLock->testLock($get['CLASS_NAME']);

?>
