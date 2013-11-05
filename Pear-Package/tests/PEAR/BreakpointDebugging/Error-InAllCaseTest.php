<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnitStepExecution as BU;
use \BreakpointDebugging_Error_InAllCaseTest as T;

class TestErrorHandler2Parent
{
    static $isRegister = false;

}

class example
{
    const CONST_TEST = 1; // Tests constant property when unit test reflects object.

    public $nestingObject;

}

function testParentException2($nestingArray, $nestingObject, $e)
{
    throw new \Exception('Test message.', 0, $e);
}

function testParentException($nestingArray, $nestingObject, $e, $tmpfile = null, $globals = null, $string = null)
{
    testParentException2($nestingArray, $nestingObject, $e);
}

/**
 * Test class for BreakpointDebugging_Error.
 * Generated by PHPUnit on 2012-10-08 at 13:08:45.
 */
class BreakpointDebugging_Error_InAllCaseTest extends \BreakpointDebugging_PHPUnitStepExecution_PHPUnitFrameworkTestCase
{
    private $_error;
    private static $_errorLogDir;
    static $line1_, $line2_, $lineA_, $lineB_;
    static $line1, $line2, $lineA, $lineB;

    static function setUpBeforeClass()
    {
        $maxLogStringSize = &B::refStatic('$_maxLogStringSize');
        $maxLogStringSize = 8;
        self::$_errorLogDir = \BreakpointDebugging_Error::getErrorLogDir();
    }

    function setUp()
    {
        parent::setUp();

        $errorLogDirectory = B::getStatic('$_workDir') . self::$_errorLogDir;
        if (is_dir($errorLogDirectory)) {
            $errorLogDirElements = scandir($errorLogDirectory);
            foreach ($errorLogDirElements as $errorLogDirElement) {
                $errorLogDirElementPath = $errorLogDirectory . $errorLogDirElement;
                if (!is_file($errorLogDirElementPath)) {
                    continue;
                }
                // Deletes the error log file, variable configuring file and the error location file.
                B::unlink(array ($errorLogDirElementPath));
            }
            B::rmdir(array ($errorLogDirectory));
        }
        $this->_error = new \BreakpointDebugging_Error();
    }

    function tearDown()
    {
        $this->_error = null;

        parent::tearDown();
    }

    /**
     * @covers \BreakpointDebugging_Error<extended>
     */
    function test__construct()
    {
        new \BreakpointDebugging_Error();
        new \BreakpointDebugging_Error();

        B::setXebugExists(false);
        new \BreakpointDebugging_Error();
        B::setXebugExists(true);
    }

    /**
     * Gets a file number of a file name in the file of "ErrorLog.var.conf".
     *
     * @param type $fileName File name which was registered in the file of "ErrorLog.var.conf".
     */
    private function _getFileNumber($fileName)
    {
        $fileName = str_replace('\\', '/', $fileName);
        $pFile = B::fopen(array (B::getStatic('$_workDir') . self::$_errorLogDir . 'ErrorLog.var.conf', 'rb'));
        fgets($pFile);
        while ($readFileLine = fgets($pFile)) {
            $readFileName = substr($readFileLine, 0, strrpos($readFileLine, '?'));
            $readFileName = str_replace('\\', '/', $readFileName);
            if (strcasecmp($fileName, $readFileName) === 0) {
                fclose($pFile);
                return rtrim(substr($readFileLine, strrpos($readFileLine, '?') + 1), PHP_EOL);
            }
        }
        fclose($pFile);
        throw new \BreakpointDebugging_ErrorException('', 101);
    }

