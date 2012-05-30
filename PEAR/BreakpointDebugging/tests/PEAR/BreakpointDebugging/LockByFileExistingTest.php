<?php

chdir(__DIR__ . '/../../../../../');
require_once './BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

class LockByFileExistingTest extends PHPUnit_Framework_TestCase
{

    protected $object;

    protected function setUp()
    {
        $this->object = new \BreakpointDebugging_LockByFileExisting(__DIR__ . '/LockByFileExistingMultiprocessTest/SomethingDir/FileForLockByFileExisting.txt');
    }

    protected function tearDown()
    {

    }

    public function testWhole1()
    {
        $this->object->lock();
        clearstatcache();
        $this->assertTrue(filesize(B::getPropertyForTest($this->object, '$_lockingFlagFilePath')) !== 0);
        $this->object->unlock();
        clearstatcache();
        $this->assertTrue(filesize(B::getPropertyForTest($this->object, '$_lockingFlagFilePath')) === 0);
    }

}

?>
