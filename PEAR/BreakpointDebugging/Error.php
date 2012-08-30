<?php

/**
 * This class does error or exception handling.
 *
 * There is this file to increase speed when does not do error or exception handling.
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
     * @var string Variable configuring file name.
     */
    private $_varConfFileName = 'ErrorLog.var.conf';

    /**
     * @var string Key of current error log file name.
     */
    private $_keyOfCurrentErrorLogFileName = 'CURRENT_ERROR_LOG_FILE_NAME=';

    /**
     * @var string Prefix of error log file name.
     */
    private $_prefixOfErrorLogFileName = 'php_error_';

    /**
     * @var string Error log file extension.
     */
    private $_errorLogFileExt = '.log';

    /**
     * @var string Error log file path.
     *             Warning: When you use existing log, it is destroyed if it is not "UTF-8". It is necessary to be a single character sets.
     */
    private $_errorLogFilePath;

    /**
     * @var resource Error log file pointer.
     */
    private $_pErrorLogFile;

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
    public $tags;

    /**
     * @var object Locking object.
     */
    public $lockByFileExisting;

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
            $this->tags['font']['caution'] = '';
            $this->tags['font']['bool'] = '';
            $this->tags['font']['int'] = '';
            $this->tags['font']['float'] = '';
            $this->tags['font']['string'] = '';
            $this->tags['font']['null'] = '';
            $this->tags['font']['resource'] = '';
            $this->tags['font']['=>'] = '';
            $this->tags['/font'] = '';
            $this->tags['small'] = '';
            $this->tags['/small'] = '';
            $this->tags['i'] = '';
            $this->tags['/i'] = '';
            $this->tags['b'] = '';
            $this->tags['/b'] = '';
            $this->tags['pre'] = '';
            $this->tags['/pre'] = PHP_EOL . PHP_EOL;
        } else { // In case of not the logging.
            $this->_isLogging = false;
            $this->_mark = '&diams;';
            $this->tags['font']['caution'] = '<font color=\'#ff0000\'>';
            $this->tags['font']['bool'] = '<font color=\'#75507b\'>';
            $this->tags['font']['int'] = '<font color=\'#4e9a06\'>';
            $this->tags['font']['float'] = '<font color=\'#f57900\'>';
            $this->tags['font']['string'] = '<font color=\'#cc0000\'>';
            $this->tags['font']['null'] = '<font color=\'#3465a4\'>';
            $this->tags['font']['resource'] = '<font color=\'#8080ff\'>';
            $this->tags['font']['=>'] = '<font color=\'#888a85\'>';
            $this->tags['/font'] = '</font>';
            $this->tags['small'] = '<small>';
            $this->tags['/small'] = '</small>';
            $this->tags['i'] = '<i>';
            $this->tags['/i'] = '</i>';
            $this->tags['b'] = '<b>';
            $this->tags['/b'] = '</b>';
            $this->tags['pre'] = '<pre class=\'xdebug-var-dump\' dir=\'ltr\'>';
            $this->tags['/pre'] = '</pre>';
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
                B::internalException($message);
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
        B::internalAssert(func_num_args() <= 4);
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
        $this->_outputFixedFunctionToLogging($array, $pTmpLog2, $onceFlag2, $func, $class, '', '', "\t" . $tabs);
        $this->_addFunctionValuesToLog($pTmpLog2, $pTmpLog, $onceFlag2, $func, $class, '', "\t" . $tabs);

        $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font'] . $this->tags['b'] . 'array' . $this->tags['/b'] . ' (');
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
        B::internalAssert($pTmpLog2 !== null);
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

        B::internalAssert(func_num_args() <= 4);
        B::internalAssert(is_string($paramName) || is_int($paramName));
        B::internalAssert(is_string($className));
        B::internalAssert(is_object($object));
        B::internalAssert(is_int($tabNumber));

        $tabs = str_repeat("\t", $tabNumber);
        $classReflection = new ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        $constants = $classReflection->getConstants();

        $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font'] . $this->tags['b'] . 'class ' . $this->tags['/b'] . $this->tags['i'] . $className . $this->tags['/i'] . PHP_EOL . $tabs . '{');
        // Beyond max log param nesting level.
        if ($tabNumber >= B::$maxLogParamNestingLevel) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
        } else {
            foreach ($constants as $constName => $constValue) {
                $this->_getTypeAndValue($pTmpLog, $this->tags['i'] . 'const ' . $this->tags['/i'] . $constName, $constValue, $tabNumber + 1);
            }
            count($constants) ? $this->_logBufferWriting($pTmpLog, PHP_EOL) : null;
            foreach ($propertyReflections as $propertyReflection) {
                $propertyReflection->setAccessible(true);
                $paramName = $this->tags['i'];
                $paramName .= $propertyReflection->isPublic() ? 'public ' : '';
                $paramName .= $propertyReflection->isPrivate() ? 'private ' : '';
                $paramName .= $propertyReflection->isProtected() ? 'protected ' : '';
                $paramName .= $propertyReflection->isStatic() ? 'static ' : '';
                $paramName .= $this->tags['/i'];
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

        \BreakpointDebugging_Lock::forceUnlock();

        $errorMessage = $this->_convertMbString($pException->getMessage());
        $prependLog = $this->_convertMbString($prependLog);

        $this->callStackInfo = $pException->getTrace();
        $this->callStackInfo[] = array ('file' => $pException->getFile(), 'line' => $pException->getLine());
        $this->callStackInfo = array_reverse($this->callStackInfo);
        // Add scope of start page file.
        $this->callStackInfo[] = array ();
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
     * @//return bool Did the error handling end?
     * @return bool Without system log (true).
     */
    function errorHandler2($errorNumber, $errorMessage, $prependLog)
    {
        B::internalAssert(func_num_args() === 3);
        B::internalAssert(is_int($errorNumber));
        B::internalAssert(is_string($errorMessage));
        B::internalAssert(is_string($prependLog));

        global $_BreakpointDebugging_EXE_MODE;

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
                $endFlag = true;
                break;
            case E_ERROR:
                $errorKind = 'E_ERROR';
                $endFlag = true;
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
                $endFlag = true;
                break;
            case E_CORE_WARNING:
                $errorKind = 'E_CORE_WARNING';
                break;
            case E_COMPILE_ERROR:
                $errorKind = 'E_COMPILE_ERROR';
                $endFlag = true;
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
                //$errorKind = 'E_UNKNOWN';
                //BreakpointDebugging_breakpoint($errorMessage, $this->callStackInfo);
                B::internalAssert(false);
                break;
        }
//        if (isset($endFlag)) {
//            \BreakpointDebugging_Lock::forceUnlock();
//        }

        $errorMessage = $this->_convertMbString($errorMessage);
        $prependLog = $this->_convertMbString($prependLog);

        $this->callStackInfo = debug_backtrace();
        unset($this->callStackInfo[0], $this->callStackInfo[1]);
        // Add scope of start page file.
        $this->callStackInfo[] = array ();
        if ($this->outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog)) {
            BreakpointDebugging_breakpoint($errorMessage, $this->callStackInfo);
            //    return true;
            //}
            //return false; // With system log.
        } else { // In case of "B::RELEASE" mode.
            if (isset($endFlag)) {
                exit;
            }
        }
        return true;
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
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error file =======>' . $this->tags['font']['string'] . '\'' . $file . '\'' . $this->tags['/font']);
        }
        if ($line) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error line =======>' . $this->tags['font']['int'] . $line . $this->tags['/font']);
        }
        if ($class) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error class ======>' . $this->tags['i'] . $class . $this->tags['/i']);
        }
        if ($func) {
            $this->_logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error function ===>' . $this->tags['i'] . $func . $this->tags['/i'] . '( ');
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
    private function _outputFixedFunctionToLogging($backTrace, &$pTmpLog, &$onceFlag2, &$func, &$class, $file, $line, $tabs = '')
    {
        global $_BreakpointDebugging;
        $paramNumber = func_num_args();

        array_key_exists('function', $backTrace) ? $func = $backTrace['function'] : $func = '';
        array_key_exists('class', $backTrace) ? $class = $backTrace['class'] : $class = '';
        if (is_array($_BreakpointDebugging->notFixedLocations)) {
            foreach ($_BreakpointDebugging->notFixedLocations as $notFixedLocation) {
                array_key_exists('function', $notFixedLocation) ? $noFixFunc = $notFixedLocation['function'] : $noFixFunc = '';
                array_key_exists('class', $notFixedLocation) ? $noFixClass = $notFixedLocation['class'] : $noFixClass = '';
                array_key_exists('file', $notFixedLocation) ? $noFixFile = $notFixedLocation['file'] : $noFixFile = '';
                // $notFixedLocation of file scope is "$noFixFunc === '' && $noFixClass === '' && $paramNumber === 6".
                if ($noFixFunc === '' && $noFixClass === '' && $paramNumber === 8) {
                    continue;
                }
                if ($func === $noFixFunc && $class === $noFixClass && $file === $noFixFile) {
                    $marks = str_repeat($this->_mark, 10);
                    $this->_logBufferWriting($pTmpLog, PHP_EOL . $tabs . $this->tags['font']['caution'] . $marks . $this->tags['b'] . ' This function has been not fixed. ' . $this->tags['/b'] . $marks . $this->tags['/font']);
                    if ($onceFlag2) {
                        $onceFlag2 = false;
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

        B::internalAssert(func_num_args() <= 3);
        B::internalAssert(is_string($errorKind));
        B::internalAssert(is_string($errorMessage));
        B::internalAssert(is_array($this->callStackInfo));
        B::internalAssert(is_string($prependLog));

        if (!$this->_isLogging) {
            $errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
            $prependLog = htmlspecialchars($prependLog, ENT_QUOTES, 'UTF-8');
        }
        if ($errorKind === 'E_NOTICE') {
            // Should skip "E_NOTICE" in case of "B::RELEASE" mode.
            if ($_BreakpointDebugging_EXE_MODE & B::RELEASE) {
                return false;
            } else { // We had better debug by breakpoint than the display screen in case of "E_NOTICE".
                return true;
            }
        }

        // If this does a log.
        if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) {
            // Locks the error log files.
            $this->lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
            $this->lockByFileExisting->lock();
            // When "ErrorLog" directory does not exist.
            $errorLogDirectory = B::$workDir . '/ErrorLog/';
            if (!is_dir($errorLogDirectory)) {
                // Makes directory, sets permission and sets own user.
                B::mkdir($errorLogDirectory, 0600);
            }
            $exceptionMessage = '';
            $varConfFilePath = $errorLogDirectory . $this->_varConfFileName;
            // If variable configuring file exists.
            if (is_file($varConfFilePath)) {
                // Opens variable configuring file.
                $pVarConfFile = fopen($varConfFilePath, 'r+b');
            } else {
                // Creates and opens variable configuring file.
                $pVarConfFile = B::fopen($varConfFilePath, 'x+b', 0600);
                // Sets current error log file name.
                fwrite($pVarConfFile, $this->_keyOfCurrentErrorLogFileName . $this->_prefixOfErrorLogFileName . '1.log' . PHP_EOL);
                fflush($pVarConfFile);
                rewind($pVarConfFile);
            }
            reset($this->callStackInfo);
            $call = each($this->callStackInfo);
            if (substr(PHP_OS, 0, 3) === 'WIN') {
                $filePath = strtolower($call['value']['file']);
            } else {
                $filePath = $call['value']['file'];
            }
            $file = basename($filePath);
            $fileCmpLen = strlen($file) + 1;
            $filePath .= '?';
            $line = $call['value']['line'];
            // Gets current error log file name.
            $currentErrorLogFileName = substr(trim(fgets($pVarConfFile)), strlen($this->_keyOfCurrentErrorLogFileName));
            $maxErrorFileNumber = 0;
            while ($varConfLineString = fgets($pVarConfFile)) {
                $varConfLineString = trim($varConfLineString);
                if (strncmp($varConfLineString, $file . ':', $fileCmpLen) === 0) { // Searches the error file name.
                    $tmpErrorFileNumber = substr($varConfLineString, -4);
                    if ($tmpErrorFileNumber > $maxErrorFileNumber) {
                        $maxErrorFileNumber = $tmpErrorFileNumber;
                    }
                    if (substr_compare($varConfLineString, $filePath, $fileCmpLen, strlen($filePath)) === 0) { // Searches the error file path.
                        $errorFileNumber = $tmpErrorFileNumber;
                        break;
                    }
                }
            }
            if (substr(PHP_OS, 0, 3) === 'WIN') {
                $this->_errorLogFilePath = strtolower($errorLogDirectory . $currentErrorLogFileName);
            } else {
                $this->_errorLogFilePath = $errorLogDirectory . $currentErrorLogFileName;
            }
            // When current error log file does not exist.
            if (!is_file($this->_errorLogFilePath)) {
                // Creates and opens current error log file.
                $this->_pErrorLogFile = B::fopen($this->_errorLogFilePath, 'xb', 0600);
            } else {
                // Opens current error log file.
                $this->_pErrorLogFile = fopen($this->_errorLogFilePath, 'ab');
            }
            // If error location file exists.
            if (isset($errorFileNumber)) {
                $errorLocationFilePath = $errorLogDirectory . $file . sprintf('.%04d', $errorFileNumber) . '.txt';
                if (!is_file($errorLocationFilePath)) {
                    $exceptionMessage = '"' . $errorLocationFilePath . '" which should exist does not exist.' . PHP_EOL
                        . 'Please, repair "' . $varConfFilePath . '" file.';
                    goto END_LABEL;
                }
                // Opens the error location file.
                $pErrorLocationFile = fopen($errorLocationFilePath, 'r+b');
            } else {
                $errorFile4Number = sprintf('%04d', $maxErrorFileNumber + 1);
                $errorLocationFilePath = $errorLogDirectory . $file . '.' . $errorFile4Number . '.txt';
                fseek($pVarConfFile, 0, SEEK_END);
                // Sets the error file path.
                fwrite($pVarConfFile, $file . ':' . $filePath . $errorFile4Number . PHP_EOL);
                // Creates the error location file.
                $pErrorLocationFile = B::fopen($errorLocationFilePath, 'x+b', 0600);
            }
            // Searches error line.
            $fstat = fstat($pErrorLocationFile);
            $fsize = $fstat['size'];
            if ($line > $fsize) {
                // Marks the error line.
                fseek($pErrorLocationFile, 0, SEEK_END);
                $repeatNumber = $line - $fsize - 1;
                fwrite($pErrorLocationFile, str_repeat('-', $repeatNumber));
                fwrite($pErrorLocationFile, '*');
            } else {
                fseek($pErrorLocationFile, $line - 1);
                $errorLineMark = fread($pErrorLocationFile, 1);
                if ($errorLineMark === '*') {
                    // Skips same error.
                    goto END_LABEL;
                } else if ($errorLineMark !== '-') {
                    $exceptionMessage = '"' . $errorLocationFilePath . '" file must be composed with "*" or "-".';
                    goto END_LABEL;
                }
                // Marks the error line.
                fseek($pErrorLocationFile, $line - 1);
                fwrite($pErrorLocationFile, '*');
            }
            if (!is_string($currentErrorLogFileName)) {
                $exceptionMessage = 'Current error log file name should be string.';
                goto END_LABEL;
            }
        }

        $tmp = date('[Y-m-d H:i:s]') . PHP_EOL;
        $dummy = null;
        $this->_logBufferWriting($dummy, $this->tags['pre'] . $prependLog);
        // Create error log from the argument.
        $tmp .= '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' .
            PHP_EOL . $this->_mark . 'Error kind =======>' . $this->tags['font']['string'] . '\'' . $errorKind . '\'' . $this->tags['/font'] .
            PHP_EOL . $this->_mark . 'Error message ====>' . $this->tags['font']['string'] . '\'' . $errorMessage . '\'' . $this->tags['/font'];
        $this->_logBufferWriting($dummy, $tmp);
        // Search array which debug_backtrace() or getTrace() returns, and add a parametric information.
        foreach ($this->callStackInfo as $backtraceArrays) {
            $onceFlag2 = true;
            $pTmpLog2 = $this->_logPointerOpening();
            array_key_exists('file', $backtraceArrays) ? $file = $backtraceArrays['file'] : $file = '';
            array_key_exists('line', $backtraceArrays) ? $line = $backtraceArrays['line'] : $line = '';
            $this->_outputFixedFunctionToLogging($backtraceArrays, $dummy, $onceFlag2, $func, $class, $file, $line);
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
        $this->_logBufferWriting($dummy, $this->tags['/pre']);
        // If this does a log.
        if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) {
            // When log file size exceeds.
            $errorLogFileStatus = fstat($this->_pErrorLogFile);
            if ($errorLogFileStatus['size'] > B::$maxLogFileByteSize) {
                // Gets next error log file name.
                $nextNumber = substr($currentErrorLogFileName, strlen($this->_prefixOfErrorLogFileName), 1) + 1;
                if ($nextNumber > 8) {
                    $nextNumber = 1;
                }
                $nextErrorLogFilePath = $errorLogDirectory . $this->_prefixOfErrorLogFileName . $nextNumber . $this->_errorLogFileExt;
                // When next error log file exists.
                if (is_file($nextErrorLogFilePath)) {
                    // Deletes next error log file.
                    unlink($nextErrorLogFilePath);
                }
                // Seeks to error log file number.
                fseek($pVarConfFile, strlen($this->_keyOfCurrentErrorLogFileName . $this->_prefixOfErrorLogFileName));
                // Sets next error log file name number to variable configuring file.
                fwrite($pVarConfFile, $nextNumber);
            }

            END_LABEL:
            // Closes the error location file.
            fclose($pErrorLocationFile);
            // Closes current error log file.
            fclose($this->_pErrorLogFile);
            // Closes variable configuring file.
            fclose($pVarConfFile);
            // Unlocks the error log files.
            $this->lockByFileExisting->unlock();
            if ($exceptionMessage) {
                B::internalException($exceptionMessage);
            }
        }
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
        $this->_logBufferWriting($pTmpLog, $prefix . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font']);
        $tag = function ($self, $type, $paramValue) {
                return $self->tags['small'] . $type . $self->tags['/small'] . ' ' . $self->tags['font'][$type] . $paramValue . $self->tags['/font'];
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
                $this->_logBufferWriting($pTmpLog, $tag($this, 'string', $paramValue) . $this->tags['i'] . ' (length=' . $strlen . ')' . $this->tags['/i']);
            } else {
                $this->_logBufferWriting($pTmpLog, $tag($this, 'string', $paramValue) . $this->tags['i'] . '... (length=' . $strlen . ')' . $this->tags['/i']);
            }
        } else if (is_resource($paramValue)) {
            $tmp = $this->tags['b'] . 'resource' . $this->tags['/b'] . ' ' .
                $this->tags['i'] . get_resource_type($paramValue) . $this->tags['/i'] . ' ' .
                $this->tags['font']['resource'] . $paramValue . $this->tags['/font'];
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
        B::internalAssert(func_num_args() <= 3);

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
            return array ();
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

        switch ($_BreakpointDebugging_EXE_MODE & ~B::UNIT_TEST) {
            case B::RELEASE:
                rewind($pTmpLog);
                while (!feof($pTmpLog)) {
                    fwrite($this->_pErrorLogFile, fread($pTmpLog, 4096));
                }
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
                foreach ($pTmpLog as $log) {
                    fwrite($this->_pErrorLogFile, $log);
                }
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
        B::internalAssert(is_array($pLogBuffer) || is_resource($pLogBuffer) || $pLogBuffer === null);

        global $_BreakpointDebugging_EXE_MODE;

        switch ($_BreakpointDebugging_EXE_MODE & ~B::UNIT_TEST) {
            case B::RELEASE:
                if ($pLogBuffer === null) {
                    fwrite($this->_pErrorLogFile, $log);
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
                    fwrite($this->_pErrorLogFile, $log);
                } else {
                    $pLogBuffer[] = $log;
                }
                break;
            default:
                B::internalAssert(false);
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

        switch ($_BreakpointDebugging_EXE_MODE & ~B::UNIT_TEST) {
            case B::RELEASE:
                rewind($pTmpLog2);
                if ($pTmpLog === null) {
                    while (!feof($pTmpLog2)) {
                        fwrite($this->_pErrorLogFile, fread($pTmpLog2, 4096));
                    }
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
                    foreach ($pTmpLog2 as $log) {
                        fwrite($this->_pErrorLogFile, $log);
                    }
                } else if (count($pTmpLog) === 0) {
                    if (count($pTmpLog2) !== 0) {
                        $pTmpLog = $pTmpLog2;
                    }
                } else if (count($pTmpLog2) !== 0) {
                    $pTmpLog = array_merge($pTmpLog, $pTmpLog2);
                }
                break;
            default:
                B::internalAssert(false);
        }
        $this->_logPointerClosing($pTmpLog2);
    }

}

?>