    /**
     * @covers \BreakpointDebugging_Error<extended>
     */
    function testHandleException2()
    {
        $error = &$this->_error;

        BU::markTestSkippedInDebug(); // Because this unit test is the logging check.

        $testString = 'Test string.';
        B::addValuesToTrace(array ('$testString' => $testString));
        B::addValuesToTrace(array ('$testString' => $testString));
        $maxLogParamNestingLevel = &B::refStatic('$_maxLogParamNestingLevel');
        $maxLogParamNestingLevel = 2;
        $maxLogElementNumber = &B::refStatic('$_maxLogElementNumber');
        $maxLogElementNumber = 6;

        $parentFilePath = __DIR__ . '/testExceptionHandler2_Parent.php';
        include_once $parentFilePath;
        $lineParent = __LINE__ - 1;

        $parentFileNumber = $this->_getFileNumber($parentFilePath);
        $thisFileNumber = $this->_getFileNumber(__FILE__);
        function test2_($error)
        {
            BU::$exeMode |= B::IGNORING_BREAK_POINT;
            for ($count = 0; $count < 2; $count++) {
                $error->handleException2(new \Exception(), B::$prependExceptionLog);
                T::$line1_ = __LINE__ - 1;
            }
            BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
        }

        function test1_($error)
        {
            test2_($error);
            T::$line2_ = __LINE__ - 1;
        }

        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        $error->handleException2(new \Exception(), B::$prependExceptionLog);
        $line = __LINE__ - 1;
        BU::$exeMode &= ~B::IGNORING_BREAK_POINT;

        test1_($error);
        $line3 = __LINE__ - 1;

        $binData1 = file_get_contents(B::getStatic('$_workDir') . self::$_errorLogDir . $parentFileNumber . '.bin');

        $cmpBinData1 = rtrim(B::compressIntArray(array ($parentFileNumber, $line__, $thisFileNumber, $lineParent)), PHP_EOL);
        parent::assertTrue(strpos($binData1, $cmpBinData1) !== false);

        $cmpBinData1 = rtrim(B::compressIntArray(array ($parentFileNumber, self::$lineA_, $parentFileNumber, self::$lineB_, $parentFileNumber, $lineC_, $thisFileNumber, $lineParent)), PHP_EOL);
        parent::assertTrue(strpos($binData1, $cmpBinData1) !== false);

        $binData2 = file_get_contents(B::getStatic('$_workDir') . self::$_errorLogDir . $thisFileNumber . '.bin');

        $cmpBinData2 = rtrim(B::compressIntArray(array ($thisFileNumber, $line)), PHP_EOL);
        parent::assertTrue(strpos($binData2, $cmpBinData2) !== false);

        $cmpBinData2 = rtrim(B::compressIntArray(array ($thisFileNumber, self::$line1_, $thisFileNumber, self::$line2_, $thisFileNumber, $line3)), PHP_EOL);
        parent::assertTrue(strpos($binData2, $cmpBinData2) !== false);

        B::addValuesToTrace(array ('$parentFileNumber' => $parentFileNumber, '$thisFileNumber' => $thisFileNumber));
        new \BreakpointDebugging_Error();
        $valuesToTrace = &B::refStatic('$_valuesToTrace');
        $valuesToTrace = null;

        ob_start();

        // Has parent exception, and tests nesting array parameter, and tests nesting object parameter.
        $nestingArray = array (array ('test1', 'test2', 'test3'));
        $nestingObject = new \example();
        $nestingObject->nestingObject = new \stdClass();
        try {
            testParentException($nestingArray, $nestingObject, null);
        } catch (\Exception $e) {
            try {
                call_user_func_array('testParentException', array ($nestingArray, $nestingObject, $e, tmpfile(), $GLOBALS, '123456789', 'Out of parameter number.'));
            } catch (\Exception $e) {
                BU::$exeMode |= B::IGNORING_BREAK_POINT;
                $error->handleException2($e, 'Test.');
                BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
            }
        }
        // "SJIS" message.
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        $error->handleException2(new \Exception(), "\x95\xB6\x8E\x9A");
        BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
        // Error log file rotation.
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        $maxLogFileByteSize = &B::refStatic('$_maxLogFileByteSize');
        $storeMaxLogFileByteSize = $maxLogFileByteSize;
        $maxLogFileByteSize = 1;
        $error->handleException2(new \Exception(), '');
        $error->handleException2(new \Exception(), '');
        $error->handleException2(new \Exception(), '');
        $error->handleException2(new \Exception(), '');
        $error->handleException2(new \Exception(), '');
        $error->handleException2(new \Exception(), '');
        $error->handleException2(new \Exception(), '');
        $error->handleException2(new \Exception(), '');
        $maxLogFileByteSize = $storeMaxLogFileByteSize;
        BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
        // Called from "BreakpointDebugging_InAllCase::callExceptionHandlerDirectly()" method.
        try {
            BU::$exeMode |= B::IGNORING_BREAK_POINT;
            B::assert(false);
        } catch (\Exception $e) {

        }
    }

    function exceptionHandler2()
    {
        throw new \Exception();
    }

    /**
     * @covers \BreakpointDebugging_Error<extended>
     */
    function testHandleException2_A()
    {
        BU::markTestSkippedInDebug(); // Because this unit test is the logging check.

        $maxLogStringSize = &B::refStatic('$_maxLogStringSize');
        $maxLogStringSize = 140000;
        $workDir = B::getStatic('$_workDir');

        $logfileMaximumCapacityException = function ($self) {
                try {
                    BU::$exeMode |= B::IGNORING_BREAK_POINT;
                    $self->exceptionHandler2(str_repeat('1234567890', 14000), array ('Test1.'), 1.1);
                } catch (\Exception $e) {
                    B::handleException($e);
                    BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
                }
            };

        $logStartException = function () {
                BU::$exeMode |= B::IGNORING_BREAK_POINT;
                B::handleException(new \Exception('The log start exception.'));
                BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
            };

        // Makes "php_error_1.log".
        $logfileMaximumCapacityException($this);
        $stat = stat($workDir . self::$_errorLogDir . 'php_error_1.log');
        parent::assertTrue($stat['size'] === 1024 * 1024 / 8);
        // Makes "php_error_2.log".
        $logStartException();
        $stat2 = stat($workDir . self::$_errorLogDir . 'php_error_2.log');
        parent::assertTrue(0 < $stat2['size'] && $stat2['size'] < 1024 * 1024 / 8);
        // Makes "php_error_3.log".
        $logfileMaximumCapacityException($this);
        $stat3 = stat($workDir . self::$_errorLogDir . 'php_error_3.log');
        parent::assertTrue($stat3['size'] === 1024 * 1024 / 8);
        // Makes "php_error_4.log".
        $logStartException();
        // Makes "php_error_5.log".
        $logfileMaximumCapacityException($this);
        // Makes "php_error_6.log".
        $logStartException();
        // Makes "php_error_7.log".
        $logfileMaximumCapacityException($this);
        // Makes "php_error_8.log".
        $logStartException();
        // Deletes "php_error_1.log", then makes "php_error_1.log".
        $logfileMaximumCapacityException($this);
    }

