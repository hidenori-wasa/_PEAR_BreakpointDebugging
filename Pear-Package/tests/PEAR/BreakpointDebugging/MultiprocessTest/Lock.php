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

// var_dump($_SERVER['argv']); exit; // For debug.

foreach ($_SERVER['argv'] as $mode) {
    $mode = explode('=', $mode);
    // var_dump($mode); exit; // For debug.
    if (empty($mode)) {
        contiue;
    }
    list($key, $value) = $mode;
    switch ($key) {
        case 'SHMOP_KEY':
            $shmopKey = $value;
            break;
        case 'CLASS_NAME':
            $className = $value;
            break;
    }
}

// var_dump($shmopKey, $className); exit; // For debug.
//$pLock = new \Lock($_SERVER['argv'][1]);
$pLock = new \Lock($shmopKey);
//$pLock->testLock($_SERVER['argv'][2]);
$pLock->testLock($className);

?>
