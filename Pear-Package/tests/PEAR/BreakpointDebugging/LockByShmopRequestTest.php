<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;

class BreakpointDebugging_LockByShmopRequestTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCase
{
    protected $LockByShmopRequest;

    function setUp()
    {
        parent::setUp();
        // Checks shared memory operation extension existence.
        if ((BU::$exeMode & B::REMOTE) //
            && !extension_loaded('shmop') //
        ) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance.
        // $this->LockByShmopRequest = &\BreakpointDebugging_LockByShmopRequest::singleton(5, 10);
        $this->LockByShmopRequest = &\BreakpointDebugging_LockByShmopRequest::singleton(300);
    }

    function tearDown()
    {
        // Stops "\BreakpointDebugging_LockByShmopResponse" class instance.
        $sharedMemoryID = BU::getPropertyForTest('BreakpointDebugging_LockByShmopRequest', '$_sharedMemoryID');
        $stopLocation = BU::getPropertyForTest('BreakpointDebugging_LockByShmopRequest', '$_stopLocation');
        $isExisting = shmop_read($sharedMemoryID, $stopLocation, 1);
        B::assert($isExisting !== false);
        if ($isExisting === ' ') {
            $result = shmop_write($sharedMemoryID, '1', $stopLocation);
            B::assert($result !== false);
            while (true) {
                // 0.2 second sleep.
                usleep(200000);
                $isStop = shmop_read($sharedMemoryID, $stopLocation, 1);
                B::assert($isStop !== false);
                if ($isStop === '0') {
                    BU::setPropertyForTest('BreakpointDebugging_LockByShmopRequest', '$_sharedMemoryID', null);
                    // Closes the pipe for static backup, but apache socket error is caused.
                    $pPipe = BU::getPropertyForTest('BreakpointDebugging_LockByShmopRequest', '$_pPipe');
                    if (is_resource($pPipe)) {
                        $result = pclose($pPipe);
                        B::assert($result !== -1);
                    }
                    break;
                }
            }
        }
        // Destructs instance.
        $this->LockByShmopRequest = null;
        parent::tearDown();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    function testMultiprocess()
    {
        // Destructs instance.
        $this->LockByShmopRequest = null;
        $main = new \tests_PEAR_BreakpointDebugging_MultiprocessTest_Main();
        if (!$main->test(1234, '\BreakpointDebugging_LockByShmopRequest')) {
            parent::fail();
        }
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    function testLockThenUnlock_A()
    {
        $this->LockByShmopRequest->lock();
        $this->LockByShmopRequest->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    function testLockThenUnlock_A2()
    {
        $this->LockByShmopRequest->lock();
        $this->LockByShmopRequest->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone ID=101.
     */
    function test__clone()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $tmp = clone $this->LockByShmopRequest;
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    function testSingleton()
    {
        $pFile = B::fopen(array (B::getStatic('$_workDir') . '/LockByShmopRequest.txt', 'wb'));
        fwrite($pFile, 'dummydummy');
        fclose($pFile);
        BU::setPropertyForTest('\BreakpointDebugging_Lock', '$_instance', null);
        \BreakpointDebugging_LockByShmopRequest::singleton(5, 10);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    public function test__destruct()
    {
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByShmopRequest);
        // Calls "__destruct".
        $this->LockByShmopRequest = null;
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    public function testForceUnlocking()
    {
        $this->LockByShmopRequest->lock();
        $this->LockByShmopRequest->lock();

        parent::assertTrue(BU::getPropertyForTest($this->LockByShmopRequest, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        parent::assertTrue(BU::getPropertyForTest($this->LockByShmopRequest, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    function testLockThenUnlock_B()
    {
        $this->LockByShmopRequest->lock();
        $this->LockByShmopRequest->lock();
        $this->LockByShmopRequest->unlock();
        $this->LockByShmopRequest->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_C()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->LockByShmopRequest->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_D()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        try {
            $this->LockByShmopRequest->lock();
            $this->LockByShmopRequest->unlock();
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->LockByShmopRequest->unlock(); // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_E()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->LockByShmopRequest->lock();
        // Calls "__destruct()".
        $this->LockByShmopRequest = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_F()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->LockByShmopRequest->lock();
        $this->LockByShmopRequest->lock();
        $this->LockByShmopRequest->unlock();
        // Calls "__destruct()".
        $this->LockByShmopRequest = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     */
    function testSingleton_A()
    {
        $LockByShmopRequest1 = &\BreakpointDebugging_LockByShmopRequest::singleton(5, 10);
        $LockByShmopRequest2 = &\BreakpointDebugging_LockByShmopRequest::singleton(5, 10); // Same object.
        parent::assertTrue($LockByShmopRequest1 === $LockByShmopRequest2);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_B()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.
        // Constructs instance of other class.
        $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmopRequest<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_C()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.
        // Constructs instance of other class.
        $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
    }

}
