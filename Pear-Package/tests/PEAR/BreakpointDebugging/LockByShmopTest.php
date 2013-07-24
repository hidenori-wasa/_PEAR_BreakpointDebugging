<?php

//chdir(__DIR__ . '/../../../');
//require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestCaller as BU;

B::checkExeMode(true);
class BreakpointDebugging_LockByShmopTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
{
    protected $LockByShmop;

    function setUp()
    {
        parent::setUp();
        // Checks shared memory operation extension existence.
        if ((BU::$exeMode & B::REMOTE)
            && !extension_loaded('shmop')
        ) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Unlinks synchronization file.
        $lockFileName = B::getStatic('$_workDir') . '/LockByShmop.txt';
        if (is_file($lockFileName)) {
            B::unlink(array ($lockFileName));
        }
        // Constructs instance.
        $this->LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

    function tearDown()
    {
        // Destructs instance.
        $this->LockByShmop = null;
        parent::tearDown();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone ID=101.
     */
    function test__clone()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        $tmp = clone $this->LockByShmop;
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    function testSingleton()
    {
        //B::setPropertyForTest('\BreakpointDebugging_Lock', '$_instance', null);
        //// Unlinks synchronization file.
        //B::unlink(array (B::getStatic('$_workDir') . '/LockByShmop.txt'));
        //\BreakpointDebugging_LockByShmop::singleton(5, 0);

        //$pFile = B::fopen(array (B::getStatic('$_workDir') . '/LockByShmop.txt', 'wb'), 0200);
        $pFile = B::fopen(array (B::getStatic('$_workDir') . '/LockByShmop.txt', 'wb'));
        fwrite($pFile, 'dummydummy');
        fclose($pFile);
        B::setPropertyForTest('\BreakpointDebugging_Lock', '$_instance', null);
        \BreakpointDebugging_LockByShmop::singleton(5, 10);
        //B::unlink(array (B::getStatic('$_workDir') . '/LockByShmop.txt'));
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    public function test__destruct()
    {
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByShmop);
        // Calls "__destruct".
        $this->LockByShmop = null;
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    public function testForceUnlocking()
    {
        $this->LockByShmop->lock();
        $this->LockByShmop->lock();

        $this->assertTrue(B::getPropertyForTest($this->LockByShmop, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        $this->assertTrue(B::getPropertyForTest($this->LockByShmop, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    function testLockThenUnlock_A()
    {
        $this->LockByShmop->lock();
        $this->LockByShmop->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    function testLockThenUnlock_B()
    {
        $this->LockByShmop->lock();
        $this->LockByShmop->lock();
        $this->LockByShmop->unlock();
        $this->LockByShmop->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->LockByShmop->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_D()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->LockByShmop->unlock(); // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_E()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->LockByShmop->lock();
        // Calls "__destruct()".
        $this->LockByShmop = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_F()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->LockByShmop->lock();
        $this->LockByShmop->lock();
        $this->LockByShmop->unlock();
        // Calls "__destruct()".
        $this->LockByShmop = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    function testSingleton_A()
    {
        $LockByShmop1 = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
        $LockByShmop2 = &\BreakpointDebugging_LockByShmop::singleton(5, 10); // Same object.
        $this->assertTrue($LockByShmop1 === $LockByShmop2);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_B()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.
        // Constructs instance of other class.
        $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.
        // Constructs instance of other class.
        $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
    }

}

?>
