<?php

/**
 * There is this file to increase speed when does not do error or exception handling.
 *
 * In other words, this file does not cause "__autoload()" because does not read except for error or exception handling.
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012, Hidenori Wasa
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

/**
 * This class do error or exception handling.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_Error
{

    /**
     * @var array Call stack information.
     */
    public $callStackInfo;

    /**
     * @var bool Is logging?
     */
    private $_isLogging;

    /**
     * @var string Mark.
     */
    private $_mark;

    /**
     * @var array HTML tags.
     */
    public $tag;

    /**
     * This method makes HTML tags.
     *
     * @return void
     */
    function __construct()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of the logging.
            $this->_isLogging = true;
            $this->_mark = '#';
            $this->tag['font']['caution'] = '';
            $this->tag['font']['bool'] = '';
            $this->tag['font']['int'] = '';
            $this->tag['font']['float'] = '';
            $this->tag['font']['string'] = '';
            $this->tag['font']['null'] = '';
            $this->tag['font']['resource'] = '';
            $this->tag['font']['=>'] = '';
            $this->tag['/font'] = '';
            $this->tag['small'] = '';
            $this->tag['/small'] = '';
            $this->tag['i'] = '';
            $this->tag['/i'] = '';
            $this->tag['b'] = '';
            $this->tag['/b'] = '';
            $this->tag['pre'] = '';
            $this->tag['/pre'] = PHP_EOL . PHP_EOL;
        } else { // In case of not the logging.
            $this->_isLogging = false;
            $this->_mark = '&diams;';
            $this->tag['font']['caution'] = '<font color=\'#ff0000\'>';
            $this->tag['font']['bool'] = '<font color=\'#75507b\'>';
            $this->tag['font']['int'] = '<font color=\'#4e9a06\'>';
            $this->tag['font']['float'] = '<font color=\'#f57900\'>';
            $this->tag['font']['string'] = '<font color=\'#cc0000\'>';
            $this->tag['font']['null'] = '<font color=\'#3465a4\'>';
            $this->tag['font']['resource'] = '<font color=\'#8080ff\'>';
            $this->tag['font']['=>'] = '<font color=\'#888a85\'>';
            $this->tag['/font'] = '</font>';
            $this->tag['small'] = '<small>';
            $this->tag['/small'] = '</small>';
            $this->tag['i'] = '<i>';
            $this->tag['/i'] = '</i>';
            $this->tag['b'] = '<b>';
            $this->tag['/b'] = '</b>';
            $this->tag['pre'] = '<pre class=\'xdebug-var-dump\' dir=\'ltr\'>';
            $this->tag['/pre'] = '</pre>';
        }
    }

    /**
     * This is to avoid recursive method call inside error handling or exception handling.
     * This method changes it to unify multibyte character strings such as system-output or user input, and this returns UTF-8 multibyte character strings.
     * This is security for not mixing a character sets.
     *
     * @param string $string Character string which may be not UTF8.
     *
     * @return string UTF8 character string.
     */
    private function _convertMbString($string)
    {
        B::internalAssert(func_num_args() === 1);
        B::internalAssert(is_string($string));

        static $onceFlag = true;

        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8' || $charSet === 'ASCII') {
            return $string;
        } else if ($charSet === false) {
            $message = 'This isn\'t single character sets.';
            if ($onceFlag) {
                $onceFlag = false;
                if ($this->outputErrorCallStackLog2('E_USER_ERROR', $message)) {
                    BreakpointDebugging_breakpoint($message, $this->callStackInfo);
                }
                exit(-1);
            }
            return "### ERROR: {$message} ###";
        }
        return mb_convert_encoding($string, 'UTF-8', $charSet);
    }

    /**
     * Add function-values to log.
     *
     * @param mixed  &$pTmpLog2  Error temporary log pointer.
     * @param mixed  &$pTmpLog   Error temporary log pointer.
     * @param bool   &$onceFlag2 False means logging parameter header.
     * @param string $func       Function name of call stack.
     * @param string $class      Class name of call stack.
     * @param string $line       Line number of call stack.
     * @param string $tabs       Tabs to indent.
     *
     * @return void
     */
    private function _addFunctionValuesToLog(&$pTmpLog2, &$pTmpLog, &$onceFlag2, $func, $class, $line, $tabs = '')
    {
        global $_BreakpointDebugging;
        $paramNumber = func_num_args();

        $valuesToTraceFiles = &$_BreakpointDebugging->valuesToTrace;
        $onceFlag = false;
        if (!is_array($valuesToTraceFiles)) {
            return;
        }
        foreach ($valuesToTraceFiles as $valuesToTraceLines) {
            foreach ($valuesToTraceLines as $trace) {
                array_key_exists('function', $trace) ? $callFunc = $trace['function'] : $callFunc = '';
                array_key_exists('class', $trace) ? $callClass = $trace['class'] : $callClass = '';
                if ($callFunc === '' && $callClass === '' && $paramNumber === 7) {
                    continue;
                }
                if ($func === $callFunc && $class === $callClass) {
                    if ($onceFlag2) {
                        $onceFlag2 = false;
                        array_key_exists('file', $trace) ? $callFile = $trace['file'] : $callFile = '';
                        $this->_addParameterHeaderToLog($pTmpLog, $callFile, $line, $func, $class);
                    }
                    if ($onceFlag) {
                        $this->_logBufferWriting($pTmpLog2, PHP_EOL . $tabs . "\t,");
                    } else {
                        $this->_logBufferWriting($pTmpLog2, PHP_EOL . $tabs . $this->_mark . 'Function values ==>');
                        $onceFlag = true;
                    }
                    // Analyze values part of trace array, and return character string.
                    $this->_searchDebugBacktraceArgsToString($pTmpLog2, $trace['values'], strlen($tabs) + 1);
                }
            }
        }
    }

    /**
     * This method builds array information.
     *
     * @param mixed &$pTmpLog  Error temporary log pointer.
     * @param mixed $paramName Parameter name or number.
     * @param array $array     The array to reflect.
     * @param int   $tabNumber The tab number to indent.
     *
     * @return void
     */
    private function _reflectArray(&$pTmpLog, $paramName, $array, $tabNumber = 1)
    {
        B::internalAssert(func_num_args() <= 3);
        B::internalAssert(is_string($paramName) || is_int($paramName));
        B::internalAssert(is_array($array));
        B::internalAssert(is_int($tabNumber));

        $isOverMaxLogElementNumber = false;
        if (count($array) > B::$maxLogElementNumber) {
            $isOverMaxLogElementNumber = true;
            $array = array_slice($array, 0, B::$maxLogElementNumber, true);
        }
        $tabs = str_repeat("\t", $tabNumber);
        $onceFlag2 = false;
        $pTmpLog2 = $this->_logPointerOpening();
        // For "Exception::$trace".
        $this->_outputFixedFunctionToLogging($array, $pTmpLog2, $onceFlag2, $func, $class, '', "\t" . $tabs);
        $this->_addFunctionValuesToLog($pTmpLog2, $pTmpLog, $onceFlag2, $func, $class, '', "\t" . $tabs);

        $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tag['font']['=>'] . ' => ' . $this->tag['/font'] . $this->tag['b'] . 'array' . $this->tag['/b'] . ' (');
        // Beyond max log param nesting level.
        if ($tabNumber >= B::$maxLogParamNestingLevel) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
        } else {
            foreach ($array as $paramName => $paramValue) {
                if ($paramName === 'GLOBALS') {
                    continue;
                }
                if (is_string($paramName)) {
                    $paramName = '\'' . $paramName . '\'';
                }
                $this->_getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
            }
            if ($isOverMaxLogElementNumber !== false) {
                $tmp = PHP_EOL . $tabs . "\t\t.";
                $this->_logBufferWriting($pTmpLog, $tmp . $tmp . $tmp);
            }
        }
        $this->_logBufferWriting($pTmpLog2, PHP_EOL . $tabs . ')');
        assert($pTmpLog2 !== null);
        $this->_logCombination($pTmpLog, $pTmpLog2);
    }

    /**
     * This method builds property and constant information inside class difinition.
     *
     * @param mixed  &$pTmpLog  Error temporary log pointer.
     * @param mixed  $paramName Parameter name or number.
     * @param object $object    The object to reflect.
     * @param int    $tabNumber The tab number to indent.
     *
     * @return void
     */
    private function _reflectObject(&$pTmpLog, $paramName, $object, $tabNumber = 1)
    {
        $className = get_class($object);

        B::internalAssert(func_num_args() <= 3);
        B::internalAssert(is_string($paramName) || is_int($paramName));
        B::internalAssert(is_string($className));
        B::internalAssert(is_object($object));
        B::internalAssert(is_int($tabNumber));

        $tabs = str_repeat("\t", $tabNumber);
        $classReflection = new ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        $constants = $classReflection->getConstants();

        $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tag['font']['=>'] . ' => ' . $this->tag['/font'] . $this->tag['b'] . 'class ' . $this->tag['/b'] . $this->tag['i'] . $className . $this->tag['/i'] . PHP_EOL . $tabs . '{');
        // Beyond max log param nesting level.
        if ($tabNumber >= B::$maxLogParamNestingLevel) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
        } else {
            foreach ($constants as $constName => $constValue) {
                $this->_getTypeAndValue($pTmpLog, $this->tag['i'] . 'const ' . $this->tag['/i'] . $constName, $constValue, $tabNumber + 1);
            }
            count($constants) ? $this->_logBufferWriting($pTmpLog, PHP_EOL) : null;
            foreach ($propertyReflections as $propertyReflection) {
                $propertyReflection->setAccessible(true);
                $paramName = $this->tag['i'];
                $paramName .= $propertyReflection->isPublic() ? 'public ' : '';
                $paramName .= $propertyReflection->isPrivate() ? 'private ' : '';
                $paramName .= $propertyReflection->isProtected() ? 'protected ' : '';
                $paramName .= $propertyReflection->isStatic() ? 'static ' : '';
                $paramName .= $this->tag['/i'];
                $paramName .= '$' . $propertyReflection->getName();
                if ($propertyReflection->isStatic()) {
                    $paramValue = $propertyReflection->getValue($propertyReflection);
                } else {
                    $paramValue = $propertyReflection->getValue($object);
                }
                $this->_getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
            }
        }
        $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . '}');
    }

    /**
     * This is Called from exception handler.
     *
     * @param object $pException Exception info.
     * @param string $prependLog This prepend this parameter logging.
     *
     * @return void
     */
    function exceptionHandler2($pException, $prependLog)
    {
        B::internalAssert(func_num_args() === 2);
        B::internalAssert($pException instanceof Exception);
        B::internalAssert(is_string($prependLog));

        $errorMessage = $this->_convertMbString($pException->getMessage());
        $prependLog = $this->_convertMbString($prependLog);

        $this->callStackInfo = $pException->getTrace();
        // Add scope of start page file.
        $this->callStackInfo[] = array();
        if ($this->outputErrorCallStackLog2(get_class($pException), $errorMessage, $prependLog)) {
            BreakpointDebugging_breakpoint($pException->getMessage(), $this->callStackInfo);
        }
    }

    /**
     * This is Called from error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     * @param string $prependLog   This prepend this parameter logging.
     *
     * @return bool Did the error handling end?
     */
    function errorHandler2($errorNumber, $errorMessage, $prependLog)
    {
        B::internalAssert(func_num_args() === 3);
        B::internalAssert(is_int($errorNumber));
        B::internalAssert(is_string($errorMessage));
        B::internalAssert(is_string($prependLog));

        global $_BreakpointDebugging_EXE_MODE;

        $errorMessage = $this->_convertMbString($errorMessage);
        $prependLog = $this->_convertMbString($prependLog);
        // This creates error log.
        switch ($errorNumber) {
            case E_USER_DEPRECATED:
                $errorKind = 'E_USER_DEPRECATED';
                break;
            case E_USER_NOTICE:
                $errorKind = 'E_USER_NOTICE';
                break;
            case E_USER_WARNING:
                $errorKind = 'E_USER_WARNING';
                break;
            case E_USER_ERROR:
                $errorKind = 'E_USER_ERROR';
                break;
            case E_ERROR:
                $errorKind = 'E_ERROR';
                break;
            case E_WARNING:
                $errorKind = 'E_WARNING';
                break;
            case E_PARSE:
                $errorKind = 'E_PARSE';
                break;
            case E_NOTICE:
                $errorKind = 'E_NOTICE';
                break;
            case E_CORE_ERROR:
                $errorKind = 'E_CORE_ERROR';
                break;
            case E_CORE_WARNING:
                $errorKind = 'E_CORE_WARNING';
                break;
            case E_COMPILE_ERROR:
                $errorKind = 'E_COMPILE_ERROR';
                break;
            case E_COMPILE_WARNING:
                $errorKind = 'E_COMPILE_WARNING';
                break;
            case E_STRICT:
                $errorKind = 'E_STRICT';
                break;
            case E_RECOVERABLE_ERROR:
                $errorKind = 'E_RECOVERABLE_ERROR';
                break;
            case E_DEPRECATED:
                $errorKind = 'E_DEPRECATED';
                break;
            default:
                $errorKind = 'E_UNKNOWN';
                BreakpointDebugging_breakpoint($errorMessage);
                break;
        }
        $this->callStackInfo = debug_backtrace(true);
        unset($this->callStackInfo[0], $this->callStackInfo[1]);
        // Add scope of start page file.
        $this->callStackInfo[] = array();
        if ($this->outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog)) {
            BreakpointDebugging_breakpoint($errorMessage, $this->callStackInfo);
            return true;
        }
        return false; // With system log.
    }

    /**
     * Add parameter header to error log.
     *
     * @param mixed  &$pTmpLog Error temporary log pointer.
     * @param string $file     File name.
     * @param string $line     Line number.
     * @param string $func     Function name.
     * @param string $class    Class name.
     *
     * @return void
     */
    private function _addParameterHeaderToLog(&$pTmpLog, $file, $line, $func, $class)
    {
        if ($file) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error file =======>' . $this->tag['font']['string'] . '\'' . $file . '\'' . $this->tag['/font']);
        }
        if ($line) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error line =======>' . $this->tag['font']['int'] . $line . $this->tag['/font']);
        }
        if ($class) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error class ======>' . $this->tag['i'] . $class . $this->tag['/i']);
        }
        if ($func) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error function ===>' . $this->tag['i'] . $func . $this->tag['/i'] . '( ');
        }
    }

    /**
     * Output fixed-function to logging.
     *
     * @param array  $backTrace  Call stack.
     * @param mixed  &$pTmpLog   Error temporary log pointer.
     * @param bool   &$onceFlag2 False means logging parameter header.
     * @param string &$func      Function name of call stack.
     * @param string &$class     Class name of call stack.
     * @param string $line       Line number of call stack.
     * @param string $tabs       Tabs to indent.
     *
     * @return void
     */
    private function _outputFixedFunctionToLogging($backTrace, &$pTmpLog, &$onceFlag2, &$func, &$class, $line, $tabs = '')
    {
        global $_BreakpointDebugging;
        $paramNumber = func_num_args();

        array_key_exists('function', $backTrace) ? $func = $backTrace['function'] : $func = '';
        array_key_exists('class', $backTrace) ? $class = $backTrace['class'] : $class = '';
        if (is_array($_BreakpointDebugging->notFixedLocations)) {
            foreach ($_BreakpointDebugging->notFixedLocations as $notFixedLocation) {
                array_key_exists('function', $notFixedLocation) ? $noFixFunc = $notFixedLocation['function'] : $noFixFunc = '';
                array_key_exists('class', $notFixedLocation) ? $noFixClass = $notFixedLocation['class'] : $noFixClass = '';
                // $notFixedLocation of file scope is "$noFixFunc === '' && $noFixClass === '' && $paramNumber === 6".
                if ($noFixFunc === '' && $noFixClass === '' && $paramNumber === 7) {
                    continue;
                }
                if ($func === $noFixFunc && $class === $noFixClass) {
                    $marks = str_repeat($this->_mark, 10);
                    $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . $this->tag['font']['caution'] . $marks . $this->tag['b'] . ' This function has been not fixed. ' . $this->tag['/b'] . $marks . $this->tag['/font']);
                    if ($onceFlag2) {
                        $onceFlag2 = false;
                        array_key_exists('file', $notFixedLocation) ? $noFixFile = $notFixedLocation['file'] : $noFixFile = '';
                        $this->_addParameterHeaderToLog($pTmpLog, $noFixFile, $line, $func, $class);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Build error call stack log except "E_NOTICE".
     *
     * @param string $errorKind    Error kind.
     * @param string $errorMessage Error message.
     * @param string $prependLog   This prepend this parameter logging.
     *
     * @return bool Is break?
     */
    function outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog = '')
    {
        global $_BreakpointDebugging_EXE_MODE;

        B::internalAssert(func_num_args() <= 6);
        B::internalAssert(is_string($errorKind));
        B::internalAssert(is_string($errorMessage));
        B::internalAssert(is_array($this->callStackInfo));
        B::internalAssert(is_string($prependLog));

        if (!$this->_isLogging) {
            $errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
            $prependLog = htmlspecialchars($prependLog, ENT_QUOTES, 'UTF-8');
        }
        // We had better debug by breakpoint than the display screen in case of "E_NOTICE".
        // Also, we are possible to skip "E_NOTICE" which is generated while debugging execution is stopping.
        // Moreover, those "E_NOTICE" doesn't stop at breakpoint.
        if ($errorKind === 'E_NOTICE') {
            return;
        }
        // Make error log file.
        $pLog = fopen(B::$phpErrorLogFilePath, 'a');
        fclose($pLog);
        // Lock error log file.
        $lockByFileExisting = new \BreakpointDebugging_LockByFileExisting(B::$phpErrorLogFilePath);
        $lockByFileExisting->lock();
        $tmp = date('[Y-m-d H:i:s]') . PHP_EOL;
        $dummy = null;
        $this->_logBufferWriting($dummy, $this->tag['pre'] . $prependLog);
        // Create error log from the argument.
        $tmp .= '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' .
        PHP_EOL . $this->_mark . 'Error kind =======>' . $this->tag['font']['string'] . '\'' . $errorKind . '\'' . $this->tag['/font'] .
        PHP_EOL . $this->_mark . 'Error message ====>' . $this->tag['font']['string'] . '\'' . $errorMessage . '\'' . $this->tag['/font'] .
        PHP_EOL;
        $this->_logBufferWriting($dummy, $tmp);
        // Search array which debug_backtrace() or getTrace() returns, and add a parametric information.
        foreach ($this->callStackInfo as $backtraceArrays) {
            $onceFlag2 = true;
            $pTmpLog2 = $this->_logPointerOpening();
            array_key_exists('file', $backtraceArrays) ? $file = $backtraceArrays['file'] : $file = '';
            array_key_exists('line', $backtraceArrays) ? $line = $backtraceArrays['line'] : $line = '';
            $this->_outputFixedFunctionToLogging($backtraceArrays, $dummy, $onceFlag2, $func, $class, $line);
            if (array_key_exists('args', $backtraceArrays)) {
                // Analyze parameters part of trace array, and return character string.
                $this->_searchDebugBacktraceArgsToString($pTmpLog2, $backtraceArrays['args']);
                $this->_logBufferWriting($pTmpLog2, PHP_EOL . ');');
            }
            $this->_addFunctionValuesToLog($pTmpLog, $dummy, $onceFlag2, $func, $class, $line);
            if ($onceFlag2) {
                $this->_addParameterHeaderToLog($dummy, $file, $line, $func, $class);
            }
            $this->_logBufferWriting($pTmpLog2, PHP_EOL);
            $this->_logWriting($pTmpLog2);
        }
        $this->_logBufferWriting($dummy, '//////////////////////////////// CALL STACK END ////////////////////////////////');
        $this->_logBufferWriting($dummy, $this->tag['/pre']);
        // Unlock error log file.
        $lockByFileExisting->unlock();
        if ($_BreakpointDebugging_EXE_MODE & B::RELEASE) {
            return false;
        }
        return true;
    }

    /**
     * Get parameter type and value.
     *
     * @param mixed &$pTmpLog   Error temporary log pointer.
     * @param mixed $paramName  Parameter name or number.
     * @param mixed $paramValue Parameter value.
     * @param int   $tabNumber  The tab number to indent.
     *
     * @return void
     */
    private function _getTypeAndValue(&$pTmpLog, $paramName, $paramValue, $tabNumber)
    {
        B::internalAssert(func_num_args() === 4);

        if (is_array($paramValue)) {
            if ($paramName === 'GLOBALS') {
                return;
            }
            $this->_reflectArray($pTmpLog, $paramName, $paramValue, $tabNumber);
            return;
        } else if (is_object($paramValue)) {
            $this->_reflectObject($pTmpLog, $paramName, $paramValue, $tabNumber);
            return;
        }

        $prefix = PHP_EOL . str_repeat("\t", $tabNumber);
        $this->_logBufferWriting($pTmpLog, $prefix . $paramName . $this->tag['font']['=>'] . ' => ' . $this->tag['/font']);
        $tag = function ($self, $type, $paramValue) {
            return $self->tag['small'] . $type . $self->tag['/small'] . ' ' . $self->tag['font'][$type] . $paramValue . $self->tag['/font'];
        };
        if (is_null($paramValue)) {
            $this->_logBufferWriting($pTmpLog, $tag($this, 'null', 'null'));
        } else if (is_bool($paramValue)) {
            $this->_logBufferWriting($pTmpLog, $tag($this, 'bool', $paramValue ? 'true' : 'false'));
        } else if (is_int($paramValue)) {
            $this->_logBufferWriting($pTmpLog, $tag($this, 'int', $paramValue));
        } else if (is_float($paramValue)) {
            $this->_logBufferWriting($pTmpLog, $tag($this, 'float', $paramValue));
        } else if (is_string($paramValue)) {
            $paramValue = $this->_convertMbString($paramValue);
            $strlen = strlen($paramValue);
            $isOverMaxLogStringSize = false;
            if ($strlen > B::$maxLogStringSize) {
                $isOverMaxLogStringSize = true;
                $paramValue = substr($paramValue, 0, B::$maxLogStringSize);
            }
            $paramValue = '"' . $paramValue . '"';
            if (!$this->_isLogging) {
                $paramValue = htmlspecialchars($paramValue, ENT_QUOTES, 'UTF-8');
            }
            if ($isOverMaxLogStringSize === false) {
                $this->_logBufferWriting($pTmpLog, $tag($this, 'string', $paramValue) . $this->tag['i'] . ' (length=' . $strlen . ')' . $this->tag['/i']);
            } else {
                $this->_logBufferWriting($pTmpLog, $tag($this, 'string', $paramValue) . $this->tag['i'] . '... (length=' . $strlen . ')' . $this->tag['/i']);
            }
        } else if (is_resource($paramValue)) {
            $tmp = $this->tag['b'] . 'resource' . $this->tag['/b'] . ' ' .
            $this->tag['i'] . get_resource_type($paramValue) . $this->tag['/i'] . ' ' .
            $this->tag['font']['resource'] . $paramValue . $this->tag['/font'];
            $this->_logBufferWriting($pTmpLog, $tmp);
        } else {
            B::internalAssert(false);
        }
    }

    /**
     * Analyze parameter part of back trace array, and return string.
     *
     * @param mixed &$pTmpLog        Error temporary log pointer.
     * @param array $backtraceParams Back trace parameters.
     * @param int   $tabNumber       The tab number to indent.
     *
     * @return void
     */
    private function _searchDebugBacktraceArgsToString(&$pTmpLog, $backtraceParams, $tabNumber = 1)
    {
        B::internalAssert(func_num_args() <= 2);

        $isFirst = true;
        $paramCount = 0;
        foreach ($backtraceParams as $paramName => $paramValue) {
            $paramCount++;
            if ($paramCount > B::$maxLogElementNumber) {
                $tmp = PHP_EOL . str_repeat("\t", $tabNumber);
                $this->_logBufferWriting($pTmpLog, $tmp . ',');
                $tmp = $tmp . "\t.";
                $this->_logBufferWriting($pTmpLog, $tmp . $tmp . $tmp);
                break;
            }
            if ($isFirst) {
                $isFirst = false;
            } else {
                $this->_logBufferWriting($pTmpLog, PHP_EOL . str_repeat("\t", $tabNumber) . ',');
            }
            $this->_getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber);
        }
    }

    /**
     * Open error-log-pointer.
     *
     * @return mixed Error log pointer.
     */
    private function _logPointerOpening()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE & (B::LOCAL_DEBUG | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of local.
            return array();
        } else { // In case of not local debug.
            return tmpfile();
        }
    }

    /**
     * Close error-log-pointer.
     *
     * @param mixed &$pTmpLog Error temporary log pointer.
     *
     * @return void
     */
    private function _logPointerClosing(&$pTmpLog)
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) { // In case of remote.
            fclose($pTmpLog);
        }
        $pTmpLog = null;
    }

    /**
     * Error log writing.
     * This reduces amount of memory consumption in case of production server.
     *
     * @param mixed &$pTmpLog Error temporary log pointer.
     *
     * @return void
     */
    private function _logWriting(&$pTmpLog)
    {
        B::internalAssert(func_num_args() === 1);

        global $_BreakpointDebugging_EXE_MODE;

        switch ($_BreakpointDebugging_EXE_MODE) {
            case B::RELEASE:
                $pLog = fopen(B::$phpErrorLogFilePath, 'a');
                rewind($pTmpLog);
                while (!feof($pTmpLog)) {
                    fwrite($pLog, fread($pTmpLog, 4096));
                }
                fclose($pLog);
                // Delete temporary file.
                fclose($pTmpLog);
                break;
            case B::LOCAL_DEBUG:
                foreach ($pTmpLog as $log) {
                    echo $log;
                }
                break;
            case B::REMOTE_DEBUG:
                rewind($pTmpLog);
                while (!feof($pTmpLog)) {
                    echo fread($pTmpLog, 4096);
                }
                // Delete temporary file.
                fclose($pTmpLog);
                break;
            case B::LOCAL_DEBUG_OF_RELEASE:
                $pLog = fopen(B::$phpErrorLogFilePath, 'a');
                foreach ($pTmpLog as $log) {
                    fwrite($pLog, $log);
                }
                fclose($pLog);
                break;
            default:
                B::internalAssert(false);
        }
        $pTmpLog = null;
    }

    /**
     * Error log buffer writing.
     * This reduces amount of memory consumption in case of production server.
     *
     * @param mixed  &$pLogBuffer Error log buffer pointer.
     * @param string $log         Error log.
     *
     * @return void
     */
    private function _logBufferWriting(&$pLogBuffer, $log)
    {
        B::internalAssert(is_array($pLogBuffer) || $pLogBuffer === null);

        global $_BreakpointDebugging_EXE_MODE;

        switch ($_BreakpointDebugging_EXE_MODE) {
            case B::RELEASE:
                if ($pLogBuffer === null) {
                    $pLog = fopen(B::$phpErrorLogFilePath, 'a');
                    fwrite($pLog, $log);
                    fclose($pLog);
                } else {
                    fwrite($pLogBuffer, $log);
                }
                break;
            case B::LOCAL_DEBUG:
                if ($pLogBuffer === null) {
                    echo $log;
                } else {
                    $pLogBuffer[] = $log;
                }
                break;
            case B::REMOTE_DEBUG:
                if ($pLogBuffer === null) {
                    echo $log;
                } else {
                    fwrite($pLogBuffer, $log);
                }
                break;
            case B::LOCAL_DEBUG_OF_RELEASE:
                if ($pLogBuffer === null) {
                    $pLog = fopen(B::$phpErrorLogFilePath, 'a');
                    fwrite($pLog, $log);
                    fclose($pLog);
                } else {
                    $pLogBuffer[] = $log;
                }
                break;
            default:
                assert(false);
        }
    }

    /**
     * Error log combination.
     * This reduces amount of memory consumption in case of production server.
     *
     * @param mixed &$pTmpLog  Error temporary log pointer.
     * @param mixed &$pTmpLog2 Error temporary log pointer.
     *
     * @return void
     */
    private function _logCombination(&$pTmpLog, &$pTmpLog2)
    {
        global $_BreakpointDebugging_EXE_MODE;

        switch ($_BreakpointDebugging_EXE_MODE) {
            case B::RELEASE:
                rewind($pTmpLog2);
                if ($pTmpLog === null) {
                    $pLog = fopen(B::$phpErrorLogFilePath, 'a');
                    while (!feof($pTmpLog2)) {
                        fwrite($pLog, fread($pTmpLog2, 4096));
                    }
                    fclose($pLog);
                } else {
                    while (!feof($pTmpLog2)) {
                        fwrite($pTmpLog, fread($pTmpLog2, 4096));
                    }
                }
                break;
            case B::LOCAL_DEBUG:
                if ($pTmpLog === null) {
                    echo $pTmpLog2;
                } else if (count($pTmpLog) === 0) {
                    if (count($pTmpLog2) !== 0) {
                        $pTmpLog = $pTmpLog2;
                    }
                } else if (count($pTmpLog2) !== 0) {
                    $pTmpLog = array_merge($pTmpLog, $pTmpLog2);
                }
                break;
            case B::REMOTE_DEBUG:
                rewind($pTmpLog2);
                if ($pTmpLog === null) {
                    while (!feof($pTmpLog2)) {
                        echo fread($pTmpLog2, 4096);
                    }
                } else {
                    while (!feof($pTmpLog2)) {
                        fwrite($pTmpLog, fread($pTmpLog2, 4096));
                    }
                }
                break;
            case B::LOCAL_DEBUG_OF_RELEASE:
                if ($pTmpLog === null) {
                    $pLog = fopen(B::$phpErrorLogFilePath, 'a');
                    foreach ($pTmpLog2 as $log) {
                        fwrite($pLog, $log);
                    }
                    fclose($pLog);
                } else if (count($pTmpLog) === 0) {
                    if (count($pTmpLog2) !== 0) {
                        $pTmpLog = $pTmpLog2;
                    }
                } else if (count($pTmpLog2) !== 0) {
                    $pTmpLog = array_merge($pTmpLog, $pTmpLog2);
                }
                break;
            default:
                assert(false);
        }
        $this->_logPointerClosing($pTmpLog2);
    }

}

?>
