<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestCaller as BU;

B::checkExeMode(true);
class BreakpointDebuggingTestExampleBase
{
    private static $privateStaticBase = 'private static base';
    private $privateAutoBase = 'private auto base';
    protected static $protectedStaticBase = 'protected static base';
    protected $protectedAutoBase = 'protected auto base';

}

class BreakpointDebuggingTestExample extends \BreakpointDebuggingTestExampleBase
{
    const CONSTANT_TEST = 123;

    private static $privateStatic = 'private static';
    private $privateAuto = 'private auto';

}

class BreakpointDebugging_UnitTestCallerTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
{
    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testInitialize()
    {
        B::initialize();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testHandleUnitTestException()
    {
        B::handleUnitTestException(new \Exception(''));
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testIsUnitTestExeMode()
    {
        B::checkExeMode(true);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=checkExeMode ID=101.
     */
    public function testIsUnitTestExeMode_A()
    {
        ob_start();
        B::checkExeMode();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=checkExeMode ID=102.
     */
    public function testIsUnitTestExeMode_C()
    {
        ob_start();
        B::checkExeMode(false);
    }

//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    public function testExecuteUnitTest_InRelease()
//    {
//        if (BU::$exeMode & B::REMOTE) {
//            parent::markTestSkipped();
//        }
//        BU::markTestSkippedInDebug();
//
//        ob_start();
//
//        $testFileNames = array (
//            '--stop-on-failure --strict ExampleTest.php',
//            '--stop-on-failure --strict ExampleTest.php',
//        );
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        B::executeUnitTest($testFileNames);
//
//        $testFileNames = array (
//            '--stop-on-failure --strict Example_Test.php',
//            '--stop-on-failure --strict Example_Test.php',
//        );
//        BU::$exeMode |= B::IGNORING_BREAK_POINT;
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        B::executeUnitTest($testFileNames);
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    public function testExecuteUnitTest_2_InRelease()
//    {
//        if (BU::$exeMode & B::REMOTE) {
//            parent::markTestSkipped();
//        }
//        BU::markTestSkippedInDebug();
//
//        $testFileNames = array (
//            '--stop-on-failure --strict NotExistTest.php',
//            '--stop-on-failure --strict NotExistTest.php',
//        );
//        ob_start();
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        B::executeUnitTest($testFileNames);
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    public function testDisplayCodeCoverageReport_InRelease()
//    {
//        BU::markTestSkippedInDebug();
//
//        ob_start();
//        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
//        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', array ('PEAR/BreakpointDebugging/OverrideClass.php'));
//    }
    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testGetPropertyForTest()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        $this->assertTrue(B::getPropertyForTest('BreakpointDebuggingTestExample', 'CONSTANT_TEST') === 123); // Constant property.
        $this->assertTrue(B::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStatic') === 'private static'); // Private static property.
        $this->assertTrue(B::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStatic') === 'private static'); // Private static property.
        $this->assertTrue(B::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateAuto') === 'private auto'); // Private auto property.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage failed to open stream: No such file or directory
     */
    public function testGetPropertyForTest_E()
    {
        B::getPropertyForTest('notExistClassName', 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest ID=101.
     */
    public function testGetPropertyForTest_F()
    {
        B::getPropertyForTest('BreakpointDebuggingTestExample', 'notExistPropertyName');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest ID=101.
     */
    public function testGetPropertyForTest_G()
    {
        B::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStaticBase'); // Private static property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest
     */
    public function testGetPropertyForTest_H()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        B::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStaticBase'); // Private static property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testSetPropertyForTest()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        B::setPropertyForTest('\BreakpointDebuggingTestExample', '$privateStatic', 'Changed private static.'); // Private static property.
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebuggingTestExample', '$privateStatic') === 'Changed private static.');
        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateStatic', 'Changed private static 2.'); // Private static property.
        $this->assertTrue(B::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStatic') === 'Changed private static 2.');
        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateAuto', 'Changed private auto 2.'); // Private auto property.
        $this->assertTrue(B::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateAuto') === 'Changed private auto 2.');
        B::setPropertyForTest('\BreakpointDebuggingTestExample', '$protectedStaticBase', 'Changed protected static base.'); // Protected static base property.
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebuggingTestExample', '$protectedStaticBase') === 'Changed protected static base.');
        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$protectedStaticBase', 'Changed protected static base 2.'); // Protected static base property.
        $this->assertTrue(B::getPropertyForTest($pBreakpointDebuggingTestExample, '$protectedStaticBase') === 'Changed protected static base 2.');
        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$protectedAutoBase', 'Changed protected auto base 2.'); // Protected auto base property.
        $this->assertTrue(B::getPropertyForTest($pBreakpointDebuggingTestExample, '$protectedAutoBase') === 'Changed protected auto base 2.');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=101.
     */
    function testSetPropertyForTest_E()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateStaticBase', 'change'); // Private static property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=101.
     */
    function testSetPropertyForTest_F()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateAutoBase', 'change'); // Private auto property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=101.
     */
    function testSetPropertyForTest_G()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$notExistPropertyName', 'change');
    }

//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    function testExecuteUnitTest()
//    {
//        if (BU::$exeMode & B::REMOTE) {
//            parent::markTestSkipped();
//        }
//
//        $testFileNames = array (
//            '--stop-on-failure --strict ExampleTest.php',
//            '--stop-on-failure --strict ExampleTest.php',
//        );
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        ob_start();
//        B::executeUnitTest($testFileNames);
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    function testExecuteUnitTest_E()
//    {
//        if (BU::$exeMode & B::REMOTE) {
//            parent::markTestSkipped();
//        }
//        $testFileNames = array (
//            '--stop-on-failure --strict NotExistTest.php',
//            '--stop-on-failure --strict NotExistTest.php',
//        );
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        ob_start();
//        B::executeUnitTest($testFileNames);
//        $output = ob_get_contents();
//        parent::assertTrue(strpos($output, 'Cannot open file') !== false);
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    function testExecuteUnitTest_F()
//    {
//        if (BU::$exeMode & B::REMOTE) {
//            parent::markTestSkipped();
//        }
//        $testFileNames = array (
//            '--stop-on-failure --strict Example_Test.php',
//            '--stop-on-failure --strict Example_Test.php',
//        );
//        BU::$exeMode |= B::IGNORING_BREAK_POINT;
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        ob_start();
//        B::executeUnitTest($testFileNames);
//        ob_get_clean();
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     *
//     * @expectedException        \BreakpointDebugging_ErrorException
//     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=_getUnitTestDir ID=101.
//     */
//    function test_getUnitTestDir_A()
//    {
//        if (BU::$exeMode & B::REMOTE) {
//            parent::markTestSkipped();
//        }
//        BU::markTestSkippedInRelease(); // Because this unit test is assertion.
//
//        $testFileNames = array (
//            '--stop-on-failure --strict ExampleTest.php',
//            '--stop-on-failure --strict ExampleTest.php',
//        );
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        ob_start();
//        B::executeUnitTest($testFileNames);
//
//        $testFileNames = array (
//            '--stop-on-failure --strict ExampleTest.php',
//            '--stop-on-failure --strict ExampleTest.php',
//        );
//        BU::$exeMode |= B::IGNORING_BREAK_POINT;
//        B::executeUnitTest($testFileNames);
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    function testDisplayCodeCoverageReport()
//    {
//        ob_start();
//        B::setPropertyForTest('BreakpointDebugging', '$unitTestDir', null);
//        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
//        B::setPropertyForTest('BreakpointDebugging', '$unitTestDir', null);
//        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', array ('PEAR/BreakpointDebugging/OverrideClass.php'));
//    }
}

?>
