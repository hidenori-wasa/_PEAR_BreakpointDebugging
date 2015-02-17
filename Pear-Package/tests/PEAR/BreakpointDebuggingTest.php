<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;
use \BreakpointDebugging_Window as BW;

/**
 * Test class for BreakpointDebugging.
 * Generated by PHPUnit on 2012-09-30 at 16:24:29.
 */
class BreakpointDebuggingTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCase
{

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testCheckSuperUserExecution()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method does not exist.

        BU::$exeMode |= B::REMOTE;
        B::checkSuperUserExecution();
    }

    private function _limitAccess()
    {
        B::limitAccess('dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testLimitAccess()
    {
        $this->_limitAccess();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testIniSet()
    {
        B::iniSet('default_charset', 'utf8');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testInitialize()
    {
        \BreakpointDebugging::initialize();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testRefAndGetStatic()
    {
        $developerIP = &B::refStatic('$_developerIP');
        parent::assertTrue($developerIP !== 'hidenori_hidenori');
        $developerIP = 'hidenori_hidenori';
        parent::assertTrue($developerIP === 'hidenori_hidenori');
        parent::assertTrue(B::getStatic('$_developerIP') === 'hidenori_hidenori');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testGetXebugExists_A()
    {
        B::getXebugExists();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testSetXebugExists_A()
    {
        B::setXebugExists(false);
        B::setXebugExists(true);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testIniCheck_A()
    {
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            parent::markTestSkipped();
        }
        B::iniCheck('safe_mode', '', '');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testConvertMbString_A()
    {
        B::convertMbString('A character string.');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testMkdir_A()
    {
        $testDir = B::getStatic('$_workDir') . '/TestMkDir';
        if (is_dir($testDir)) {
            B::rmdir(array ($testDir));
        }
        B::mkdir(array ($testDir));
        B::rmdir(array ($testDir));
        B::mkdir(array ($testDir, 0700));
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testFopen_A()
    {
        $testFileName = B::getStatic('$_workDir') . '/TestFile.txt';
        $pFile = B::fopen(array ($testFileName, 'wb'));
        fclose($pFile);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testCompressIntArray_A()
    {
        B::compressIntArray(array (1, 2));
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testDecompressIntArray_A()
    {
        B::decompressIntArray(false);
        B::decompressIntArray('A character string.');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testExceptionHandler_A()
    {
        ob_start();
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        B::handleException(new \Exception());
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testErrorHandler_A()
    {
        ob_start();
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        B::handleError(E_USER_WARNING, 'dummy');
    }

    function limitAccess_A1()
    {
        B::limitAccess('tests/PEAR/BreakpointDebuggingTest.php');
    }

    function limitAccess_A2()
    {
        B::limitAccess('tests/PEAR/BreakpointDebuggingTest.php', true);
    }

    function limitAccess_A3()
    {
        $includePaths = &B::refStatic('$_includePaths');
        $includePaths = null;
        B::limitAccess('tests/PEAR/BreakpointDebuggingTest.php');
    }

    function limitAccess_A4()
    {
        $includePaths = &B::refStatic('$_includePaths');
        $includePaths = null;
        B::limitAccess('tests/PEAR/BreakpointDebuggingTest.php', true);
    }

    function limitAccess_A5()
    {
        try {
            BU::$exeMode |= B::IGNORING_BREAK_POINT;
            B::limitAccess(array ('tests/PEAR/Dummy1.php', 'tests/PEAR/Dummy2.php'), true);
        } catch (\Exception $e) {

        }
    }

    function limitAccess_A6()
    {
        B::limitAccess(array ('tests/PEAR/Dummy1.php', 'tests/PEAR/BreakpointDebuggingTest.php'), true);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testLimitAccess_A()
    {
        $this->limitAccess_A1();
        $this->limitAccess_A2();
        $this->limitAccess_A3();
        $this->limitAccess_A4();
        $this->limitAccess_A5();
        $this->limitAccess_A6();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testAssert()
    {
        B::assert(true);
        B::assert(true, 123);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebuggingTest FUNCTION=testAssert_A ID=1.
     */
    function testAssert_A()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method is difference.

        B::assert(false, 1);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebuggingTest FUNCTION=testAssert_A2 ID=123.
     */
    function testAssert_A2()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method is difference.

        B::assert(false, 123);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebuggingTest FUNCTION=testAssert_C ID=1.
     */
    public function testAssert_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method is difference.

        B::assert('dummy', 'dummy', 'notExist');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebuggingTest FUNCTION=testAssert_D ID=2.
     */
    public function testAssert_D()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method is difference.

        B::assert('incorrectType');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebuggingTest FUNCTION=testAssert_E ID=3.
     */
    public function testAssert_E()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method is difference.

        B::assert(true, 'incorrectType');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testConvertMbStringForDebug()
    {
        if (BU::$exeMode & B::REMOTE) { // Because this is class method for local.
            parent::markTestSkipped();
        }

        BU::markTestSkippedInRelease(); // Because this unit test class method does not exist.

        $testArray = array (2, "\xE6\x96\x87\xE5\xAD\x97 ");
        $debugValues = B::convertMbStringForDebug('SJIS', 1, $testArray, "\xE6\x96\x87\xE5\xAD\x97 ");

        $cmpArray = array (1, array (2, "\x95\xB6\x8E\x9A "), "\x95\xB6\x8E\x9A ");
        parent::assertTrue($debugValues === $cmpArray);

        BU::$exeMode = B::REMOTE | B::UNIT_TEST;
        B::convertMbStringForDebug('SJIS', 1, $testArray, "\xE6\x96\x87\xE5\xAD\x97 ");
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testIniSet_A()
    {
        B::iniSet('default_charset', 'sjis');
        B::iniSet('default_charset', 'sjis');

        BU::$exeMode = B::REMOTE | B::UNIT_TEST;
        B::iniSet('default_charset', 'utf8');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging FUNCTION=iniSet ID=101.
     */
    public function testIniSet_H()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method does not exist.

        B::iniSet('doc_root', 'dummy'); // Cannot set 'doc_root'.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging FUNCTION=iniSet ID=101.
     */
    public function testIniSet_I()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method does not exist.

        B::iniSet('not_exist', 'true'); // 'not_exist' does not exist.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testDisplayVerification_A()
    {
        BU::markTestSkippedInRelease(); // Because this unit test class method does not exist.

        $mandate = "January 01 2000";
        ob_start();
        $return = B::displayVerification('sscanf', array ($mandate, "%s %d %d", &$month, &$day, &$year));
        parent::assertTrue($return === 3);
        parent::assertTrue($month === 'January');
        parent::assertTrue($day === 1);
        parent::assertTrue($year === 2000);
        BW::close('BreakpointDebugging');
    }

}

?>
