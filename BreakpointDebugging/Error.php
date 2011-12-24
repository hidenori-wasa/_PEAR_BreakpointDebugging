<?php

/**
 * There is this file to increase speed when does not do error or exception handling.
 * 
 * In other words, this file does not cause "__autoload()" because does not read except for error or exception handling.
 * 
 * PHP version 5.3
 *
 * LICENSE:
 * Copyright (c) 2011, Hidenori Wasa
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
        $log = PHP_EOL . $tabs . $paramName . ' => array (';
        foreach ($array as $paramName => $paramValue) {
            $return = $this->_getTypeAndValue($paramName, $paramValue, $tabNumber + 1);
            if ($return === false) {
                continue;
            }
            $log .= $return;
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
        
        $tab = str_repeat("\t", $tabNumber);
        $classReflection = new ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        $constants = $classReflection->getConstants();
        
        $log = PHP_EOL . $tab . $paramName . ' => class ' . $className . PHP_EOL .
            $tab . '{';
        foreach ($constants as $constName => $constValue) {
            $return = $this->_getTypeAndValue('const ' . $constName, $constValue, $tabNumber + 1);
            if ($return === false) {
                continue;
            }
            $log .= $return;
        }
        count($constants) ? $log .= PHP_EOL : null;
        foreach ($propertyReflections as $propertyReflection) {
            $propertyReflection->setAccessible(true);
            $paramName = $propertyReflection->isPublic() ? 'public ' : '';
            $paramName .= $propertyReflection->isPrivate() ? 'private ' : '';
            $paramName .= $propertyReflection->isProtected() ? 'protected ' : '';
            $paramName .= $propertyReflection->isStatic() ? 'static ' : '';
            $paramName .= '$' . $propertyReflection->getName();
            //$paramName .= $propertyReflection->isDefault() ? ' &lt;default>' : '';
            if ($propertyReflection->isStatic()) {
                $paramValue = $propertyReflection->getValue($propertyReflection);
            } else {
                $paramValue = $propertyReflection->getValue($object);
            }
            $return = $this->_getTypeAndValue($paramName, $paramValue, $tabNumber + 1);
            if ($return === false) {
                continue;
            }
            $log .= $return;
        }
        return $log . PHP_EOL . $tab . '}';
    }
    
    /**
     * This is Called from user exception handler of the whole code.
     *
     * @param object $exception Exception info.
     * 
     * @return void
     */
    function exceptionHandler($exception)
    {
        assert(func_num_args() == 1);
        global $_BreakpointDebugging_EXE_MODE;
        
        $log = $this->_buildErrorCallStackLog($exception->getfile(), $exception->getline(), 'EXCEPTION', $exception->getmessage(), $exception->gettrace());
        $diplayLog = function ($log) {
            echo '<pre>' . $log . '<pre/>';
            echo 'This ends in the global exception.';
        };
        switch ($_BreakpointDebugging_EXE_MODE) {
        case B::LOCAL_DEBUG_OF_RELEASE:
            $diplayLog($log);
            BreakpointDebugging_breakpoint();
        case B::RELEASE:
            self::_errorLog($log);
            break;
        case B::LOCAL_DEBUG:
            $diplayLog($log);
            BreakpointDebugging_breakpoint();
            break;
        case B::REMOTE_DEBUG:
            $diplayLog($log);
            exit(-1);
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
     * 
     * @return bool Did the error handling end?
     */
    function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
        assert(func_num_args() == 4);
        global $_BreakpointDebugging_EXE_MODE;
        
        $errorMessage = mb_convert_encoding($errorMessage, 'utf8', 'auto');
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
        $log = $this->_buildErrorCallStackLog($errorFile, $errorLine, $errorKind, $errorMessage, debug_backtrace(true));
        $diplayLog = function ($log) {
            echo '<pre>' . $log . '<pre/>';
        };
        switch ($_BreakpointDebugging_EXE_MODE)
        {
        case B::LOCAL_DEBUG_OF_RELEASE:
            $diplayLog($log);
            BreakpointDebugging_breakpoint();
        case B::RELEASE:
            self::_errorLog($log);
            return false; // with system log.
        case B::LOCAL_DEBUG:
            $diplayLog($log);
            BreakpointDebugging_breakpoint();
            return true;
        case B::REMOTE_DEBUG:
            // This displays error log because breakpoint can not be used on remote debug.
            $diplayLog($log);
            return true;
        default:
            assert(false);
            exit(-1);
        }
    }
    
    /**
     * Build error call stack log.
     * 
     * @param string $errorFile    Error file name.
     * @param int    $errorLine    Error file line.
     * @param int    $errorKind    Error kind.
     * @param string $errorMessage Error message.
     * @param object $backTrace    Back trace for debug.
     * 
     * @return string Error call stack log.
     */
    private function _buildErrorCallStackLog($errorFile, $errorLine, $errorKind, $errorMessage, $backTrace)
    {
        assert(func_num_args() == 5);
        global $_BreakpointDebugging;
        
        // Create error log from the argument.
        $log = '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' . PHP_EOL .
            '★Error file =======>' . $errorFile . PHP_EOL .
            '★Error line =======>' . $errorLine . PHP_EOL .
            '★Error kind =======>' . $errorKind . PHP_EOL .
            '★Error message ====>' . $errorMessage . PHP_EOL;
        // Search array which debug_backtrace() or Exception::gettrace() returns, and add a parametric information.
        array_reverse($backTrace, true);
        foreach ($backTrace as $backtraceArrays) {
            array_key_exists('file', $backtraceArrays) ? $file = $backtraceArrays['file'] : $file = '';
            array_key_exists('line', $backtraceArrays) ? $line = $backtraceArrays['line'] : $line = '';
            array_key_exists('function', $backtraceArrays) ? $func = $backtraceArrays['function'] : $func = '';
            array_key_exists('class', $backtraceArrays) ? $class = $backtraceArrays['class'] : $class = '';
            foreach ($_BreakpointDebugging->callStack as $callStack) {
                array_key_exists('file', $callStack) ? $noFixFile = $callStack['file'] : $noFixFile = '';
                array_key_exists('function', $callStack) ? $noFixFunc = $callStack['function'] : $noFixFunc = '';
                array_key_exists('class', $callStack) ? $noFixClass = $callStack['class'] : $noFixClass = '';
                if ($file == $noFixFile && $func == $noFixFunc && $class == $noFixClass) {
                    $log .= PHP_EOL . '★★★ This function has been not fixed. ★★★';
                    break;
                }
            }
            $log .= PHP_EOL . '★Error file =======>' . $file;
            $log .= PHP_EOL . '★Error line =======>' . $line;
            $log .= PHP_EOL . '★Function call ====>' . $func . '( ';
            if (array_key_exists('args', $backtraceArrays)) {
                // Analyze parameter part of back trace array, and return string.
                $log .= $this->_searchDebugBacktraceArgsToString($backtraceArrays['args']);
            }
            $log .= PHP_EOL . ');';
        }
        $log .= PHP_EOL . '//////////////////////////////// CALL STACK END ////////////////////////////////' . PHP_EOL;
        return $log;
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
                return false;
            }
            return $this->_reflectArray($paramName, $paramValue, $tabNumber);
        } else if (is_object($paramValue)) {
            return $this->_reflectObject($paramName, $paramValue, $tabNumber);
        }
        
        $tabs = str_repeat("\t", $tabNumber);
        $prefix = PHP_EOL . $tabs;
        $log = $prefix . $paramName . ' => ';
        if (is_null($paramValue)) {
            $log .= 'null null';
        } else if (is_bool($paramValue)) {
            $log .= 'bool ' . ( $paramValue ? 'true' : 'false');
        } else if (is_int($paramValue)) {
            $log .= 'int ' . $paramValue;
        } else if (is_float($paramValue)) {
            $log .= 'float ' . $paramValue;
        } else if (is_string($paramValue)) {
            $paramValue = B::convertMbString($paramValue);
            assert(mb_detect_encoding($paramValue, 'utf8', true) != false);
            $paramValue = htmlspecialchars($paramValue, ENT_QUOTES, 'UTF-8');
            $log .= 'string ' . $paramValue;
        } else if (is_resource($paramValue)) {
            $log .= get_resource_type($paramValue) . ' ' . $paramValue;
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
            $return = $this->_getTypeAndValue($paramName, $paramValue, 1);
            if ($return === false) {
                continue;
            }
            $log .= $return;
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
    final private static function _errorLog($errorLog)
    {
        assert(func_num_args() == 1);
        
        if (error_log(PHP_EOL . $errorLog . PHP_EOL) === false) {
            echo '<br/>This failed in the output of the error log.<br/>';
        }
    }
}

?>
