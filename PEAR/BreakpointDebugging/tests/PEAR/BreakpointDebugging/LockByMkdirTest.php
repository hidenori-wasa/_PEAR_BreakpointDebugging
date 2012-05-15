<?php

require_once './BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

class LockByMkdirTest extends PHPUnit_Framework_TestCase
{
    protected $object;
    private $shmopId;

    protected function setUp()
    {
        $this->object = new \BreakpointDebugging_LockByMkdir('./SomethingDir/FileWhichWantsToLock.txt');
    }

    protected function tearDown()
    {
    
    }

      public function testWhole1()
      {
      $this->assertFalse(file_exists(B::getPropertyForTest($this->object, '$_lockingFlagDirPath')));
      $this->object->lock();
      $this->assertTrue(file_exists(B::getPropertyForTest($this->object, '$_lockingFlagDirPath')));
      $this->object->unlock();
      $this->assertFalse(file_exists(B::getPropertyForTest($this->object, '$_lockingFlagDirPath')));
      }
}

?>