    /**
     * @covers \BreakpointDebugging_Error<extended>
     */
    function testHandleError2()
    {
        $error = &$this->_error;

        BU::markTestSkippedInDebug(); // Because this unit test is the logging check.
        function handleError($error)
        {
            BU::$exeMode |= B::IGNORING_BREAK_POINT;
            $error->handleError2(E_USER_WARNING, '', B::$prependErrorLog, debug_backtrace());
            BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
        }

        function trigger_error2($error)
        {
            handleError($error);
        }

        $parentFilePath = __DIR__ . '/testErrorHandler2_Parent.php';
        include_once $parentFilePath;
        $lineParent = __LINE__ - 1;

        $parentFileNumber = $this->_getFileNumber($parentFilePath);
        $thisFileNumber = $this->_getFileNumber(__FILE__);
        function test2($error)
        {
            trigger_error2($error);
            T::$line1 = __LINE__ - 1;
        }

        function test1($error)
        {
            test2($error);
            T::$line2 = __LINE__ - 1;
        }

        trigger_error2($error);
        $line = __LINE__ - 1;
        test1($error);
        $line3 = __LINE__ - 1;

        $binData1 = file_get_contents(B::getStatic('$_workDir') . self::$_errorLogDir . $parentFileNumber . '.bin');

        $cmpBinData1 = rtrim(B::compressIntArray(array ($parentFileNumber, $line_, $thisFileNumber, $lineParent)), PHP_EOL);
        parent::assertTrue(strpos($binData1, $cmpBinData1) !== false);

        $cmpBinData1 = rtrim(B::compressIntArray(array ($parentFileNumber, self::$lineA, $parentFileNumber, self::$lineB, $parentFileNumber, $lineC, $thisFileNumber, $lineParent)), PHP_EOL);
        parent::assertTrue(strpos($binData1, $cmpBinData1) !== false);

        $binData2 = file_get_contents(B::getStatic('$_workDir') . self::$_errorLogDir . $thisFileNumber . '.bin');

        $cmpBinData2 = rtrim(B::compressIntArray(array ($thisFileNumber, $line)), PHP_EOL);
        parent::assertTrue(strpos($binData2, $cmpBinData2) !== false);

        $cmpBinData2 = rtrim(B::compressIntArray(array ($thisFileNumber, self::$line1, $thisFileNumber, self::$line2, $thisFileNumber, $line3)), PHP_EOL);
        parent::assertTrue(strpos($binData2, $cmpBinData2) !== false);

        ob_start();
        BU::$exeMode |= B::IGNORING_BREAK_POINT;

        $error->handleError2(E_USER_DEPRECATED, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_USER_NOTICE, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_USER_WARNING, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_USER_ERROR, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_ERROR, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_WARNING, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_PARSE, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_NOTICE, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_CORE_ERROR, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_CORE_WARNING, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_COMPILE_ERROR, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_COMPILE_WARNING, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_STRICT, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_RECOVERABLE_ERROR, '', B::$prependErrorLog, debug_backtrace());

        $error->handleError2(E_DEPRECATED, '', B::$prependErrorLog, debug_backtrace());
    }

    /**
     * @covers \BreakpointDebugging_Error<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Error_InAllCase FUNCTION=handleError2 ID=5.
     */
    function testHandleError2_A()
    {
        BU::markTestSkippedInDebug(); // Because this unit test is the logging check.

        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        $this->_error->handleError2(255, '', B::$prependErrorLog, debug_backtrace());
    }

    /**
     * @covers \BreakpointDebugging_Error<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Error_InAllCase FUNCTION=convertMbString ID=3.
     */
    function testConvertMbString()
    {
        BU::markTestSkippedInDebug(); // Because this unit test is the logging check.
        // SJIS + UTF-8
        B::$prependExceptionLog = "\x95\xB6\x8E\x9A \xE6\x96\x87\xE5\xAD\x97 ";
        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        try {
            B::handleException(new \Exception());
        } catch (\Exception $e) {
            B::$prependExceptionLog = '';
            throw $e;
        }
    }

}

?>
