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

class BreakpointDebugging_PHPUnitTestSimple extends \BreakpointDebugging_PHPUnit_FrameworkTestCaseSimple
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
        $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames, '', 'PHPUNIT_OWN');

        $testFileNames = array (
            'ExampleTest.php',
        );
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        BU::setPropertyForTest('BreakpointDebugging_PHPUnit', '$unitTestDir', null);
        try {
            $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames, '', 'PHPUNIT_OWN');
        } catch (\BreakpointDebugging_ErrorException $e) {
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=executeUnitTest ID=101.');
        }
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
            $breakpointDebuggingPHPUnit->executeUnitTest($testFileNames, '', 'PHPUNIT_OWN');
        } catch (\BreakpointDebugging_ErrorException $e) {
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=executeUnitTest ID=102.');
        }
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
            BU::assertExceptionMessage($e, 'Class notExistClassName does not exist');
        }
    }

    public function testGetPropertyForTest_F()
    {
        try {
            BU::getPropertyForTest('BreakpointDebuggingTestExample', 'notExistPropertyName');
        } catch (\BreakpointDebugging_ErrorException $e) {
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=getPropertyForTest ID=101.');
        }
    }

    public function testGetPropertyForTest_G()
    {
        try {
            BU::getPropertyForTest('BreakpointDebuggingTestExample', '$privateStaticBase'); // Private static property of base class.
        } catch (\BreakpointDebugging_ErrorException $e) {
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=getPropertyForTest ID=101.');
        }
    }

    public function testGetPropertyForTest_H()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        try {
            BU::getPropertyForTest($pBreakpointDebuggingTestExample, '$privateStaticBase'); // Private static property of base class.
        } catch (\BreakpointDebugging_ErrorException $e) {
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=getPropertyForTest');
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
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=setPropertyForTest ID=101.');
        }
    }

    function testSetPropertyForTest_F()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        try {
            BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$privateAutoBase', 'change'); // Private auto property of base class.
        } catch (\BreakpointDebugging_ErrorException $e) {
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=setPropertyForTest ID=101.');
        }
    }

    function testSetPropertyForTest_G()
    {
        $pBreakpointDebuggingTestExample = new \BreakpointDebuggingTestExample();

        try {
            BU::setPropertyForTest($pBreakpointDebuggingTestExample, '$notExistPropertyName', 'change');
        } catch (\BreakpointDebugging_ErrorException $e) {
            BU::assertExceptionMessage($e, 'CLASS=BreakpointDebugging_PHPUnit FUNCTION=setPropertyForTest ID=101.');
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
