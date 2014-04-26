<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;

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

class BreakpointDebugging_PHPUnitTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCaseSimple
{
    public function testIsUnitTestExeMode()
    {
        BU::checkExeMode(true);
    }

    public function testExecuteUnitTest_InRelease()
    {
        if (BU::$exeMode & B::REMOTE) {
            return;
        }
        if (parent::markTestSkippedInDebug()) {
            return;
        }

        ob_start();

        $testFileNames = array (
            'ExampleTest.php',
        );
        BU::setPropertyForTest('BreakpointDebugging_PHPUnit', '$unitTestDir', null);
        $breakpointDebuggingPHPUnit = new \BreakpointDebugging_PHPUnit();
        $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames);

        $testFileNames = array (
            'Example_Test.php',
        );
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        BU::setPropertyForTest('BreakpointDebugging_PHPUnit', '$unitTestDir', null);
        $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames);
    }

    public function testExecuteUnitTest_2_InRelease()
    {
        if (BU::$exeMode & B::REMOTE) {
            return;
        }
        if (parent::markTestSkippedInDebug()) {
            return;
        }

        $testFileNames = array (
            'NotExistTest.php',
        );
        ob_start();
        BU::setPropertyForTest('BreakpointDebugging_PHPUnit', '$unitTestDir', null);
        $breakpointDebuggingPHPUnit = new \BreakpointDebugging_PHPUnit();
        $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames);
    }

    public function testDisplayCodeCoverageReport_InRelease()
    {
        if (parent::markTestSkippedInDebug()) {
            return;
        }

        ob_start();
        $breakpointDebuggingPHPUnit = new \BreakpointDebugging_PHPUnit();
        $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
        $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', array ('PEAR/BreakpointDebugging/OverrideClass.php'));
    }

    public function testGetPropertyForTest()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        parent::assertTrue(BU::getPropertyForTest('BreakpointDebuggingTestExample', 'CONSTANT_TEST') === 123); // Constant property.
        parent::assertTrue(BU::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStatic') === 'private static'); // Private static property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStatic') === 'private static'); // Private static property.
        parent::assertTrue(BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateAuto') === 'private auto'); // Private auto property.
    }

    public function testGetPropertyForTest_E()
    {
        try {
            BU::getPropertyForTest('notExistClassName', 'dummy');
        } catch (\ReflectionException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'Class notExistClassName does not exist') !== false);
        }
    }

    public function testGetPropertyForTest_F()
    {
        try {
            BU::getPropertyForTest('BreakpointDebuggingTestExample', 'notExistPropertyName');
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=getPropertyForTest ID=101.') !== false);
        }
    }

    public function testGetPropertyForTest_G()
    {
        try {
            BU::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStaticBase'); // Private static property of base class.
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=getPropertyForTest ID=101.') !== false);
        }
    }

    public function testGetPropertyForTest_H()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        try {
            BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStaticBase'); // Private static property of base class.
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=getPropertyForTest') !== false);
        }
    }

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

    function testSetPropertyForTest_E()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        try {
            BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateStaticBase', 'change'); // Private static property of base class.
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=setPropertyForTest ID=101.') !== false);
        }
    }

    function testSetPropertyForTest_F()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        try {
            BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateAutoBase', 'change'); // Private auto property of base class.
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=setPropertyForTest ID=101.') !== false);
        }
    }

    function testSetPropertyForTest_G()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        try {
            BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$notExistPropertyName', 'change');
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=setPropertyForTest ID=101.') !== false);
        }
    }

    function testExecuteUnitTest()
    {
        if (BU::$exeMode & B::REMOTE) {
            return;
        }

        $testFileNames = array (
            'ExampleTest.php',
        );
        BU::setPropertyForTest('BreakpointDebugging_PHPUnit', '$unitTestDir', null);
        ob_start();
        $breakpointDebuggingPHPUnit = new \BreakpointDebugging_PHPUnit();
        $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames);
    }

    function testExecuteUnitTest_E()
    {
        if (BU::$exeMode & B::REMOTE) {
            return;
        }
        $testFileNames = array (
            'NotExistTest.php',
        );
        BU::setPropertyForTest('BreakpointDebugging_PHPUnit', '$unitTestDir', null);
        $breakpointDebuggingPHPUnit = new \BreakpointDebugging_PHPUnit();
        try {
            $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames);
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(strpos($e->getMessage(), 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=executeUnitTest ID=102.') !== false);
        }
    }

    function testDisplayCodeCoverageReport()
    {
        ob_start();
        $breakpointDebuggingPHPUnit = new \BreakpointDebugging_PHPUnit();
        $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php'));
        $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
    }

}
