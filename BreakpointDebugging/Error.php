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
 * @author   Hidenori Wasa <username@example.com>
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
 * @author   Hidenori Wasa <username@example.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_Error
{
    /**
     * This is Called from user exception handler of the whole code.
     *
     * @param object $exception Exception info.
     * 
     * @return void
     */
    static function exceptionHandler($exception)
    {
        global $_BreakpointDebugging_EXE_MODE;
        
        $log = self::_buildErrorCallStackLog($exception->getfile(), $exception->getline(), 'EXCEPTION', $exception->getmessage(), $exception->gettrace());
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
    static function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
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
        $log = self::_buildErrorCallStackLog($errorFile, $errorLine, $errorKind, $errorMessage, debug_backtrace(true));
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
    private static function _buildErrorCallStackLog($errorFile, $errorLine, $errorKind, $errorMessage, $backTrace)
    {
        global $_BreakpointDebugging;
        
        // Create error log from the argument.
        $log = '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' . PHP_EOL .
            '### error file =======>' . $errorFile . PHP_EOL .
            '### error line =======>' . $errorLine . PHP_EOL .
            '### error kind =======>' . $errorKind . PHP_EOL .
            '### error message ====>' . $errorMessage . PHP_EOL;
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
                    $log .= PHP_EOL . '##### This function has been not fixed. #####';
                    break;
                }
            }
            $log .= PHP_EOL . '### error file =======>' . $file;
            $log .= PHP_EOL . '### error line =======>' . $line;
            $log .= PHP_EOL . '### function call ====>' . $func . '( ';
            if (array_key_exists('args', $backtraceArrays)) {
                // Analyze parameter part of back trace array, and return string.
                $log .= self::_searchDebugBacktraceArgsToString($backtraceArrays['args']);
            }
            $log .= ');' . PHP_EOL;
        }
        $log .= '//////////////////////////////// CALL STACK END ////////////////////////////////' . PHP_EOL;
        return $log;
    }
    
    /**
     * Analyze parameter part of back trace array, and return string.
     * 
     * @param array $backtraceParams Back trace parameters.
     * 
     * @return string Part of log lines.
     */
    private static function _searchDebugBacktraceArgsToString($backtraceParams)
    {
        global $_BreakpointDebugging_EXE_MODE;
        
        $isFirst = true;
        $logLines = '';
        foreach ($backtraceParams as $key => $param) {
            if ($isFirst) {
                $isFirst = false;
                $logLines .= PHP_EOL;
            } else {
                $logLines .= ',';
            }
            if (is_array($param)) {
                if ($key == 'GLOBALS') {
                    continue;
                }
                $logLines .= PHP_EOL . 'array(' . self::_searchDebugBacktraceArgsToString($param) . ')' . PHP_EOL;
            } else {
                if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of release.
                    $htmlErrors = ini_get('html_errors');
                    // Delete HTML tag of var_dump().
                    B::iniSet('html_errors', '');
                }
                ob_start();
                var_dump($param);
                $param = ob_get_clean();
                if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of release.
                    // Restore setting.
                    B::iniSet('html_errors', $htmlErrors);
                }
                
                B::convertMbString($param);
                assert(mb_detect_encoding($param, 'utf8', true) != false);
                $logLines .= $param;
            }
        }
        return $logLines;
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
