<?php

/**
 * There is this file to increase speed when does not do error or exception handling.
 * 
 * In other words, this file does not cause "__autoload()" because does not read except for error or exception handling.
 * 
 * PHP version 5.3
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
    private $_isLogging;
    private $_mark;
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
     * This method builds array information.
     * 
     * @param mixed $paramName Parameter name or number.
     * @param array $array     The array to reflect.
     * @param int   $tabNumber The tab number to indent.
     * 
     * @return string Array information.
     */
    private function _reflectArray($paramName, $array, $tabNumber = 1)
    {
        assert(func_num_args() <= 3);
        assert(is_string($paramName) || is_int($paramName));
        assert(is_array($array));
        assert(is_int($tabNumber));
        
        $tabs = str_repeat("\t", $tabNumber);
        $log = PHP_EOL . $tabs . $paramName . $this->tag['font']['=>'] . ' => ' . $this->tag['/font'] . $this->tag['b'] . 'array' . $this->tag['/b'] . ' (';
        // Beyond max log param nesting level
        if ($tabNumber >= B::$maxLogParamNestingLevel) {
            $log .= PHP_EOL . $tabs . "\t...";
        } else {
            foreach ($array as $paramName => $paramValue) {
                if ($paramName == 'GLOBALS') {
                    continue;
                }
                if (is_string($paramName)) {
                    $paramName = '\'' . $paramName . '\'';
                }
                $log .= $this->_getTypeAndValue($paramName, $paramValue, $tabNumber + 1);
            }
        }
        return $log . PHP_EOL . $tabs . ')';
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
        
        assert(func_num_args() <= 3);
        assert(is_string($paramName) || is_int($paramName));
        assert(is_string($className));
        assert(is_object($object));
        assert(is_int($tabNumber));
        
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
        assert(func_num_args() == 2);
        assert($pException instanceof Exception);
        assert(is_string($prependLog));
        global $_BreakpointDebugging_EXE_MODE;
        
        $prependLog = B::convertMbString($prependLog);
        $trace = $pException->getTrace();
        if (empty($trace)) {
            $backtrace = debug_backtrace();
            $trace = array ($backtrace[count($backtrace) - 1]);
        }
        $log = $this->buildErrorCallStackLog2($pException->getfile(), $pException->getline(), get_class($pException), $pException->getmessage(), $trace, $prependLog);
        
        $diplayLog = function ($log) {
            echo $log;
        };
        switch ($_BreakpointDebugging_EXE_MODE) {
        case B::LOCAL_DEBUG_OF_RELEASE:
            BreakpointDebugging_breakpoint();
        case B::RELEASE:
            $this->_errorLog($log);
            break;
        case B::LOCAL_DEBUG:
            $diplayLog($log);
            BreakpointDebugging_breakpoint();
            break;
        case B::REMOTE_DEBUG:
            $diplayLog($log); // This displays error log because breakpoint can not be used on remote debug.
            exit(-1); // This exits immediately to avoid not ending.
        default:
            assert(false);
        }
    }
    
    /**
     * This is Called from error handler.
     * 
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     * @param string $errorFile    Error file name.
     * @param int    $errorLine    Error file line.
     * @param string $prependLog   This prepend this parameter logging.
     * 
     * @return bool Did the error handling end?
     */
    function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine, $prependLog)
    {
        assert(func_num_args() == 5);
        assert(is_int($errorNumber));
        assert(is_string($errorMessage));
        assert(is_string($errorFile));
        assert(is_int($errorLine));
        assert(is_string($prependLog));
        global $_BreakpointDebugging_EXE_MODE;
        
        $errorMessage = B::convertMbString($errorMessage);
        $prependLog = B::convertMbString($prependLog);
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
            BreakpointDebugging_breakpoint();
            break;
        }
        $log = $this->buildErrorCallStackLog2($errorFile, $errorLine, $errorKind, $errorMessage, debug_backtrace(true), $prependLog);
        $diplayLog = function ($log) {
            echo $log;
        };
        switch ($_BreakpointDebugging_EXE_MODE)
        {
        case B::LOCAL_DEBUG_OF_RELEASE:
            BreakpointDebugging_breakpoint();
        case B::RELEASE:
            $this->_errorLog($log);
            return false; // with system log.
        case B::LOCAL_DEBUG:
            $diplayLog($log);
            BreakpointDebugging_breakpoint();
            return true;
        case B::REMOTE_DEBUG:
            $diplayLog($log); // This displays error log because breakpoint can not be used on remote debug.
            return true;
        default:
            assert(false);
            exit(-1);
        }
    }
    
    /**
     * Build error call stack log except "E_NOTICE".
     * 
     * @param string $errorFile    Error file name.
     * @param int    $errorLine    Error file line.
     * @param int    $errorKind    Error kind.
     * @param string $errorMessage Error message.
     * @param object $backTrace    Back trace for debug.
     * @param string $prependLog   This prepend this parameter logging.
     * 
     * @return string Error call stack log.
     */
    function buildErrorCallStackLog2($errorFile, $errorLine, $errorKind, $errorMessage, $backTrace, $prependLog = '')
    {
        assert(func_num_args() <= 6);
        assert(is_string($errorFile));
        assert(is_int($errorLine));
        assert(is_string($errorKind));
        assert(is_string($errorMessage));
        assert(is_array($backTrace));
        assert(is_string($prependLog));
        global $_BreakpointDebugging;
        
        $errorMessage = B::convertMbString($errorMessage);
        $prependLog = B::convertMbString($prependLog);
        if (!$this->_isLogging) {
            $errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
            $prependLog = htmlspecialchars($prependLog, ENT_QUOTES, 'UTF-8');
        }
        // We had better debug by breakpoint than the display screen in case of "E_NOTICE".
        // Also, we are possible to skip "E_NOTICE" which is generated while debugging execution is stopping.
        // Moreover, those "E_NOTICE" doesn't stop at breakpoint.
        if ($errorKind == 'E_NOTICE') {
            return '';
        }
        // Create error log from the argument.
        $log = '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' .
            PHP_EOL . $this->_mark . 'Error file =======>' . $this->tag['font']['string'] . '\'' . $errorFile . '\'' . $this->tag['/font'] .
            PHP_EOL . $this->_mark . 'Error line =======>' . $this->tag['font']['int'] . $errorLine . $this->tag['/font'] .
            PHP_EOL . $this->_mark . 'Error kind =======>' . $this->tag['font']['string'] . '\'' . $errorKind . '\'' . $this->tag['/font'] .
            PHP_EOL . $this->_mark . 'Error message ====>' . $this->tag['font']['string'] . '\'' . $errorMessage . '\'' . $this->tag['/font'] .
            PHP_EOL;
        // Search array which debug_backtrace() or getTrace() returns, and add a parametric information.
        array_reverse($backTrace, true);
        foreach ($backTrace as $backtraceArrays) {
            array_key_exists('file', $backtraceArrays) ? $file = $backtraceArrays['file'] : $file = '';
            array_key_exists('line', $backtraceArrays) ? $line = $backtraceArrays['line'] : $line = '';
            array_key_exists('function', $backtraceArrays) ? $func = $backtraceArrays['function'] : $func = '';
            array_key_exists('class', $backtraceArrays) ? $class = $backtraceArrays['class'] : $class = '';
            if (is_array($_BreakpointDebugging->notFixedLocations)) {
                foreach ($_BreakpointDebugging->notFixedLocations as $notFixedLocation) {
                    array_key_exists('file', $notFixedLocation) ? $noFixFile = $notFixedLocation['file'] : $noFixFile = '';
                    array_key_exists('function', $notFixedLocation) ? $noFixFunc = $notFixedLocation['function'] : $noFixFunc = '';
                    array_key_exists('class', $notFixedLocation) ? $noFixClass = $notFixedLocation['class'] : $noFixClass = '';
                    if ($file == $noFixFile && $func == $noFixFunc && $class == $noFixClass) {
                        $marks = str_repeat($this->_mark, 10);
                        $log .= PHP_EOL . $this->tag['font']['caution'] . $marks . $this->tag['b'] . ' This function has been not fixed. ' . $this->tag['/b'] . $marks . $this->tag['/font'];
                        break;
                    }
                }
            }
            $log .= PHP_EOL . $this->_mark . 'Error file =======>' . $this->tag['font']['string'] . '\'' . $file . '\'' . $this->tag['/font'];
            $log .= PHP_EOL . $this->_mark . 'Error line =======>' . $this->tag['font']['int'] . $line . $this->tag['/font'];
            $log .= PHP_EOL . $this->_mark . 'Function call ====>' . $this->tag['i'] . $func . $this->tag['/i'] . '( ';
            if (array_key_exists('args', $backtraceArrays)) {
                // Analyze parameters part of trace array, and return character string.
                $log .= $this->_searchDebugBacktraceArgsToString($backtraceArrays['args']);
            }
            $log .= PHP_EOL . ');';
            $log .= PHP_EOL . $this->_mark . 'Function values ==>';
            $valuesToTraceLines = &$_BreakpointDebugging->valuesToTrace;
            $onceFlag = false;
            foreach ($valuesToTraceLines as $valuesToTraceFiles) {
                foreach ($valuesToTraceFiles as $trace) {
                    array_key_exists('file', $trace) ? $callFile = $trace['file'] : $callFile = '';
                    array_key_exists('function', $trace) ? $callFunc = $trace['function'] : $callFunc = '';
                    array_key_exists('class', $trace) ? $callClass = $trace['class'] : $callClass = '';
                    if ($file == $callFile && $func == $callFunc && $class == $callClass) {
                        if ($onceFlag) {
                            $log .= PHP_EOL . "\t,";
                        }
                        $onceFlag = true;
                        // Analyze values part of trace array, and return character string.
                        $log .= $this->_searchDebugBacktraceArgsToString($trace['values']);
                    }
                }
            }
            $log .= PHP_EOL;
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
        assert(func_num_args() == 3);
        
        if (is_array($paramValue)) {
            if ($paramName == 'GLOBALS') {
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
            $paramValue = B::convertMbString($paramValue);
            $strlen = strlen($paramValue);
            $paramValue = '"' . $paramValue . '"';
            if (!$this->_isLogging) {
                $paramValue = htmlspecialchars($paramValue, ENT_QUOTES, 'UTF-8');
            }
            $log .= $tag($this, 'string', $paramValue) . $this->tag['i'] . ' (length=' . $strlen . ')' . $this->tag['/i'];
        } else if (is_resource($paramValue)) {
            $log .= $this->tag['b'] . 'resource' . $this->tag['/b'] . ' ' .
                $this->tag['i'] . get_resource_type($paramValue) . $this->tag['/i'] . ' ' .
                $this->tag['font']['resource'] . $paramValue . $this->tag['/font'];
        } else {
            assert(false);
        }
        return $log;
    }
    
    /**
     * Analyze parameter part of back trace array, and return string.
     * 
     * @param array $backtraceParams Back trace parameters.
     * 
     * @return string Part of log lines.
     */
    private function _searchDebugBacktraceArgsToString($backtraceParams)
    {
        assert(func_num_args() == 1);
        global $_BreakpointDebugging_EXE_MODE;
        
        $isFirst = true;
        $log = '';
        foreach ($backtraceParams as $paramName => $paramValue) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $log .= PHP_EOL . "\t,";
            }
            $log .= $this->_getTypeAndValue($paramName, $paramValue, 1);
        }
        return $log;
    }
    
    /**
     * Log errors.
     * 
     * @param string $errorLog Error log.
     * 
     * @return void
     */
    private function _errorLog($errorLog)
    {
        assert(func_num_args() == 1);
        
        if (error_log(PHP_EOL . $errorLog . PHP_EOL) === false) {
            echo '<br/>This failed in the output of the error log.<br/>';
        }
    }
}

?>
