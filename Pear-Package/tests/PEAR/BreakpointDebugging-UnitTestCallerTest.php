<?php

chdir(__DIR__ . '/../../');
require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestOverridingBase as BU;

//B::isUnitTestExeMode('UNIT_TEST');
B::isUnitTestExeMode(true);
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

//if (B::getStatic('$exeMode') & B::RELEASE) {
//B::isUnitTestExeMode('RELEASE_UNIT_TEST');
class BreakpointDebugging_UnitTestCallerTest extends \BreakpointDebugging_UnitTestOverriding
{
    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testCheckUnitTestExeMode()
    {
        //BU::markTestSkippedInDebug();
        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
        //B::isUnitTestExeMode('RELEASE_UNIT_TEST');
        //BU::$exeMode = B::RELEASE | B::UNIT_TEST;
        //B::isUnitTestExeMode('UNIT_TEST');
        B::isUnitTestExeMode(true);
        //B::isUnitTestExeMode('RELEASE_UNIT_TEST');
//            BU::$exeMode = B::REMOTE | B::RELEASE | B::UNIT_TEST;
//            B::isUnitTestExeMode('RELEASE_UNIT_TEST');
//            BU::$exeMode = B::RELEASE;
//            B::isUnitTestExeMode();
//            BU::$exeMode = B::REMOTE | B::RELEASE;
//            B::isUnitTestExeMode();
//            BU::$exeMode = B::RELEASE;
//            B::isUnitTestExeMode('FALSE');
//            BU::$exeMode = B::REMOTE | B::RELEASE;
//            B::isUnitTestExeMode('FALSE');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testExecuteUnitTest_InRelease()
    {
        BU::markTestSkippedInDebug();

        ob_start();

        $testFileNames = array (
            '--stop-on-failure --strict ExampleTest.php',
            '--stop-on-failure --strict ExampleTest.php',
        );
        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
        B::executeUnitTest($testFileNames);

        $testFileNames = array (
            '--stop-on-failure --strict Example_Test.php',
            '--stop-on-failure --strict Example_Test.php',
        );
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
        B::executeUnitTest($testFileNames);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testExecuteUnitTest_A_InRelease()
    {
        BU::markTestSkippedInDebug();

        $testFileNames = array (
            '--stop-on-failure --strict NotExistTest.php',
            '--stop-on-failure --strict NotExistTest.php',
        );
        ob_start();
        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
        B::executeUnitTest($testFileNames);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testDisplayCodeCoverageReport_InRelease()
    {
        BU::markTestSkippedInDebug();

        ob_start();
        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', array ('PEAR/BreakpointDebugging/OverrideClass.php'));
    }

//    }
//} else {
    //B::isUnitTestExeMode('DEBUG_UNIT_TEST');
//    class BreakpointDebuggingTestExampleBase
//    {
//        private static $privateStaticBase = 'private static base';
//        private $privateAutoBase = 'private auto base';
//        protected static $protectedStaticBase = 'protected static base';
//        protected $protectedAutoBase = 'protected auto base';
//
//    }
//
//    class BreakpointDebuggingTestExample extends \BreakpointDebuggingTestExampleBase
//    {
//        const CONSTANT_TEST = 123;
//
//        private static $privateStatic = 'private static';
//        private $privateAuto = 'private auto';
//
//    }
//    class BreakpointDebugging_UnitTestCallerTest extends \BreakpointDebugging_UnitTestOverriding
//    {
//    /**
//     * @covers \BreakpointDebugging<extended>
//     */
//    public function testCheckUnitTestExeMode_InDebug()
//    {
//        BU::markTestSkippedInRelease();
//
//        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
//        //B::isUnitTestExeMode('DEBUG_UNIT_TEST');
//        //BU::$exeMode = B::UNIT_TEST;
//        //B::isUnitTestExeMode('UNIT_TEST');
//        B::isUnitTestExeMode(true);
//        //B::isUnitTestExeMode('DEBUG_UNIT_TEST');
////            BU::$exeMode = 0;
////            B::isUnitTestExeMode();
////
////            BU::$exeMode = 0;
////            B::isUnitTestExeMode('FALSE');
//    }
    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=isUnitTestExeMode ID=1
     */
    public function testCheckUnitTestExeMode_A()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::isUnitTestExeMode('dummy', 'notExist');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=isUnitTestExeMode ID=3
     */
    public function testCheckUnitTestExeMode_B()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::isUnitTestExeMode(false);
    }

//    /**
//     * @covers \BreakpointDebugging<extended>
//     *
//     * @expectedException        \BreakpointDebugging_ErrorException
//     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=isUnitTestExeMode ID=5
//     */
//    public function testCheckUnitTestExeMode_C()
//    {
//        B::isUnitTestExeMode('RELEASE_UNIT_TEST');
//    }
    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument 1 for BreakpointDebugging_UnitTestCaller::isUnitTestExeMode()
     */
    public function testCheckUnitTestExeMode_D()
    {
        B::isUnitTestExeMode();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=isUnitTestExeMode ID=2
     */
    public function testCheckUnitTestExeMode_E()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::isUnitTestExeMode('Mistaken character string.');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testGetPropertyForTest_A()
    {
        BU::markTestSkippedInRelease();

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
     * @expectedExceptionMessage Missing argument 2 for BreakpointDebugging_UnitTestCaller::getPropertyForTest()
     */
    public function testGetPropertyForTest_B()
    {
        B::getPropertyForTest('dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest ID=1
     */
    public function testGetPropertyForTest_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::getPropertyForTest('dummy', 'dummy', 'notExist');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest ID=2
     */
    public function testGetPropertyForTest_D()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::getPropertyForTest('dummy', 123);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest ID=3
     */
    public function testGetPropertyForTest_E()
    {
        B::getPropertyForTest(123, 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage failed to open stream: No such file or directory
     */
    public function testGetPropertyForTest_F()
    {
        B::getPropertyForTest('notExistClassName', 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest ID=4
     */
    public function testGetPropertyForTest_G()
    {
        B::getPropertyForTest('BreakpointDebuggingTestExample', 'notExistPropertyName');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest ID=4
     */
    public function testGetPropertyForTest_H()
    {
        B::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStaticBase'); // Private static property of base class.
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=getPropertyForTest
     */
    public function testGetPropertyForTest_I()
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
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument 3 for BreakpointDebugging_UnitTestCaller::setPropertyForTest()
     */
    function testSetPropertyForTest_A()
    {
        B::setPropertyForTest('dummy', 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=1
     */
    function testSetPropertyForTest_B()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::setPropertyForTest('dummy', 'dummy', 'dummy', 'notExist');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=2
     */
    function testSetPropertyForTest_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::setPropertyForTest('dummy', 123, 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=3
     */
    function testSetPropertyForTest_D()
    {
        B::setPropertyForTest(123, 'dummy', 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=4
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
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=4
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
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=setPropertyForTest ID=4
     */
    function testSetPropertyForTest_G()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        B::setPropertyForTest($pBreakpointDebuggingTestExample, '$notExistPropertyName', 'change');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testExecuteUnitTest()
    {
        ob_start();

        $testFileNames = array (
            '--stop-on-failure --strict ExampleTest.php',
            '--stop-on-failure --strict ExampleTest.php',
        );
        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
        B::executeUnitTest($testFileNames);

        $testFileNames = array (
            '--stop-on-failure --strict Example_Test.php',
            '--stop-on-failure --strict Example_Test.php',
        );
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
        B::executeUnitTest($testFileNames);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument 1 for BreakpointDebugging_UnitTestCaller::executeUnitTest()
     */
    function testExecuteUnitTest_A()
    {
        B::executeUnitTest();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=executeUnitTest ID=1
     */
    function testExecuteUnitTest_B()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::executeUnitTest('dummy', 'notExist');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=executeUnitTest ID=2
     */
    function testExecuteUnitTest_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::executeUnitTest('incorrectType');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=executeUnitTest ID=3
     */
    function testExecuteUnitTest_D()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::executeUnitTest(array ());
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testExecuteUnitTest_E()
    {
        $testFileNames = array (
            '--stop-on-failure --strict NotExistTest.php',
            '--stop-on-failure --strict NotExistTest.php',
        );
        ob_start();
        B::setPropertyForTest('BreakpointDebugging_UnitTestCaller', '$unitTestDir', null);
        B::executeUnitTest($testFileNames);
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    function testDisplayCodeCoverageReport()
    {
        ob_start();
        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
        B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', array ('PEAR/BreakpointDebugging/OverrideClass.php'));
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument 2 for BreakpointDebugging_UnitTestCaller::displayCodeCoverageReport()
     */
    function testDisplayCodeCoverageReport_A()
    {
        B::displayCodeCoverageReport('dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=displayCodeCoverageReport ID=1
     */
    function testDisplayCodeCoverageReport_B()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::displayCodeCoverageReport('dummy', 'dummy', 'notExist');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=displayCodeCoverageReport ID=2
     */
    function testDisplayCodeCoverageReport_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::displayCodeCoverageReport(123, 'dummy');
    }

    /**
     * @covers \BreakpointDebugging<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_UnitTestCaller FUNCTION=displayCodeCoverageReport ID=3
     */
    function testDisplayCodeCoverageReport_D()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        B::displayCodeCoverageReport('dummy', 123);
    }

}

//}

?>
