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
     * @var array Call stack information
     */
    public $callStackInfo;

    /**
     * @var bool Is logging?
     */
    private $_isLogging;

    /**
     * @var string Mark
     */
    private $_mark;

    /**
     * @var array HTML tags
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
            $this->tag['/pre'] = '';
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
     * And this becomes possible to assert inside error handling.
     *
     * @param bool $expression Judgment expression
     *
     * @return void
     */
    private function _assert($expression)
    {
        global $_BreakpointDebugging_EXE_MODE;
        static $onceFlag = true;

        if (!($_BreakpointDebugging_EXE_MODE & B::RELEASE)) { // In case of not release
            if ($onceFlag) {
                $onceFlag = false;
                if (func_num_args() !== 1 || !is_bool($expression) || $expression === false) {
                    B::errorHandler(E_USER_WARNING, 'Assertion failed.');
                    exit(-1);
                }
            }
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
        $this->_assert(func_num_args() === 1);
        $this->_assert(is_string($string));
        static $onceFlag = true;

        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8' || $charSet === 'ASCII') {
            return $string;
        } else if ($charSet === false) {
            $message = 'This isn\'t single character sets.';
            if ($onceFlag) {
                $onceFlag = false;
                $log = $this->buildErrorCallStackLog2('E_USER_ERROR', $message);
                if ($this->_errorLog($log)) {
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
     * @param string &$logBuffer Error log buffer
     * @param string &$log       Error log
     * @param bool   &$onceFlag2 False means logging parameter header.
     * @param string $func       Function name of call stack
     * @param string $class      Class name of call stack
     * @param string $line       Line number of call stack
     * @param string $tabs       Tabs to indent
     *
     * @return void
     */
    private function _addFunctionValuesToLog(&$logBuffer, &$log, &$onceFlag2, $func, $class, $line, $tabs = '')
    {
        global $_BreakpointDebugging;
        $paramNumber = func_num_args();

        $valuesToTraceFiles = &$_BreakpointDebugging->valuesToTrace;
        $onceFlag = false;
        foreach ($valuesToTraceFiles as $valuesToTraceLines) {
            foreach ($valuesToTraceLines as $trace) {
                array_key_exists('function', $trace) ? $callFunc = $trace['function'] : $callFunc = '';
                array_key_exists('class', $trace) ? $callClass = $trace['class'] : $callClass = '';
                //if ($callFunc === '' && $callClass === '' && $paramNumber === 8) {
                if ($callFunc === '' && $callClass === '' && $paramNumber === 7) {
                    continue;
                }
                if ($func === $callFunc && $class === $callClass) {
                    if ($onceFlag2) {
                        $onceFlag2 = false;
                        array_key_exists('file', $trace) ? $callFile = $trace['file'] : $callFile = '';
                        $this->_addParameterHeaderToLog($log, $callFile, $line, $func, $class);
                    }
                    if ($onceFlag) {
                        $logBuffer .= PHP_EOL . $tabs . "\t,";
                    } else {
                        $logBuffer .= PHP_EOL . $tabs . $this->_mark . 'Function values ==>';
                        $onceFlag = true;
                    }
                    // Analyze values part of trace array, and return character string.
                    $logBuffer .= $this->_searchDebugBacktraceArgsToString($trace['values'], strlen($tabs) + 1);
                }
            }
        }
    }

    /**
     * This method builds array information.
     *
     * @param mixed $paramName Parameter name or number
     * @param array $array     The array to reflect
     * @param int   $tabNumber The tab number to indent
     *
     * @return string Array information.
     */
    private function _reflectArray($paramName, $array, $tabNumber = 1)
    {
        $this->_assert(func_num_args() <= 3);
        $this->_assert(is_string($paramName) || is_int($paramName));
        $this->_assert(is_array($array));
        $this->_assert(is_int($tabNumber));

        $isOverMaxLogElementNumber = false;
        if (count($array) > B::$maxLogElementNumber) {
            $isOverMaxLogElementNumber = true;
            $array = array_slice($array, 0, B::$maxLogElementNumber, true);
        }
        $tabs = str_repeat("\t", $tabNumber);
        $onceFlag2 = false;
        $this->_outputFixedFunctionToLogging($array, $log, $onceFlag2, $func, $class, '', $tabs);
        $this->_addFunctionValuesToLog($logBuffer, $log, $onceFlag2, $func, $class, '', "\t" . $tabs);

        $log .= PHP_EOL . $tabs . $paramName . $this->tag['font']['=>'] . ' => ' . $this->tag['/font'] . $this->tag['b'] . 'array' . $this->tag['/b'] . ' (';
        // Beyond max log param nesting level
        if ($tabNumber >= B::$maxLogParamNestingLevel) {
            $log .= PHP_EOL . $tabs . "\t...";
        } else {
            foreach ($array as $paramName => $paramValue) {
                if ($paramName === 'GLOBALS') {
                    continue;
                }
                if (is_string($paramName)) {
                    $paramName = '\'' . $paramName . '\'';
                }
                $log .= $this->_getTypeAndValue($paramName, $paramValue, $tabNumber + 1);
            }
            if ($isOverMaxLogElementNumber !== false) {
                $tmp = PHP_EOL . $tabs . "\t\t.";
                $log .= $tmp . $tmp . $tmp;
            }
        }
        $log .= $logBuffer .
        PHP_EOL . $tabs . ')';
        return $log;
    }

    /**
     * This method builds property and constant information inside class difinition.
     *
     * @param mixed  $paramName Parameter name or number.
     * @param object $object    The object to reflect.
     * @param int    $tabNumber The tab number to indent.
     *
     * @return string Object information.
     */
    private function _reflectObject($paramName, $object, $tabNumber = 1)
    {
        $className = get_class($object);

        $this->_assert(func_num_args() <= 3);
        $this->_assert(is_string($paramName) || is_int($paramName));
        $this->_assert(is_string($className));
        $this->_assert(is_object($object));
        $this->_assert(is_int($tabNumber));

        $tabs = str_repeat("\t", $tabNumber);
        $classReflection = new ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        $constants = $classReflection->getConstants();

        $log = PHP_EOL . $tabs . $paramName . $this->tag['font']['=>'] . ' => ' . $this->tag['/font'] . $this->tag['b'] . 'class ' . $this->tag['/b'] . $this->tag['i'] . $className . $this->tag['/i'] .
        PHP_EOL . $tabs . '{';
        // Beyond max log param nesting level
        if ($tabNumber >= B::$maxLogParamNestingLevel) {
            $log .= PHP_EOL . $tabs . "\t...";
        } else {
            foreach ($constants as $constName => $constValue) {
                $log .= $this->_getTypeAndValue($this->tag['i'] . 'const ' . $this->tag['/i'] . $constName, $constValue, $tabNumber + 1);
            }
            count($constants) ? $log .= PHP_EOL : null;
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
                $log .= $this->_getTypeAndValue($paramName, $paramValue, $tabNumber + 1);
            }
        }
        return $log . PHP_EOL . $tabs . '}';
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
        $this->_assert(func_num_args() === 2);
        $this->_assert($pException instanceof Exception);
        $this->_assert(is_string($prependLog));

        $errorMessage = $this->_convertMbString($pException->getMessage());
        $prependLog = $this->_convertMbString($prependLog);

        $this->callStackInfo = $pException->getTrace();
        // Add scope of start page file.
        $this->callStackInfo[] = array();
        $log = $this->buildErrorCallStackLog2(get_class($pException), $errorMessage, $prependLog);
        if ($this->_errorLog($log)) {
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
        $this->_assert(func_num_args() === 3);
        $this->_assert(is_int($errorNumber));
        $this->_assert(is_string($errorMessage));
        $this->_assert(is_string($prependLog));
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
        $log = $this->buildErrorCallStackLog2($errorKind, $errorMessage, $prependLog);
        if ($this->_errorLog($log)) {
            BreakpointDebugging_breakpoint($errorMessage, $this->callStackInfo);
        }

        if ($_BreakpointDebugging_EXE_MODE & B::RELEASE) { // In case of release
            return false; // With system log
        }
        return true;
    }

    /**
     * Add parameter header to error log
     *
     * @param string &$log  Error log
     * @param string $file  File name
     * @param string $line  Line number
     * @param string $func  Function name
     * @param string $class Class name
     *
     * @return void
     */
    private function _addParameterHeaderToLog(&$log, $file, $line, $func, $class)
    {
        if ($file) {
            $log .= PHP_EOL . $this->_mark . 'Error file =======>' . $this->tag['font']['string'] . '\'' . $file . '\'' . $this->tag['/font'];
        }
        if ($line) {
            $log .= PHP_EOL . $this->_mark . 'Error line =======>' . $this->tag['font']['int'] . $line . $this->tag['/font'];
        }
        if ($class) {
            $log .= PHP_EOL . $this->_mark . 'Error class ======>' . $this->tag['i'] . $class . $this->tag['/i'];
        }
        if ($func) {
            $log .= PHP_EOL . $this->_mark . 'Error function ===>' . $this->tag['i'] . $func . $this->tag['/i'] . '( ';
        }
    }

    /**
     * Output fixed-function to logging
     *
     * @param array  $backTrace  Call stack
     * @param string &$log       Error log
     * @param bool   &$onceFlag2 False means logging parameter header.
     * @param string &$func      Function name of call stack
     * @param string &$class     Class name of call stack
     * @param string $line       Line number of call stack
     * @param string $tabs       Tabs to indent
     *
     * @return void
     */
    private function _outputFixedFunctionToLogging($backTrace, &$log, &$onceFlag2, &$func, &$class, $line, $tabs = '')
    {
        global $_BreakpointDebugging;
        $paramNumber = func_num_args();

        array_key_exists('function', $backTrace) ? $func = $backTrace['function'] : $func = '';
        array_key_exists('class', $backTrace) ? $class = $backTrace['class'] : $class = '';
        if (is_array($_BreakpointDebugging->notFixedLocations)) {
            foreach ($_BreakpointDebugging->notFixedLocations as $notFixedLocation) {
                array_key_exists('function', $notFixedLocation) ? $noFixFunc = $notFixedLocation['function'] : $noFixFunc = '';
                array_key_exists('class', $notFixedLocation) ? $noFixClass = $notFixedLocation['class'] : $noFixClass = '';
                if ($noFixFunc === '' && $noFixClass === '' && $paramNumber === 7) {
                    continue;
                }
                if ($func === $noFixFunc && $class === $noFixClass) {
                    $marks = str_repeat($this->_mark, 10);
                    $log .= PHP_EOL . $tabs . $this->tag['font']['caution'] . $marks . $this->tag['b'] . ' This function has been not fixed. ' . $this->tag['/b'] . $marks . $this->tag['/font'];
                    if ($onceFlag2) {
                        $onceFlag2 = false;
                        array_key_exists('file', $notFixedLocation) ? $noFixFile = $notFixedLocation['file'] : $noFixFile = '';
                        $this->_addParameterHeaderToLog($log, $noFixFile, $line, $func, $class);
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
     * @return string Error call stack log.
     */
    function buildErrorCallStackLog2($errorKind, $errorMessage, $prependLog = '')
    {
        $this->_assert(func_num_args() <= 6);
        $this->_assert(is_string($errorKind));
        $this->_assert(is_string($errorMessage));
        $this->_assert(is_array($this->callStackInfo));
        $this->_assert(is_string($prependLog));

        if (!$this->_isLogging) {
            $errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
            $prependLog = htmlspecialchars($prependLog, ENT_QUOTES, 'UTF-8');
        }
        // We had better debug by breakpoint than the display screen in case of "E_NOTICE".
        // Also, we are possible to skip "E_NOTICE" which is generated while debugging execution is stopping.
        // Moreover, those "E_NOTICE" doesn't stop at breakpoint.
        if ($errorKind === 'E_NOTICE') {
            return '';
        }
        // Create error log from the argument.
        $log = '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' .
        PHP_EOL . $this->_mark . 'Error kind =======>' . $this->tag['font']['string'] . '\'' . $errorKind . '\'' . $this->tag['/font'] .
        PHP_EOL . $this->_mark . 'Error message ====>' . $this->tag['font']['string'] . '\'' . $errorMessage . '\'' . $this->tag['/font'] .
        PHP_EOL;
        // Search array which debug_backtrace() or getTrace() returns, and add a parametric information.
        foreach ($this->callStackInfo as $backtraceArrays) {
            $onceFlag2 = true;
            $logBuffer = '';
            array_key_exists('file', $backtraceArrays) ? $file = $backtraceArrays['file'] : $file = '';
            array_key_exists('line', $backtraceArrays) ? $line = $backtraceArrays['line'] : $line = '';
            $this->_outputFixedFunctionToLogging($backtraceArrays, $log, $onceFlag2, $func, $class, $line);
            if (array_key_exists('args', $backtraceArrays)) {
                // Analyze parameters part of trace array, and return character string.
                $logBuffer .= $this->_searchDebugBacktraceArgsToString($backtraceArrays['args']);
                $logBuffer .= PHP_EOL . ');';
            }
            $this->_addFunctionValuesToLog($logBuffer, $log, $onceFlag2, $func, $class, $line);
            if ($onceFlag2) {
                $this->_addParameterHeaderToLog($log, $file, $line, $func, $class);
            }
            $log .= $logBuffer . PHP_EOL;
        }
        $log .= '//////////////////////////////// CALL STACK END ////////////////////////////////';
        return $this->tag['pre'] . $prependLog . $log . $this->tag['/pre'];
    }

    /**
     * Get parameter type and value.
     *
     * @param mixed $paramName  Parameter name or number.
     * @param mixed $paramValue Parameter value.
     * @param int   $tabNumber  The tab number to indent.
     *
     * @return string parameter information.
     */
    private function _getTypeAndValue($paramName, $paramValue, $tabNumber)
    {
        $this->_assert(func_num_args() === 3);

        if (is_array($paramValue)) {
            if ($paramName === 'GLOBALS') {
                return '';
            }
            return $this->_reflectArray($paramName, $paramValue, $tabNumber);
        } else if (is_object($paramValue)) {
            return $this->_reflectObject($paramName, $paramValue, $tabNumber);
        }

        $prefix = PHP_EOL . str_repeat("\t", $tabNumber);
        $log = $prefix . $paramName . $this->tag['font']['=>'] . ' => ' . $this->tag['/font'];
        $tag = function ($self, $type, $paramValue) {
            return $self->tag['small'] . $type . $self->tag['/small'] . ' ' . $self->tag['font'][$type] . $paramValue . $self->tag['/font'];
        };
        if (is_null($paramValue)) {
            $log .= $tag($this, 'null', 'null');
        } else if (is_bool($paramValue)) {
            $log .= $tag($this, 'bool', $paramValue ? 'true' : 'false');
        } else if (is_int($paramValue)) {
            $log .= $tag($this, 'int', $paramValue);
        } else if (is_float($paramValue)) {
            $log .= $tag($this, 'float', $paramValue);
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
                $log .= $tag($this, 'string', $paramValue) . $this->tag['i'] . ' (length=' . $strlen . ')' . $this->tag['/i'];
            } else {
                $log .= $tag($this, 'string', $paramValue) . $this->tag['i'] . '... (length=' . $strlen . ')' . $this->tag['/i'];
            }
        } else if (is_resource($paramValue)) {
            $log .= $this->tag['b'] . 'resource' . $this->tag['/b'] . ' ' .
            $this->tag['i'] . get_resource_type($paramValue) . $this->tag['/i'] . ' ' .
            $this->tag['font']['resource'] . $paramValue . $this->tag['/font'];
        } else {
            $this->_assert(false);
        }
        return $log;
    }

    /**
     * Analyze parameter part of back trace array, and return string.
     *
     * @param array $backtraceParams Back trace parameters.
     * @param int   $tabNumber       The tab number to indent.
     *
     * @return string Part of log lines.
     */
    private function _searchDebugBacktraceArgsToString($backtraceParams, $tabNumber = 1)
    {
        $this->_assert(func_num_args() <= 2);

        $isFirst = true;
        $log = '';
        foreach ($backtraceParams as $paramName => $paramValue) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $log .= PHP_EOL . str_repeat("\t", $tabNumber) . ',';
            }
            $log .= $this->_getTypeAndValue($paramName, $paramValue, $tabNumber);
        }
        return $log;
    }

    /**
     * Log errors.
     *
     * @param string $log Error log.
     *
     * @return Is break?
     */
    private function _errorLog($log)
    {
        $this->_assert(func_num_args() === 1);
        global $_BreakpointDebugging_EXE_MODE;
        $isBreak = false;

        $diplayLog = function ($log) {
            echo <<<EOD
<pre>{$log}</pre>
EOD;
        };
        switch ($_BreakpointDebugging_EXE_MODE) {
        case B::LOCAL_DEBUG_OF_RELEASE:
            $isBreak = true;
        case B::RELEASE:
            if (error_log(PHP_EOL . $log . PHP_EOL) === false) {
                echo '<br/>This failed in the output of the error log.<br/>';
            }
            break;
        case B::LOCAL_DEBUG:
            $diplayLog($log);
            $isBreak = true;
            break;
        case B::REMOTE_DEBUG:
            $diplayLog($log); // This displays error log because breakpoint can not be used on remote debug.
            exit(-1); // This exits immediately to avoid not ending.
        default:
            $this->_assert(false);
        }
        return $isBreak;
    }

}

?>
