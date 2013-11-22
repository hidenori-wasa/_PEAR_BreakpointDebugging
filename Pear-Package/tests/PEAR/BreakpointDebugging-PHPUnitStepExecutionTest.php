<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnitStepExecution as BU;

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

class BreakpointDebugging_PHPUnitStepExecutionTest extends \BreakpointDebugging_PHPUnitStepExecution_PHPUnitFrameworkTestCase
{
    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testIsUnitTestExeMode()
    {
        BU::checkExeMode(true);
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
//        BU::setPropertyForTest('BreakpointDebugging_PHPUnitStepExecution', '$unitTestDir', null);
//        BU::executeUnitTest($testFileNames);
//
//        $testFileNames = array (
//            '--stop-on-failure --strict Example_Test.php',
//            '--stop-on-failure --strict Example_Test.php',
//        );
//        BU::$exeMode |= B::IGNORING_BREAK_POINT;
//        BU::setPropertyForTest('BreakpointDebugging_PHPUnitStepExecution', '$unitTestDir', null);
//        BU::executeUnitTest($testFileNames);
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
//        BU::setPropertyForTest('BreakpointDebugging_PHPUnitStepExecution', '$unitTestDir', null);
//        BU::executeUnitTest($testFileNames);
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    public function testDisplayCodeCoverageReport_InRelease()
//    {
//        BU::markTestSkippedInDebug();
//
//        ob_start();
//        BU::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
//        BU::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', array ('PEAR/BreakpointDebugging/OverrideClass.php'));
//    }
    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testGetPropertyForTest()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        parent::assertTrue(BU::getPropertyForTest('BreakpointDebuggingTestExample', 'CONSTANT_TEST') === 123); // Constant property.
        parent::assertTrue(BU::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStatic') === 'private static'); // Private static property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStatic') === 'private static'); // Private static property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateAuto') === 'private auto'); // Private auto property.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage failed to open stream:
     */
    public function testGetPropertyForTest_E()
    {
        BU::getPropertyForTest('notExistClassName', 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_PHPUnitStepExecution FUNCTION=getPropertyForTest ID=101.
     */
    public function testGetPropertyForTest_F()
    {
        BU::getPropertyForTest('BreakpointDebuggingTestExample', 'notExistPropertyName');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_PHPUnitStepExecution FUNCTION=getPropertyForTest ID=101.
     */
    public function testGetPropertyForTest_G()
    {
        BU::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStaticBase'); // Private static property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_PHPUnitStepExecution FUNCTION=getPropertyForTest
     */
    public function testGetPropertyForTest_H()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStaticBase'); // Private static property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testSetPropertyForTest()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        BU::setPropertyForTest('\BreakpointDebuggingTestExample', '$privateStatic', 'Changed private static.'); // Private static property.
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebuggingTestExample', '$privateStatic') === 'Changed private static.');
        BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateStatic', 'Changed private static 2.'); // Private static property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStatic') === 'Changed private static 2.');
        BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateAuto', 'Changed private auto 2.'); // Private auto property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateAuto') === 'Changed private auto 2.');
        BU::setPropertyForTest('\BreakpointDebuggingTestExample', '$protectedStaticBase', 'Changed protected static base.'); // Protected static base property.
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebuggingTestExample', '$protectedStaticBase') === 'Changed protected static base.');
        BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$protectedStaticBase', 'Changed protected static base 2.'); // Protected static base property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$protectedStaticBase') === 'Changed protected static base 2.');
        BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$protectedAutoBase', 'Changed protected auto base 2.'); // Protected auto base property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$protectedAutoBase') === 'Changed protected auto base 2.');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_PHPUnitStepExecution FUNCTION=setPropertyForTest ID=101.
     */
    function testSetPropertyForTest_E()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateStaticBase', 'change'); // Private static property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_PHPUnitStepExecution FUNCTION=setPropertyForTest ID=101.
     */
    function testSetPropertyForTest_F()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateAutoBase', 'change'); // Private auto property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_PHPUnitStepExecution FUNCTION=setPropertyForTest ID=101.
     */
    function testSetPropertyForTest_G()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$notExistPropertyName', 'change');
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
//        BU::setPropertyForTest('BreakpointDebugging_PHPUnitStepExecution', '$unitTestDir', null);
//        ob_start();
//        BU::executeUnitTest($testFileNames);
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
//        BU::setPropertyForTest('BreakpointDebugging_PHPUnitStepExecution', '$unitTestDir', null);
//        ob_start();
//        BU::executeUnitTest($testFileNames);
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
//        BU::setPropertyForTest('BreakpointDebugging_PHPUnitStepExecution', '$unitTestDir', null);
//        ob_start();
//        BU::executeUnitTest($testFileNames);
//        ob_get_clean();
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     *
//     * @expectedException        \BreakpointDebugging_ErrorException
//     * @expectedExceptionMessage CLASS=BreakpointDebugging_PHPUnitStepExecution FUNCTION=_getUnitTestDir ID=101.
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
//        BU::setPropertyForTest('BreakpointDebugging_PHPUnitStepExecution', '$unitTestDir', null);
//        ob_start();
//        BU::executeUnitTest($testFileNames);
//
//        $testFileNames = array (
//            '--stop-on-failure --strict ExampleTest.php',
//            '--stop-on-failure --strict ExampleTest.php',
//        );
//        BU::$exeMode |= B::IGNORING_BREAK_POINT;
//        BU::executeUnitTest($testFileNames);
//    }
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    function testDisplayCodeCoverageReport()
//    {
//        ob_start();
//        BU::setPropertyForTest('BreakpointDebugging', '$unitTestDir', null);
//        BU::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
//        BU::setPropertyForTest('BreakpointDebugging', '$unitTestDir', null);
//        BU::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', array ('PEAR/BreakpointDebugging/OverrideClass.php'));
//    }
}

?>
