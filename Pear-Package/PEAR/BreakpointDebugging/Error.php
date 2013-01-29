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
 * Copyright (c) 2012-2013, Hidenori Wasa
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
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
use \BreakpointDebugging as B;

/**
 * This class do error or exception handling.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class BreakpointDebugging_Error_InAllCase
{
    /**
     * @var array Logged arrays.
     */
    private $_loggedArrays;

    /**
     * @var array Logged objects.
     */
    private $_loggedObjects;

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
    protected $pErrorLogFile;

    /**
     * @var array Call stack information.
     */
    private $_callStackInfo;

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
    private $_tags = array ();

    /**
     * @var object Locking object.
     */
    private $_lockByFileExisting;

    /**
     * Sets HTML tags.
     *
     * @param array &$tags HTML tags.
     *
     * @return void
     */
    protected function setHTMLTags(&$tags)
    {
        $tags['font']['caution'] = '';
        $tags['font']['bool'] = '';
        $tags['font']['int'] = '';
        $tags['font']['float'] = '';
        $tags['font']['string'] = '';
        $tags['font']['null'] = '';
        $tags['font']['resource'] = '';
        $tags['font']['=>'] = '';
        $tags['/font'] = '';
        $tags['small'] = '';
        $tags['/small'] = '';
    }

    /**
     * Makes HTML tags.
     *
     * @return void
     */
    function __construct()
    {
        global $_BreakpointDebugging_EXE_MODE;

        B::limitAccess('BreakpointDebugging.php');
        B::assert(func_num_args() === 0);

        $this->_loggedArrays = array ();
        $this->_loggedObjects = array ();
        if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of the logging.
            $this->_isLogging = true;
            $this->_mark = '#';
            $this->setHTMLTags($this->_tags);
            $this->_tags['pre'] = '';
            $this->_tags['/pre'] = PHP_EOL . PHP_EOL;
            $this->_tags['i'] = '';
            $this->_tags['/i'] = '';
            $this->_tags['b'] = '';
            $this->_tags['/b'] = '';
        } else { // In case of not the logging.
            $this->_isLogging = false;
            $this->_mark = '&diams;';
            // When "Xdebug" exists.
            if (B::getXebugExists()) {
                $this->_tags['pre'] = '<pre class=\'xdebug-var-dump\' dir=\'ltr\'>';
                $this->_tags['font']['caution'] = '<font color=\'#ff0000\'>';
                $this->_tags['font']['bool'] = '<font color=\'#75507b\'>';
                $this->_tags['font']['int'] = '<font color=\'#4e9a06\'>';
                $this->_tags['font']['float'] = '<font color=\'#f57900\'>';
                $this->_tags['font']['string'] = '<font color=\'#cc0000\'>';
                $this->_tags['font']['null'] = '<font color=\'#3465a4\'>';
                $this->_tags['font']['resource'] = '<font color=\'#8080ff\'>';
                $this->_tags['font']['=>'] = '<font color=\'#888a85\'>';
                $this->_tags['/font'] = '</font>';
                $this->_tags['small'] = '<small>';
                $this->_tags['/small'] = '</small>';
            } else { // When "Xdebug" does not exist.
                $this->_tags['pre'] = '<pre>';
                $this->setHTMLTags($this->_tags);
            }
            $this->_tags['/pre'] = '</pre>';
            $this->_tags['i'] = '<i>';
            $this->_tags['/i'] = '</i>';
            $this->_tags['b'] = '<b>';
            $this->_tags['/b'] = '</b>';
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
    protected function convertMbString($string)
    {
        static $onceFlag = true;

        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8'
            || $charSet === 'ASCII'
        ) {
            return $string;
        } else if ($charSet === false) {
            $message = 'This isn\'t single character sets.';
            if ($onceFlag) {
                $onceFlag = false;
                B::internalException($message, 3);
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
     * @param mixed  $line       Line number of call stack.
     * @param string $tabs       Tabs to indent.
     *
     * @return void
     */
    protected function addFunctionValuesToLog(&$pTmpLog2, &$pTmpLog, &$onceFlag2, $func, $class, $line, $tabs = '')
    {
        $paramNumber = func_num_args();

        $valuesToTraceFiles = B::getStatic('$_valuesToTrace');
        $onceFlag = false;
        if (!is_array($valuesToTraceFiles)) {
            return;
        }
        foreach ($valuesToTraceFiles as $valuesToTraceLines) {
            foreach ($valuesToTraceLines as $trace) {
                array_key_exists('function', $trace) ? $callFunc = $trace['function'] : $callFunc = '';
                array_key_exists('class', $trace) ? $callClass = $trace['class'] : $callClass = '';
                if ($callFunc === ''
                    && $callClass === ''
                    && $paramNumber === 7
                ) {
                    continue;
                }
                if ($func === $callFunc
                    && $class === $callClass
                ) {
                    if ($onceFlag2) {
                        $onceFlag2 = false;
                        array_key_exists('file', $trace) ? $callFile = $trace['file'] : $callFile = '';
                        $this->addParameterHeaderToLog($pTmpLog, $callFile, $line, $func, $class);
                    }
                    if ($onceFlag) {
                        $this->logBufferWriting($pTmpLog2, PHP_EOL . $tabs . "\t,");
                    } else {
                        $this->logBufferWriting($pTmpLog2, PHP_EOL . $tabs . $this->_mark . 'Function values ==>');
                        $onceFlag = true;
                    }
                    // Analyze values part of trace array, and return character string.
                    $this->searchDebugBacktraceArgsToString($pTmpLog2, $trace['values'], strlen($tabs) + 1);
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
    protected function reflectArray(&$pTmpLog, $paramName, $array, $tabNumber = 1)
    {
        $isOverMaxLogElementNumber = false;
        if (count($array) > B::getStatic('$_maxLogElementNumber')) {
            $isOverMaxLogElementNumber = true;
            $array = array_slice($array, 0, B::getStatic('$_maxLogElementNumber'), true);
        }
        $tabs = str_repeat("\t", $tabNumber);
        $onceFlag2 = false;
        $pTmpLog2 = $this->logPointerOpening();
        // For "Exception::$trace".
        $this->outputFixedFunctionToLogging($array, $pTmpLog2, $onceFlag2, $func, $class, '', '', "\t" . $tabs);
        $this->addFunctionValuesToLog($pTmpLog2, $pTmpLog, $onceFlag2, $func, $class, '', "\t" . $tabs);

        foreach ($this->_loggedArrays as $loggedArrayNumber => $loggedArray) {
            if ($loggedArray === $array) {
                // Skips same array.
                $loggedArrayNumber++;
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->_tags['font']['=>'] . ' => ' . $this->_tags['/font'] . $this->_tags['b'] . "same array #$loggedArrayNumber" . $this->_tags['/b'] . ' (');
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
                goto AFTER_TREATMENT;
            }
        }
        $this->_loggedArrays[] = $array;
        $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->_tags['font']['=>'] . ' => ' . $this->_tags['/font'] . $this->_tags['b'] . 'array #' . count($this->_loggedArrays) . $this->_tags['/b'] . ' (');
        // Beyond max log param nesting level.
        if ($tabNumber >= B::getStatic('$_maxLogParamNestingLevel')) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
        } else {
            foreach ($array as $paramName => $paramValue) {
                if ($paramName === 'GLOBALS') {
                    continue;
                }
                if (is_string($paramName)) {
                    $paramName = '\'' . $paramName . '\'';
                }
                $this->getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
            }
            if ($isOverMaxLogElementNumber !== false) {
                $tmp = PHP_EOL . $tabs . "\t\t.";
                $this->logBufferWriting($pTmpLog, $tmp . $tmp . $tmp);
            }
        }
        AFTER_TREATMENT:
        $this->logBufferWriting($pTmpLog2, PHP_EOL . $tabs . ')');
        $this->logCombination($pTmpLog, $pTmpLog2);
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
    protected function reflectObject(&$pTmpLog, $paramName, $object, $tabNumber = 1)
    {
        $className = get_class($object);
        $tabs = str_repeat("\t", $tabNumber);
        $classReflection = new \ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        $constants = $classReflection->getConstants();

        foreach ($this->_loggedObjects as $loggedObjectNumber => $loggedObject) {
            if ($loggedObject === $object) {
                // Skips same object.
                $loggedObjectNumber++;
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->_tags['font']['=>'] . ' => ' . $this->_tags['/font'] . $this->_tags['b'] . "same class object #$loggedObjectNumber " . $this->_tags['/b'] . $this->_tags['i'] . $className . $this->_tags['/i'] . PHP_EOL . $tabs . '{');
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
                goto AFTER_TREATMENT;
            }
        }
        $this->_loggedObjects[] = $object;
        $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->_tags['font']['=>'] . ' => ' . $this->_tags['/font'] . $this->_tags['b'] . 'class object #' . count($this->_loggedObjects) . ' ' . $this->_tags['/b'] . $this->_tags['i'] . $className . $this->_tags['/i'] . PHP_EOL . $tabs . '{');
        // Beyond max log param nesting level.
        if ($tabNumber >= B::getStatic('$_maxLogParamNestingLevel')) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
        } else {
            foreach ($constants as $constName => $constValue) {
                $this->getTypeAndValue($pTmpLog, $this->_tags['i'] . 'const ' . $this->_tags['/i'] . $constName, $constValue, $tabNumber + 1);
            }
            count($constants) ? $this->logBufferWriting($pTmpLog, PHP_EOL) : null;
            foreach ($propertyReflections as $propertyReflection) {
                $propertyReflection->setAccessible(true);
                $paramName = $this->_tags['i'];
                $paramName .= $propertyReflection->isPublic() ? 'public ' : '';
                $paramName .= $propertyReflection->isPrivate() ? 'private ' : '';
                $paramName .= $propertyReflection->isProtected() ? 'protected ' : '';
                $paramName .= $propertyReflection->isStatic() ? 'static ' : '';
                $paramName .= $this->_tags['/i'];
                $paramName .= '$' . $propertyReflection->getName();
                if ($propertyReflection->isStatic()) {
                    $paramValue = $propertyReflection->getValue($propertyReflection);
                } else {
                    $paramValue = $propertyReflection->getValue($object);
                }
                $this->getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
            }
        }
        AFTER_TREATMENT:
        $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . '}');
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
        $prependLog = $this->convertMbString($prependLog);

        for ($pCurrentException = $pException; $pCurrentException; $pCurrentException = $pCurrentException->getPrevious()) {
            $pExceptions[] = $pCurrentException;
        }
        $pExceptions = array_reverse($pExceptions);

        $elementNumber = count($pExceptions);
        for ($count = 0; $count < $elementNumber; $count++) {
            $pCurrentException = $pExceptions[$count];
            $callStackInfo = $pCurrentException->getTrace();
            if (array_key_exists($count + 1, $pExceptions)) {
                $nextCallStackInfo = $pExceptions[$count + 1]->getTrace();
                $nextCallInfo = $nextCallStackInfo[0];
                $deleteFlag = false;
                foreach ($callStackInfo as $callKey => $callInfo) {
                    if (array_key_exists('line', $callInfo)
                        && array_key_exists('line', $nextCallInfo)
                        && $callInfo['line'] === $nextCallInfo['line']
                        && array_key_exists('file', $callInfo)
                        && array_key_exists('file', $nextCallInfo)
                        && $callInfo['file'] === $nextCallInfo['file']
                    ) {
                        $deleteFlag = true;
                    }
                    if ($deleteFlag) {
                        unset($callStackInfo[$callKey]);
                    }
                }
            }

            if (B::getStatic('$_isInternal')) { // Has been called from internal method.
                B::setStatic('$_isInternal', false);
                // Array top is set to location which "self::internalException()" is called  because this location is registered to logging.
                unset($callStackInfo[0]);
            } else {
                // Array top is set to location which throws exception because this location is registered to logging.
                array_unshift($callStackInfo, array ('file' => $pCurrentException->getFile(), 'line' => $pCurrentException->getLine()));
            }
            $this->_callStackInfo = $callStackInfo;
            // Add scope of start page file.
            $this->_callStackInfo[] = array ();
            $errorMessage = $this->convertMbString($pCurrentException->getMessage());
            $this->outputErrorCallStackLog2(get_class($pCurrentException), $errorMessage, $prependLog);
        }

        B::breakpoint($errorMessage, $this->_callStackInfo);
    }

    /**
     * This is Called from error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     * @param string $prependLog   This prepend this parameter logging.
     * @param array  $callStack    The call stack.
     *
     * @return void
     */
    function errorHandler2($errorNumber, $errorMessage, $prependLog, $callStack)
    {
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
                throw new \BreakpointDebugging_ErrorException('', 5);
                break;
        }

        $errorMessage = $this->convertMbString($errorMessage);
        $prependLog = $this->convertMbString($prependLog);

        $this->_callStackInfo = $callStack;
        if (B::getStatic('$_isInternal')) { // Has been called from internal method.
            B::setStatic('$_isInternal', false);
            // Deletes location which triggers error because this handler must not log this location.
            unset($this->_callStackInfo[0], $this->_callStackInfo[1]);
        } else {
            // Sets location which triggers error to top of call stack array because this handler must log this location.
            unset($this->_callStackInfo[0]);
        }
        // Add scope of start page file.
        $this->_callStackInfo[] = array ();
        $this->outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog);
        if ($_BreakpointDebugging_EXE_MODE === B::RELEASE) { // In case of release.
            if (isset($endFlag)) {
                // In case of release mode, we must exit this process when kind is error.
                exit;
            }
        }
        B::breakpoint($errorMessage, $this->_callStackInfo);
        // We can do step execution to error location to see variable value even though kind is error.
    }

    /**
     * Add parameter header to error log.
     *
     * @param mixed  &$pTmpLog Error temporary log pointer.
     * @param string $file     File name.
     * @param mixed  $line     Line number.
     * @param string $func     Function name.
     * @param string $class    Class name.
     *
     * @return void
     */
    protected function addParameterHeaderToLog(&$pTmpLog, $file, $line, $func, $class)
    {
        if (strripos($file, 'test.php') === strlen($file) - strlen('test.php')) {
            if ($file) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error file =======>' . $this->_tags['b'] . $this->_tags['font']['string'] . '\'' . $file . '\'' . $this->_tags['/font'] . $this->_tags['/b']);
            }
            if ($line) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error line =======>' . $this->_tags['b'] . $this->_tags['font']['int'] . $line . $this->_tags['/font'] . $this->_tags['/b']);
            }
        } else {
            if ($file) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error file =======>' . $this->_tags['font']['string'] . '\'' . $file . '\'' . $this->_tags['/font']);
            }
            if ($line) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error line =======>' . $this->_tags['font']['int'] . $line . $this->_tags['/font']);
            }
        }
        if ($class) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error class ======>' . $this->_tags['i'] . $class . $this->_tags['/i']);
        }
        if ($func) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $this->_mark . 'Error function ===>' . $this->_tags['i'] . $func . $this->_tags['/i'] . '( ');
        }
    }

    /**
     * Output fixed-function to logging.
     *
     * @param array  $backTrace  Call stack.
     * @param mixed  &$pTmpLog   Error temporary log pointer.
     * @param bool   &$onceFlag2 False means logging parameter header.
     * @param mixed  &$func      Function name of call stack.
     * @param mixed  &$class     Class name of call stack.
     * @param string $file       File name of call stack.
     * @param mixed  $line       Line number of call stack.
     * @param string $tabs       Tabs to indent.
     *
     * @return void
     */
    protected function outputFixedFunctionToLogging($backTrace, &$pTmpLog, &$onceFlag2, &$func, &$class, $file, $line, $tabs = '')
    {
        $paramNumber = func_num_args();

        array_key_exists('function', $backTrace) ? $func = $backTrace['function'] : $func = '';
        array_key_exists('class', $backTrace) ? $class = $backTrace['class'] : $class = '';
        if (is_array(B::getStatic('$_notFixedLocations'))) {
            foreach (B::getStatic('$_notFixedLocations') as $notFixedLocation) {
                array_key_exists('function', $notFixedLocation) ? $noFixFunc = $notFixedLocation['function'] : $noFixFunc = '';
                array_key_exists('class', $notFixedLocation) ? $noFixClass = $notFixedLocation['class'] : $noFixClass = '';
                array_key_exists('file', $notFixedLocation) ? $noFixFile = $notFixedLocation['file'] : $noFixFile = '';
                // $notFixedLocation of file scope is "$noFixFunc === '' && $noFixClass === '' && $paramNumber === 7".
                if ($noFixFunc === ''
                    && $noFixClass === ''
                    && $paramNumber === 8
                ) {
                    continue;
                }
                if ($func === $noFixFunc
                    && $class === $noFixClass
                    && $file === $noFixFile
                ) {
                    $marks = str_repeat($this->_mark, 10);
                    $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $this->_tags['font']['caution'] . $marks . $this->_tags['b'] . ' This function has been not fixed. ' . $this->_tags['/b'] . $marks . $this->_tags['/font']);
                    if ($onceFlag2) {
                        $onceFlag2 = false;
                        $this->addParameterHeaderToLog($pTmpLog, $noFixFile, $line, $func, $class);
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
     * @return void
     */
    protected function outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog = '')
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (!$this->_isLogging) {
            $errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
            $prependLog = htmlspecialchars($prependLog, ENT_QUOTES, 'UTF-8');
        }
        if ($errorKind === 'E_NOTICE') {
            // We had better debug by breakpoint than the display screen in case of "E_NOTICE".
            // Also, breakpoint does not exist in case of release mode.
            return;
        }

        // If this does a log.
        if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) {
            // Locks the error log files.
            $this->_lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
            $this->_lockByFileExisting->lock();
            // When "ErrorLog" directory does not exist.
            $errorLogDirectory = B::getStatic('$_workDir') . '/ErrorLog/';
            if (!is_dir($errorLogDirectory)) {
                // Makes directory, sets permission and sets own user.
                B::mkdir($errorLogDirectory, 0700);
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

            // Gets current error log file name.
            $currentErrorLogFileName = substr(trim(fgets($pVarConfFile)), strlen($this->_keyOfCurrentErrorLogFileName));
            if (B::getStatic('$_os') === 'WIN') { // In case of Windows.
                $this->_errorLogFilePath = strtolower($errorLogDirectory . $currentErrorLogFileName);
            } else { // In case of Unix.
                $this->_errorLogFilePath = $errorLogDirectory . $currentErrorLogFileName;
            }
            if (!is_string($currentErrorLogFileName)) {
                $exceptionMessage = 'Current error log file name should be string.';
                goto END_LABEL;
            }
            // When current error log file does not exist.
            if (!is_file($this->_errorLogFilePath)) {
                // Creates and opens current error log file.
                $this->pErrorLogFile = B::fopen($this->_errorLogFilePath, 'xb', 0600);
            } else {
                // Opens current error log file.
                $this->pErrorLogFile = fopen($this->_errorLogFilePath, 'ab');
            }

            // Reads native call stack paths and its number.
            $nativeCallStackArray = array ();
            while ($varConfLineString = fgets($pVarConfFile)) {
                $varConfLineString = trim($varConfLineString);
                list($key, $value) = explode('?', $varConfLineString);
                $nativeCallStackArray[$key] = $value;
            }
            // The call stack loop.
            $callStackInfoArray = array ();
            foreach ($this->_callStackInfo as $call) {
                if (empty($call)
                    || !array_key_exists('file', $call)
                    || !array_key_exists('line', $call)
                ) {
                    continue;
                }
                if (B::getStatic('$_os') === 'WIN') { // In case of Windows.
                    $path = strtolower($call['file']);
                } else { // In case of Unix.
                    $path = $call['file'];
                }
                // Searches the error file path.
                if (array_key_exists($path, $nativeCallStackArray)) {
                    $pathNumber = $nativeCallStackArray[$path];
                } else {
                    $count = count($nativeCallStackArray);
                    if ($count) {
                        $pathNumber = base_convert($count + 1, 10, 36);
                    } else {
                        $pathNumber = 1;
                    }
                    $nativeCallStackArray[$path] = $pathNumber;
                    // Sets the error path and its number.
                    // Disk access is executed per sector.
                    // Therefore, compresses from error path to sequential number because it is purpose to decrease disk access.
                    fwrite($pVarConfFile, $path . '?' . $pathNumber . PHP_EOL);
                }
                // Creates the call stack information character string.
                $callStackInfoArray[] = base_convert($pathNumber, 36, 10);
                $callStackInfoArray[] = $call['line'];
                if (!isset($errorPathNumber)) {
                    // Sets the error location file number.
                    $errorPathNumber = $pathNumber;
                }
            }
            // Compresses integer array.
            $callStackInfoString = B::compressIntArray($callStackInfoArray);
            // Disk access is executed per sector.
            // Therefore, partitions out error locations to error location files because it is purpose to decrease disk access.
            $errorLocationFilePath = $errorLogDirectory . $errorPathNumber . '.bin';
            // If error location file exists.
            if (is_file($errorLocationFilePath)) {
                // Opens the error location file.
                $pErrorLocationFile = fopen($errorLocationFilePath, 'r+b');
            } else {
                // Creates the error location file.
                $pErrorLocationFile = B::fopen($errorLocationFilePath, 'x+b', 0600);
            }
            $isExisting = false;
            while ($callStackInfoStringLine = fgets($pErrorLocationFile)) {
                if ($callStackInfoStringLine === $callStackInfoString) {
                    $isExisting = true;
                    break;
                }
            }
            if ($isExisting) {
                // Skips same error.
                goto END_LABEL;
            } else {
                // Registers the call stack information character string.
                fseek($pErrorLocationFile, 0, SEEK_END);
                fwrite($pErrorLocationFile, $callStackInfoString);
            }
        }

        $tmp = date('[Y-m-d H:i:s]') . PHP_EOL;
        $dummy = null;
        $this->logBufferWriting($dummy, $this->_tags['pre'] . $prependLog);
        // Create error log from the argument.
        $tmp .= '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' .
            PHP_EOL . $this->_mark . 'Error kind =======>' . $this->_tags['font']['string'] . '\'' . $errorKind . '\'' . $this->_tags['/font'] .
            PHP_EOL . $this->_mark . 'Error message ====>' . $this->_tags['font']['string'] . '\'' . $errorMessage . '\'' . $this->_tags['/font'];
        $this->logBufferWriting($dummy, $tmp);
        // Search array which debug_backtrace() or getTrace() returns, and add a parametric information.
        foreach ($this->_callStackInfo as $backtraceArrays) {
            $onceFlag2 = true;
            $pTmpLog2 = $this->logPointerOpening();
            array_key_exists('file', $backtraceArrays) ? $file = $backtraceArrays['file'] : $file = '';
            array_key_exists('line', $backtraceArrays) ? $line = $backtraceArrays['line'] : $line = '';
            $this->outputFixedFunctionToLogging($backtraceArrays, $dummy, $onceFlag2, $func, $class, $file, $line);
            if (array_key_exists('args', $backtraceArrays)) {
                // Analyze parameters part of trace array, and return character string.
                $this->searchDebugBacktraceArgsToString($pTmpLog2, $backtraceArrays['args']);
                $this->logBufferWriting($pTmpLog2, PHP_EOL . ');');
            }
            $this->addFunctionValuesToLog($pTmpLog, $dummy, $onceFlag2, $func, $class, $line);
            if ($onceFlag2) {
                $this->addParameterHeaderToLog($dummy, $file, $line, $func, $class);
            }
            $this->logBufferWriting($pTmpLog2, PHP_EOL);
            $this->logWriting($pTmpLog2);
        }
        $this->logBufferWriting($dummy, '//////////////////////////////// CALL STACK END ////////////////////////////////');
        $this->logBufferWriting($dummy, $this->_tags['/pre']);

        // If this does a log.
        if ($_BreakpointDebugging_EXE_MODE & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) {
            // When log file size exceeds.
            $errorLogFileStatus = fstat($this->pErrorLogFile);
            if ($errorLogFileStatus['size'] > B::getStatic('$_maxLogFileByteSize')) {
                // Gets next error log file name.
                $nextNumber = substr($currentErrorLogFileName, strlen($this->_prefixOfErrorLogFileName), 1) + 1;
                if ($nextNumber > 8) {
                    // Error log file rotation.
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
            if (is_resource($pErrorLocationFile)) {
                // Closes the error location file.
                fclose($pErrorLocationFile);
            }
            if (is_resource($this->pErrorLogFile)) {
                // Closes current error log file.
                fclose($this->pErrorLogFile);
            }
            // Closes variable configuring file.
            fclose($pVarConfFile);
            // Unlocks the error log files.
            $this->_lockByFileExisting->unlock();
            if ($exceptionMessage) {
                B::internalException($exceptionMessage, 6);
            }
        }
    }

    /**
     * Gets parameter information by adding HTML tags.
     *
     * @param array  $tags       HTML tags.
     * @param string $type       Parameter value type.
     * @param mixed  $paramValue Parameter value.
     *
     * @return string HTML tags.
     */
    protected function getParamInfo($tags, $type, $paramValue)
    {
        return $tags['small'] . $type . $tags['/small'] . ' ' . $tags['font'][$type] . $paramValue . $tags['/font'];
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
    protected function getTypeAndValue(&$pTmpLog, $paramName, $paramValue, $tabNumber)
    {
        if (is_array($paramValue)) {
            if ($paramName === 'GLOBALS') {
                return;
            }
            $this->reflectArray($pTmpLog, $paramName, $paramValue, $tabNumber);
            return;
        } else if (is_object($paramValue)) {
            $this->reflectObject($pTmpLog, $paramName, $paramValue, $tabNumber);
            return;
        }

        $prefix = PHP_EOL . str_repeat("\t", $tabNumber);
        $this->logBufferWriting($pTmpLog, $prefix . $paramName . $this->_tags['font']['=>'] . ' => ' . $this->_tags['/font']);

        if (is_null($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->_tags, 'null', 'null'));
        } else if (is_bool($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->_tags, 'bool', $paramValue ? 'true' : 'false'));
        } else if (is_int($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->_tags, 'int', $paramValue));
        } else if (is_float($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->_tags, 'float', $paramValue));
        } else if (is_string($paramValue)) {
            $paramValue = $this->convertMbString($paramValue);
            $strlen = strlen($paramValue);
            $isOverMaxLogStringSize = false;
            if ($strlen > B::getStatic('$_maxLogStringSize')) {
                $isOverMaxLogStringSize = true;
                $paramValue = substr($paramValue, 0, B::getStatic('$_maxLogStringSize'));
            }
            $paramValue = '"' . $paramValue . '"';
            if (!$this->_isLogging) {
                $paramValue = htmlspecialchars($paramValue, ENT_QUOTES, 'UTF-8');
            }
            if ($isOverMaxLogStringSize === false) {
                $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->_tags, 'string', $paramValue) . $this->_tags['i'] . ' (length=' . $strlen . ')' . $this->_tags['/i']);
            } else {
                $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->_tags, 'string', $paramValue) . $this->_tags['i'] . '... (length=' . $strlen . ')' . $this->_tags['/i']);
            }
        } else if (is_resource($paramValue)) {
            $tmp = $this->_tags['b'] . 'resource' . $this->_tags['/b'] . ' ' .
                $this->_tags['i'] . get_resource_type($paramValue) . $this->_tags['/i'] . ' ' .
                $this->_tags['font']['resource'] . $paramValue . $this->_tags['/font'];
            $this->logBufferWriting($pTmpLog, $tmp);
        } else {
            throw new \BreakpointDebugging_ErrorException('', 2);
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
    protected function searchDebugBacktraceArgsToString(&$pTmpLog, $backtraceParams, $tabNumber = 1)
    {
        $isFirst = true;
        $paramCount = 0;
        foreach ($backtraceParams as $paramName => $paramValue) {
            $paramCount++;
            if ($paramCount > B::getStatic('$_maxLogElementNumber')) {
                $tmp = PHP_EOL . str_repeat("\t", $tabNumber);
                $this->logBufferWriting($pTmpLog, $tmp . ',');
                $tmp = $tmp . "\t.";
                $this->logBufferWriting($pTmpLog, $tmp . $tmp . $tmp);
                break;
            }
            if ($isFirst) {
                $isFirst = false;
            } else {
                $this->logBufferWriting($pTmpLog, PHP_EOL . str_repeat("\t", $tabNumber) . ',');
            }
            $this->getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber);
        }
    }

    /**
     * Open error-log-pointer.
     *
     * @return mixed Error log pointer.
     */
    protected function logPointerOpening()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE & (B::LOCAL_DEBUG | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of local host.
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
    protected function logPointerClosing(&$pTmpLog)
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
    protected function logWriting(&$pTmpLog)
    {
        rewind($pTmpLog);
        while (!feof($pTmpLog)) {
            fwrite($this->pErrorLogFile, fread($pTmpLog, 4096));
        }
        // Delete temporary file.
        fclose($pTmpLog);
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
    protected function logBufferWriting(&$pLogBuffer, $log)
    {
        if ($pLogBuffer === null) {
            fwrite($this->pErrorLogFile, $log);
        } else {
            fwrite($pLogBuffer, $log);
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
    protected function logCombination(&$pTmpLog, &$pTmpLog2)
    {
        rewind($pTmpLog2);
        if ($pTmpLog === null) {
            while (!feof($pTmpLog2)) {
                fwrite($this->pErrorLogFile, fread($pTmpLog2, 4096));
            }
        } else {
            while (!feof($pTmpLog2)) {
                fwrite($pTmpLog, fread($pTmpLog2, 4096));
            }
        }
        $this->logPointerClosing($pTmpLog2);
    }

}

global $_BreakpointDebugging_EXE_MODE;

if ($_BreakpointDebugging_EXE_MODE === B::RELEASE) { // In case of release.
    /**
     * Dummy class for release.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    final class BreakpointDebugging_Error extends \BreakpointDebugging_Error_InAllCase
    {

    }

} else { // In case of not release.
    include_once __DIR__ . '/Error_Option.php';
}

?>