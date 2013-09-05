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
    protected $uintTestAnchorHref = '<a href="#UnitTestAnchor">To unit test error information.</a>';
    protected $uintTestAnchorName = '<a name="UnitTestAnchor"></a>';

    /**
     * @var array Logged call-stacks.
     */
    private $_loggedCallStacks = array ();

    /**
     * @var array Logged arrays.
     */
    private $_loggedArrays = array ();

    /**
     * @var array Logged objects.
     */
    private $_loggedObjects = array ();

    /**
     * @var string Variable configuring file name.
     */
    private $_varConfFileName = 'ErrorLog.var.conf';

    /**
     * @const string Key of enabled error log file name.
     */
    const ENABLED_ERROR_LOG_FILE_NAME = 'ENABLED_ERROR_LOG_FILE_NAME_=';

    /**
     * @const string Key of disabled error log file name.
     */
    const DISABLED_ERROR_LOG_FILE_NAME = 'DISABLED_ERROR_LOG_FILE_NAME=';

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
     * @var resource Current error log file size.
     */
    private $_pCurrentErrorLogFileSize;

    /**
     * @var resource Error log file pointer.
     */
    protected $pErrorLogFile;

    /**
     * @var array Call stack information.
     */
    private $_callStack;

    /**
     * @var bool Is logging?
     */
    protected $isLogging;

    /**
     * @var string Mark.
     */
    protected $mark;

    /**
     * @var array HTML tags.
     */
    protected $tags = array ();

    /**
     * @var object Locking object.
     */
    private $_lockByFileExisting;

    /**
     * @var string Current error log filename.
     */
    private $_currentErrorLogFileName;

    /**
     * @var string Full path of error log directory.
     */
    private $_errorLogDirectory;

    /**
     * @var resource Variable configuring file.
     */
    private $_pVarConfFile;

    /**
     * @var int Maximum log file byte size.
     */
    protected $maxLogFileByteSize;

    /**
     * @var string The log byte size.
     */
    protected $logByteSize = 0;

    /**
     * @var bool Error flag of once.
     */
    private static $_onceFlag = true;

    /**
     * @var string Error log directory name.
     */
    private static $_errorLogDir = '/ErrorLog/';

    /**
     * Gets error log directory name.
     *
     * @return string Error log directory name.
     */
    static function getErrorLogDir()
    {
        return self::$_errorLogDir;
    }

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
        $tags['font']['blue'] = '';
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
        $this->maxLogFileByteSize = B::getStatic('$_maxLogFileByteSize');
        $this->isLogging = true;
        $this->mark = '#';
        $this->setHTMLTags($this->tags);
        $this->tags['pre'] = '';
        $this->tags['/pre'] = PHP_EOL . PHP_EOL;
        $this->tags['i'] = '';
        $this->tags['/i'] = '';
        $this->tags['b'] = '';
        $this->tags['/b'] = '';
        if (B::getStatic('$exeMode') & B::UNIT_TEST) {
            $this->tags['uint test anchor href'] = $this->uintTestAnchorHref;
            $this->tags['uint test anchor name'] = $this->uintTestAnchorName;
        } else {
            $this->tags['uint test anchor href'] = '';
            $this->tags['uint test anchor name'] = '';
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
        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8'
            || $charSet === 'ASCII'
        ) {
            return $string;
        } else if ($charSet === false) {
            $message = 'This isn\'t single character sets.';
            if (self::$_onceFlag) {
                self::$_onceFlag = false;
                B::internalException($message, 3);
                // @codeCoverageIgnoreStart
            }
            // @codeCoverageIgnoreEnd
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
                    && $tabs !== ''
                ) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
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
                        $this->logBufferWriting($pTmpLog2, PHP_EOL . $tabs . $this->mark . 'Function values ==>');
                        $onceFlag = true;
                    }
                    // Analyze values part of trace array, and return character string.
                    $this->searchDebugBacktraceArgsToString($pTmpLog2, $trace['values'], strlen($tabs) + 1);
                }
            }
        }
    }

    /**
     * Lowers hypertext reference anchor.
     *
     * @param string $referenceName Reference name of hypertext.
     */
    private function _lowerHypertextReferenceAnchor($referenceName)
    {
        if (B::getStatic('$exeMode') & B::RELEASE) {
            return 'same ' . $referenceName;
        } else {
            return '<a href="#' . $referenceName . '">same ' . $referenceName . '</a>';
        }
    }

    /**
     * Sets hypertext reference.
     *
     * @param string $referenceName Reference name of hypertext.
     */
    private function _setHypertextReference($referenceName)
    {
        if (B::getStatic('$exeMode') & B::RELEASE) {
            return $referenceName;
        } else {
            return '<a name="' . $referenceName . '">' . $referenceName . '</a>';
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
        $this->checkLogByteSize($pTmpLog);
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
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font'] . $this->tags['b'] . $this->_lowerHypertextReferenceAnchor('array #' . $loggedArrayNumber) . $this->tags['/b'] . ' (');
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
                goto AFTER_TREATMENT;
            }
        }
        // Memory is not used unless value is overwritten.
        $this->_loggedArrays[] = $array;
        $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font'] . $this->tags['b'] . $this->_setHypertextReference('array #' . count($this->_loggedArrays)) . $this->tags['/b'] . ' (');
        // Beyond max log param nesting level.
        if ($tabNumber >= B::getStatic('$_maxLogParamNestingLevel')) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
        } else {
            foreach ($array as $paramName => $paramValue) {
                if (is_string($paramName)) {
                    $paramName = '\'' . $paramName . '\'';
                }
                if (is_array($paramValue)) {
                    // Reduces call stack nest level.
                    $this->reflectArray($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
                } else if (is_object($paramValue)) {
                    // Reduces call stack nest level.
                    $this->reflectObject($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
                } else {
                    $this->getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
                }
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
        $this->checkLogByteSize($pTmpLog);
        $className = get_class($object);
        $tabs = str_repeat("\t", $tabNumber);
        $classReflection = new \ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        $constants = $classReflection->getConstants();

        foreach ($this->_loggedObjects as $loggedObjectNumber => $loggedObject) {
            if ($loggedObject === $object) {
                // Skips same object.
                $loggedObjectNumber++;
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font'] . $this->tags['b'] . $this->_lowerHypertextReferenceAnchor('class object #' . $loggedObjectNumber) . ' ' . $this->tags['/b'] . $this->tags['i'] . $className . $this->tags['/i'] . PHP_EOL . $tabs . '{');
                $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
                goto AFTER_TREATMENT;
            }
        }
        $this->_loggedObjects[] = $object;
        $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font'] . $this->tags['b'] . $this->_setHypertextReference('class object #' . count($this->_loggedObjects)) . ' ' . $this->tags['/b'] . $this->tags['i'] . $className . $this->tags['/i'] . PHP_EOL . $tabs . '{');

        // Beyond max log param nesting level.
        if ($tabNumber >= B::getStatic('$_maxLogParamNestingLevel')) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . "\t...");
        } else {
            foreach ($constants as $constName => $constValue) {
                $this->getTypeAndValue($pTmpLog, $this->tags['i'] . 'const ' . $this->tags['/i'] . $constName, $constValue, $tabNumber + 1);
            }
            count($constants) ? $this->logBufferWriting($pTmpLog, PHP_EOL) : null;
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
                if (is_array($paramValue)) {
                    // Clears recursive array element.
                    $paramValue = B::clearRecursiveArrayElement($paramValue);
                    // Reduces call stack nest level.
                    $this->reflectArray($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
                } else if (is_object($paramValue)) {
                    // Reduces call stack nest level.
                    $this->reflectObject($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
                } else {
                    $this->getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber + 1);
                }
            }
        }
        AFTER_TREATMENT:
        $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . '}');
    }

    /**
     * This is Called as global exception handler.
     *
     * @param object $pException Exception info.
     * @param string $prependLog This prepend this parameter logging.
     *
     * @return void
     */
    function handleException2($pException, $prependLog)
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
            if (B::getStatic('$_callingExceptionHandlerDirectly')) { // Has been called from "BreakpointDebugging_InAllCase::callExceptionHandlerDirectly()" method.
                // @codeCoverageIgnoreStart
                $callingExceptionHandlerDirectly = &B::refStatic('$_callingExceptionHandlerDirectly');
                $callingExceptionHandlerDirectly = false;
                // Array top is set to location which "self::internalException()" is called  because this location is registered to logging.
                unset($callStackInfo[0]);
            } else {
                // @codeCoverageIgnoreEnd
                // Array top is set to location which throws exception because this location is registered to logging.
                array_unshift($callStackInfo, array ('file' => $pCurrentException->getFile(), 'line' => $pCurrentException->getLine()));
            }
            // Clears recursive array element.
            $this->_callStack = B::clearRecursiveArrayElement($callStackInfo);
            // Add scope of start page file.
            $this->_callStack[] = array ();
            $errorMessage = $this->convertMbString($pCurrentException->getMessage());
            $this->outputErrorCallStackLog2(get_class($pCurrentException), $errorMessage, $prependLog);
        }

        B::breakpoint($errorMessage, $this->_callStack);
    }

    /**
     * This is Called as global error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     * @param string $prependLog   This prepend this parameter logging.
     * @param array  $callStack    The call stack.
     *
     * @return void
     */
    function handleError2($errorNumber, $errorMessage, $prependLog, $callStack)
    {
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
                B::internalException('', 5);
        }

        $errorMessage = $this->convertMbString($errorMessage);
        $prependLog = $this->convertMbString($prependLog);

        // Clears recursive array element.
        $this->_callStack = B::clearRecursiveArrayElement($callStack);
        // Sets location which triggers error to top of call stack array because this handler must log this location.
        unset($this->_callStack[0]);
        // Add scope of start page file.
        $this->_callStack[] = array ();
        $this->outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog);
        if (B::getStatic('$exeMode') === (B::REMOTE | B::RELEASE)) { // In case of remote release.
            // @codeCoverageIgnoreStart
            if (isset($endFlag)) {
                // In case of release mode, we must exit this process when kind is error.
                exit;
            }
        }
        // @codeCoverageIgnoreEnd
        B::breakpoint($errorMessage, $this->_callStack);
        // We can do step execution to error location to see variable value even though kind is error.
    }

    /**
     * Displays error log in case of debug, or logs error log in case of release.
     *
     * @param type $message   Message.
     * @param type $callStack The call stack information.
     *
     * @return void
     */
    private static function _handleInternal($message, $callStack)
    {
        // Does not handle error. Also, displays "XDebug" error except remote release.
        restore_error_handler(); // Restores from "self::handleInternalError()".
        restore_error_handler(); // Restores from "B::handleError()".

        try {
            // trigger_error('Internal error test.', E_USER_WARNING); // For debug.
            // Controls how many nested levels of array elements and object properties.
            // Display by var_dump(), local variables or Function Traces.
            ini_set('xdebug.var_display_max_depth', '6');

            $log = 'Internal error message: ' . $message . PHP_EOL;
            $log .= 'The call stack:' . PHP_EOL;
            foreach ($callStack as $value) {
                ob_start();
                var_dump($value);
                $log .= ob_get_clean();
            }
            $log = B::convertMbString($log);
            // If this does a log.
            if (B::getStatic('$exeMode') & B::RELEASE) {
                $errorLogDirectory = B::getStatic('$_workDir') . self::$_errorLogDir;
                $logFileName = 'InternalError.log';
                $errorLogFilePath = $errorLogDirectory . $logFileName;
                // Locks internal error log file.
                $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
                $lockByFileExisting->lock();
                // When "ErrorLog" directory does not exist.
                if (!is_dir($errorLogDirectory)) {
                    // Makes directory, sets permission and sets own user.
                    B::mkdir(array ($errorLogDirectory, 0700));
                }
                // Strips HTML and PHP tags.
                $log = strip_tags($log);
                // Decodes HTML special characters.
                $log = htmlspecialchars_decode($log, ENT_QUOTES);
                $log .= '////////////////////////////////////////////////////////////////////////////////' . PHP_EOL;
                // Opens error log file as created and written.
                $pErrorLogFile = B::fopen(array ($errorLogFilePath, 'ab'));
                $fileStatus = fstat($pErrorLogFile);
                // If file size is smaller than 1MB.
                if ($fileStatus['size'] < (1 << 20)) {
                    // Writes error log file.
                    fwrite($pErrorLogFile, $log);
                }
                // If file size is bigger than 1MB.
                if ($fileStatus['size'] > (1 << 20)) {
                    // Truncates the file size as 1MB.
                    ftruncate($pErrorLogFile, 1 << 20);
                }
                // Closes error log file.
                fclose($pErrorLogFile);
                // Unlocks the error log files.
                $lockByFileExisting->unlock();
            } else { // If this displays.
                echo '<pre>' . $log . '</pre>';
            }
            B::breakpoint($message, $callStack);
        } catch (\Exception $e) {
            $callStack = $e->getTrace();
            array_unshift($callStack, array ('file' => $e->getFile(), 'line' => $e->getLine()));
            B::breakpoint($e->getMessage(), $callStack);
        }
    }

    /**
     * Global internal exception handler.
     *
     * @param object $pException Exception information.
     *
     * @return void
     */
    static function handleInternalException($pException)
    {
        B::limitAccess('BreakpointDebugging.php');

        for ($pCurrentException = $pException; $pCurrentException; $pCurrentException = $pCurrentException->getPrevious()) {
            $pExceptions[] = $pCurrentException;
        }
        $pExceptions = array_reverse($pExceptions);

        foreach ($pExceptions as $pException) {
            $callStack = $pException->getTrace();
            array_unshift($callStack, array ('file' => $pException->getFile(), 'line' => $pException->getLine()));
            self::_handleInternal($pException->getMessage(), $callStack);
        }
        exit;
    }

    /**
     * Global internal error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     *
     * @return void
     */
    static function handleInternalError($errorNumber, $errorMessage)
    {
        $callStack = debug_backtrace();
        unset($callStack[0]);
        self::_handleInternal($errorMessage, $callStack);
        exit;
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
        $className = B::fullFilePathToClassName($file);
        if ($className
            && is_subclass_of($className, 'PHPUnit_Framework_Test')) {
            $this->logBufferWriting($pTmpLog, $this->tags['uint test anchor name']);
            $this->tags['uint test anchor name'] = '';
            if ($file) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->mark . 'Error file =======>' . $this->tags['font']['string'] . $this->tags['b'] . '\'' . $file . '\'' . $this->tags['/b'] . $this->tags['/font']);
            }
            if ($line) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->mark . 'Error line =======>' . $this->tags['font']['int'] . $this->tags['b'] . $line . $this->tags['/b'] . $this->tags['/font']);
            }
        } else {
            if ($file) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->mark . 'Error file =======>' . $this->tags['font']['string'] . '\'' . $file . '\'' . $this->tags['/font']);
            }
            if ($line) {
                $this->logBufferWriting($pTmpLog, PHP_EOL . $this->mark . 'Error line =======>' . $this->tags['font']['int'] . $line . $this->tags['/font']);
            }
        }
        if ($class) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $this->mark . 'Error class ======>' . $this->tags['i'] . $class . $this->tags['/i']);
        }
        if ($func) {
            $this->logBufferWriting($pTmpLog, PHP_EOL . $this->mark . 'Error function ===>' . $this->tags['i'] . $func . $this->tags['/i'] . '( ');
        }
    }

    /**
     * Output fixed-function to logging.
     *
     * @param array  $callStack  The call stack.
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
    protected function outputFixedFunctionToLogging($callStack, &$pTmpLog, &$onceFlag2, &$func, &$class, $file, $line, $tabs = '')
    {
        array_key_exists('function', $callStack) ? $func = $callStack['function'] : $func = '';
        array_key_exists('class', $callStack) ? $class = $callStack['class'] : $class = '';
        if (is_array(B::getStatic('$_notFixedLocations'))) {
            foreach (B::getStatic('$_notFixedLocations') as $notFixedLocation) {
                array_key_exists('function', $notFixedLocation) ? $noFixFunc = $notFixedLocation['function'] : $noFixFunc = '';
                array_key_exists('class', $notFixedLocation) ? $noFixClass = $notFixedLocation['class'] : $noFixClass = '';
                array_key_exists('file', $notFixedLocation) ? $noFixFile = $notFixedLocation['file'] : $noFixFile = '';
                // $notFixedLocation of file scope is "$noFixFunc === '' && $noFixClass === '' && $tabs !== ''".
                if ($noFixFunc === ''
                    && $noFixClass === ''
                    && $tabs !== ''
                ) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                if ($func === $noFixFunc
                    && $class === $noFixClass
                    && $file === $noFixFile
                ) {
                    $marks = str_repeat($this->mark, 10);
                    $this->logBufferWriting($pTmpLog, PHP_EOL . $tabs . $this->tags['font']['caution'] . $this->tags['b'] . $marks . ' This function has been not fixed. ' . $marks . $this->tags['/b'] . $this->tags['/font']);
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
     * Changes the log file.
     *
     * @param mixed $pTmpLog Error temporary log pointer.
     *
     * @return void
     * @throw \BreakpointDebugging_OutOfLogRangeException
     */
    protected function changeLogFile($pTmpLog)
    {
        // In case of writing from top of file.
        if ($this->_pCurrentErrorLogFileSize === 0) {
            // Shortens to specification size.
            $continuingMark = PHP_EOL . str_repeat("\t", 1) . '.';
            $continuingMark = PHP_EOL . '### Omits since then because it exceeded logfile maximum capacity. ###' . $continuingMark . $continuingMark . $continuingMark;
            if (is_resource($pTmpLog)) {
                $errorLogFileStatus1 = fstat($this->pErrorLogFile);
                ftruncate($pTmpLog, $this->maxLogFileByteSize - $errorLogFileStatus1['size'] - strlen($continuingMark));
                fseek($pTmpLog, 0, SEEK_END);
            }
            $this->logBufferWriting($pTmpLog, $continuingMark);
            $this->logWriting($pTmpLog);
            // This exception is caught inside handler.
            throw new \BreakpointDebugging_OutOfLogRangeException('', 101);
        }
        $this->_pCurrentErrorLogFileSize = 0;
        // Gets next error log file name.
        $nextNumber = substr($this->_currentErrorLogFileName, strlen($this->_prefixOfErrorLogFileName), 1)
            + 1;
        if ($nextNumber > 8) {
            // Error log file rotation.
            $nextNumber = 1;
        }
        $nextErrorLogFilePath = $this->_errorLogDirectory . $this->_prefixOfErrorLogFileName . $nextNumber . $this->_errorLogFileExt;
        // When next error log file exists.
        if (is_file($nextErrorLogFilePath)) {
            // Deletes next error log file.
            B::unlink(array ($nextErrorLogFilePath));
        }
        // Seeks to error log file number.
        fseek($this->_pVarConfFile, strlen(self::ENABLED_ERROR_LOG_FILE_NAME . $this->_prefixOfErrorLogFileName));

        // Sets next error log file name number to variable configuring file.
        fwrite($this->_pVarConfFile, $nextNumber);
        // Creates current error log filename.
        $this->_currentErrorLogFileName = substr_replace($this->_currentErrorLogFileName, $nextNumber . $this->_errorLogFileExt, strlen($this->_prefixOfErrorLogFileName));
    }

    /**
     * Checks the log byte size.
     *
     * @param mixed $pTmpLog Error temporary log pointer.
     *
     * @return void
     */
    protected function checkLogByteSize($pTmpLog)
    {
        if (!isset($pTmpLog)) {
            return;
        }
        // When log file size exceeds.
        $errorLogFileStatus1 = fstat($this->pErrorLogFile);
        $errorLogFileStatus2 = fstat($pTmpLog);
        if ($this->_pCurrentErrorLogFileSize + $errorLogFileStatus1['size'] + $errorLogFileStatus2['size'] > $this->maxLogFileByteSize) {
            $this->changeLogFile($pTmpLog);
        }
    }

    /**
     * Repairs the logging system file.
     *
     * @return void
     */
    private function _repairLoggingSystemFile()
    {
        // Repairs variable configuring file.
        $lines = array ();
        // Gets first line.
        $lines[] = self::ENABLED_ERROR_LOG_FILE_NAME . fgets($this->_pVarConfFile);
        while ($line = fgets($this->_pVarConfFile)) {
            $lines[] = $line;
        }
        $isWritten = false;
        $fileNumbers = array ();
        for ($count = count($lines) - 1; $count >= 1; $count--) {
            $line = $lines[$count];
            $matches = array ();
            $result = preg_match('`^ ([^?]+) \? ([[:alnum:]]+) \r? \n $`xX', $line, $matches);
            if ($result !== 1 // If not matches.
                || !is_file($matches[1]) // If error "*.php" file does not exist.
            ) {
                $isWritten = true;
                // Deletes incorrect line.
                $lines[$count] = PHP_EOL;
                if (array_key_exists(2, $matches)) {
                    $errorLocationFileName = $this->_errorLogDirectory . $matches[2] . '.bin';
                    if (is_file($errorLocationFileName)) {
                        // Deletes the error location file.
                        B::unlink(array ($this->_errorLogDirectory . $matches[2] . '.bin'));
                    }
                }
                continue;
            }
            $fileNumbers[] = base_convert($matches[2], 36, 10);
        }
        $line1pre = self::ENABLED_ERROR_LOG_FILE_NAME . 'php_error_';
        $line1sa = '.log' . PHP_EOL;
        $line1 = $line1pre . '1' . $line1sa;
        $line = $lines[$count];
        if (preg_match("`^ $line1pre [1-8] $line1sa`xXD", $line) !== 1) {
            $isWritten = true;
            // Repairs incorrect line.
            $lines[$count] = $line1;
        }
        if ($isWritten) {
            rewind($this->_pVarConfFile);
            foreach ($lines as $line) {
                fwrite($this->_pVarConfFile, $line);
            }
            ftruncate($this->_pVarConfFile, ftell($this->_pVarConfFile));
        }

        // Repairs the error location files.
        foreach (scandir($this->_errorLogDirectory) as $errorLocationFileName) {
            $errorLocationFileName = $this->_errorLogDirectory . $errorLocationFileName;
            if (!is_file($errorLocationFileName)
                || pathinfo($errorLocationFileName, PATHINFO_EXTENSION) !== 'bin'
            ) {
                continue;
            }
            // Opens the error location file.
            $pErrorLocationFile = B::fopen(array ($errorLocationFileName, 'r+b'));
            $lines = array ();
            while ($line = fgets($pErrorLocationFile)) {
                $lines[] = $line;
            }
            for ($count = count($lines) - 1; $count >= 0; $count--) {
                $callStackInfoArray = B::decompressIntArray($lines[$count]);
                $lineNumber = count($callStackInfoArray);
                if ($lineNumber % 2 !== 0 // If the decompressed data is not even number.
                    || $lineNumber - 2 < 0 // If the decompressed data is less than 2.
                ) {
                    // Deletes incorrect line.
                    unset($lines[$count]);
                    continue;
                }
                for ($count2 = $lineNumber - 2; $count2 >= 0; $count2 -= 2) {
                    // If error file number does not exist.
                    if (!in_array($callStackInfoArray[$count2], $fileNumbers)) {
                        unset($lines[$count]);
                        continue 2;
                    }
                }
            }
            rewind($pErrorLocationFile);
            foreach ($lines as $line) {
                fwrite($pErrorLocationFile, $line);
            }
            ftruncate($pErrorLocationFile, ftell($pErrorLocationFile));
            fclose($pErrorLocationFile);
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
        if (!$this->isLogging) {
            // @codeCoverageIgnoreStart
            $errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
            $prependLog = htmlspecialchars($prependLog, ENT_QUOTES, 'UTF-8');
        }
        // @codeCoverageIgnoreEnd
        if ($errorKind === 'E_NOTICE') {
            // We had better debug by breakpoint than the display screen in case of "E_NOTICE".
            // Also, breakpoint does not exist in case of release mode.
            return;
        }

        try {
            // If this does a log.
            if (B::getStatic('$exeMode') & B::RELEASE) {
                // Locks the error log files.
                $this->_lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
                $this->_lockByFileExisting->lock();
                // When "ErrorLog" directory does not exist.
                $this->_errorLogDirectory = B::getStatic('$_workDir') . self::$_errorLogDir;
                if (!is_dir($this->_errorLogDirectory)) {
                    // Makes directory, sets permission and sets own user.
                    B::mkdir(array ($this->_errorLogDirectory, 0700));
                }
                $varConfFilePath = $this->_errorLogDirectory . $this->_varConfFileName;
                // If variable configuring file exists.
                if (is_file($varConfFilePath)) {
                    // Opens variable configuring file.
                    $this->_pVarConfFile = B::fopen(array ($varConfFilePath, 'r+b'));
                    // Checks the abnormal termination.
                    $logEnableString = fread($this->_pVarConfFile, strlen(self::DISABLED_ERROR_LOG_FILE_NAME));
                    if ($logEnableString !== self::DISABLED_ERROR_LOG_FILE_NAME) {
                        $this->_repairLoggingSystemFile($logEnableString);
                    } else {
                        rewind($this->_pVarConfFile);
                        fwrite($this->_pVarConfFile, self::ENABLED_ERROR_LOG_FILE_NAME);
                    }
                } else {
                    // Creates and opens variable configuring file.
                    $this->_pVarConfFile = B::fopen(array ($varConfFilePath, 'x+b'));
                    // Sets current error log file name.
                    fwrite($this->_pVarConfFile, self::ENABLED_ERROR_LOG_FILE_NAME . $this->_prefixOfErrorLogFileName . '1.log' . PHP_EOL);
                }
                fflush($this->_pVarConfFile);
                rewind($this->_pVarConfFile);

                // Gets current error log file name.
                $this->_currentErrorLogFileName = substr(trim(fgets($this->_pVarConfFile)), strlen(self::ENABLED_ERROR_LOG_FILE_NAME));
                $this->_errorLogFilePath = $this->_errorLogDirectory . $this->_currentErrorLogFileName;
                // When current error log file does not exist.
                if (!is_file($this->_errorLogFilePath)) {
                    // Creates and opens current error log file.
                    $this->_pCurrentErrorLogFileSize = 0;
                } else {
                    // Opens current error log file.
                    $errorLogFileStatus = stat($this->_errorLogFilePath);
                    $this->_pCurrentErrorLogFileSize = $errorLogFileStatus['size'];
                }
                // Reads native call stack paths and its number.
                $nativeCallStackArray = array ();
                $count = 1;
                while ($varConfLineString = fgets($this->_pVarConfFile)) {
                    $key = base_convert($count, 10, 36);
                    if ($varConfLineString === PHP_EOL) {
                        $nativeCallStackArray[$key] = PHP_EOL;
                    } else {
                        $varConfLineString = trim($varConfLineString);
                        list($value, $dummy) = explode('?', $varConfLineString);
                        $nativeCallStackArray[$key] = $value;
                    }
                    $count++;
                }
                // The call stack loop.
                $callStackInfoArray = array ();
                foreach ($this->_callStack as $call) {
                    if (empty($call)
                        || !array_key_exists('file', $call)
                        || !array_key_exists('line', $call)
                    ) {
                        continue;
                    }
                    $path = $call['file'];
                    // Searches the error file path.
                    while (true) {
                        foreach ($nativeCallStackArray as $key => $nativeCallStack) {
                            if ($path === $nativeCallStackArray[$key]) {
                                $pathNumber = $key;
                                break 2;
                            }
                        }
                        $count = count($nativeCallStackArray);
                        if ($count) {
                            $pathNumber = base_convert($count + 1, 10, 36);
                        } else {
                            $pathNumber = 1;
                        }
                        $nativeCallStackArray[$pathNumber] = $path;
                        // Sets the error path and its number.
                        // Disk access is executed per sector.
                        // Therefore, compresses from error path to sequential number because it is purpose to decrease disk access.
                        fwrite($this->_pVarConfFile, $path . '?' . $pathNumber . PHP_EOL);
                        break;
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
                $errorLocationFilePath = $this->_errorLogDirectory . $errorPathNumber . '.bin';
                // If error location file exists.
                if (is_file($errorLocationFilePath)) {
                    // Opens the error location file.
                    $pErrorLocationFile = B::fopen(array ($errorLocationFilePath, 'r+b'));
                } else {
                    // Creates the error location file.
                    $pErrorLocationFile = B::fopen(array ($errorLocationFilePath, 'x+b'));
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

            $this->pErrorLogFile = $this->logPointerOpening();
            $tmp = date('[Y-m-d H:i:s]') . PHP_EOL;
            $dummy = null;
            $this->logBufferWriting($dummy, $this->tags['pre'] . $prependLog);
            // Create error log from the argument.
            $tmp .= '/////////////////////////////// CALL STACK BEGIN ///////////////////////////////' .
                PHP_EOL . $this->mark . 'Error kind =======>' . $this->tags['font']['string'] . '\'' . $errorKind . '\'' . $this->tags['/font'] .
                PHP_EOL . $this->mark . 'Error message ====>' . $this->tags['font']['string'] . '\'' . $errorMessage . '\'' . $this->tags['/font'] .
                PHP_EOL . $this->tags['uint test anchor href'] .
                PHP_EOL;
            $this->logBufferWriting($dummy, $tmp);
            // Search array which debug_backtrace() or getTrace() returns, and add a parametric information.
            foreach ($this->_callStack as $call) {
                $onceFlag2 = true;
                $pTmpLog2 = $this->logPointerOpening();
                if ($call === array ()) {
                    continue;
                }
                if ($call === '') {
                    $this->logBufferWriting($pTmpLog2, PHP_EOL . 'Omits call stack because exceeded "\BreakpointDebugging::$_maxLogElementNumber".' . PHP_EOL . "\t." . PHP_EOL . "\t." . PHP_EOL . "\t." . PHP_EOL);
                    $this->logWriting($pTmpLog2);
                    continue;
                }
                B::assert(is_array($call), 102);
                foreach ($this->_loggedCallStacks as $loggedCallNumber => $loggedCall) {
                    if ($loggedCall === $call) {
                        // Skips same call stack.
                        $loggedCallNumber++;
                        $this->logBufferWriting($dummy, PHP_EOL . $this->tags['b'] . $this->_lowerHypertextReferenceAnchor('function call #' . $loggedCallNumber) . $this->tags['/b'] . " ...");
                        $file = '';
                        $line = '';
                        $func = '';
                        $class = '';
                        goto AFTER_TREATMENT;
                    }
                }
                array_key_exists('file', $call) ? $file = $call['file'] : $file = '';
                array_key_exists('line', $call) ? $line = $call['line'] : $line = '';
                $this->_loggedCallStacks[] = $call;
                $this->logBufferWriting($dummy, PHP_EOL . $this->tags['b'] . $this->_setHypertextReference('function call #' . count($this->_loggedCallStacks)) . $this->tags['/b']);
                $this->outputFixedFunctionToLogging($call, $dummy, $onceFlag2, $func, $class, $file, $line);
                if (is_array($call)
                    && array_key_exists('args', $call)
                ) {
                    // Analyze parameters part of trace array, and return character string.
                    $this->searchDebugBacktraceArgsToString($pTmpLog2, $call['args']);
                    $this->logBufferWriting($pTmpLog2, PHP_EOL . ');');
                }
                $this->addFunctionValuesToLog($pTmpLog, $dummy, $onceFlag2, $func, $class, $line);

                AFTER_TREATMENT:
                if ($onceFlag2) {
                    $this->addParameterHeaderToLog($dummy, $file, $line, $func, $class);
                }
                $this->logBufferWriting($pTmpLog2, PHP_EOL);
                $this->checkLogByteSize($pTmpLog2);
                $this->logWriting($pTmpLog2);
            }
            $this->logBufferWriting($dummy, '//////////////////////////////// CALL STACK END ////////////////////////////////');
            $this->logBufferWriting($dummy, $this->tags['/pre']);
        } catch (\BreakpointDebugging_OutOfLogRangeException $e) {

        }

        // If this does a log.
        if (B::getStatic('$exeMode') & B::RELEASE) {
            // Gets current error log file name.
            $this->_errorLogFilePath = $this->_errorLogDirectory . $this->_currentErrorLogFileName;
            // When current error log file does not exist.
            if (!is_file($this->_errorLogFilePath)) {
                // Creates and opens current error log file.
                $pErrorLogFile = B::fopen(array ($this->_errorLogFilePath, 'xb'));
            } else {
                // Opens current error log file.
                $pErrorLogFile = B::fopen(array ($this->_errorLogFilePath, 'ab'));
            }
            // Writes to error log.
            $this->logWriting($this->pErrorLogFile, $pErrorLogFile);
            // Closes current error log file.
            fclose($pErrorLogFile);

            END_LABEL:
            if (is_resource($pErrorLocationFile)) {
                // Closes the error location file.
                fclose($pErrorLocationFile);
            }
            rewind($this->_pVarConfFile);
            fwrite($this->_pVarConfFile, self::DISABLED_ERROR_LOG_FILE_NAME);
            // Closes variable configuring file.
            fclose($this->_pVarConfFile);
            // Unlocks the error log files.
            $this->_lockByFileExisting->unlock();
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
            // Clears recursive array element.
            $paramValue = B::clearRecursiveArrayElement($paramValue);
            $this->reflectArray($pTmpLog, $paramName, $paramValue, $tabNumber);
            return;
        } else if (is_object($paramValue)) {
            $this->reflectObject($pTmpLog, $paramName, $paramValue, $tabNumber);
            return;
        }

        $prefix = PHP_EOL . str_repeat("\t", $tabNumber);
        $this->logBufferWriting($pTmpLog, $prefix . $paramName . $this->tags['font']['=>'] . ' => ' . $this->tags['/font']);

        if (is_null($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->tags, 'null', 'null'));
        } else if (is_bool($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->tags, 'bool', $paramValue ? 'true' : 'false'));
        } else if (is_int($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->tags, 'int', $paramValue));
        } else if (is_float($paramValue)) {
            $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->tags, 'float', $paramValue));
        } else if (is_string($paramValue)) {
            $paramValue = $this->convertMbString($paramValue);
            $strlen = strlen($paramValue);
            $isOverMaxLogStringSize = false;
            if ($strlen > B::getStatic('$_maxLogStringSize')) {
                $isOverMaxLogStringSize = true;
                $paramValue = substr($paramValue, 0, B::getStatic('$_maxLogStringSize'));
            }
            $paramValue = '"' . $paramValue . '"';
            if (!$this->isLogging) {
                // @codeCoverageIgnoreStart
                $paramValue = htmlspecialchars($paramValue, ENT_QUOTES, 'UTF-8');
            }
            // @codeCoverageIgnoreEnd
            if ($isOverMaxLogStringSize === false) {
                $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->tags, 'string', $paramValue) . $this->tags['i'] . ' (length=' . $strlen . ')' . $this->tags['/i']);
            } else {
                $this->logBufferWriting($pTmpLog, $this->getParamInfo($this->tags, 'string', $paramValue) . $this->tags['i'] . '... (length=' . $strlen . ')' . $this->tags['/i']);
            }
        } else if (is_resource($paramValue)) {
            $tmp = $this->tags['b'] . 'resource' . $this->tags['/b'] . ' ' .
                $this->tags['i'] . get_resource_type($paramValue) . $this->tags['/i'] . ' ' .
                $this->tags['font']['resource'] . $paramValue . $this->tags['/font'];
            $this->logBufferWriting($pTmpLog, $tmp);
        } else if (preg_match("`^Resource [[:blank:]]+ id [[:blank:]]+ #[[:digit:]]+$`xX", (string) $paramValue)) {
            $tmp = $this->tags['b'] . 'closed resource' . $this->tags['/b'] . ' ' .
                $this->tags['i'] . get_resource_type($paramValue) . $this->tags['/i'] . ' ' .
                $this->tags['font']['resource'] . $paramValue . $this->tags['/font'];
            $this->logBufferWriting($pTmpLog, $tmp);
        } else {
            // @codeCoverageIgnoreStart
            B::internalException('', 2);
            // @codeCoverageIgnoreEnd
        }
        return false;
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
        return tmpfile();
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
        fclose($pTmpLog);
        $pTmpLog = null;
    }

    /**
     * Error log writing.
     * This reduces amount of memory consumption in case of production server.
     *
     * @param mixed &$pTmpLog Error temporary log pointer.
     * @param mixed $pLog     Error log pointer.
     *
     * @return void
     */
    protected function logWriting(&$pTmpLog, $pLog = false)
    {
        rewind($pTmpLog);
        while (!feof($pTmpLog)) {
            $tmpLog = fread($pTmpLog, 4096);
            if ($pLog) {
                fwrite($pLog, $tmpLog);
            } else {
                fwrite($this->pErrorLogFile, $tmpLog);
            }
            $this->logByteSize += strlen($tmpLog);
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
            $this->logByteSize += strlen($log);
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
        if (!isset($pTmpLog)) {
            return;
        }
        rewind($pTmpLog2);
        while (!feof($pTmpLog2)) {
            fwrite($pTmpLog, fread($pTmpLog2, 4096));
        }
        $this->logPointerClosing($pTmpLog2);
    }

}

// @codeCoverageIgnoreStart
if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release.
    /**
     * Dummy class for release.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     * @codeCoverageIgnore
     */

    final class BreakpointDebugging_Error extends \BreakpointDebugging_Error_InAllCase
    {

    }

} else { // In case of not release.
    include_once __DIR__ . '/Error_Option.php';
}
// @codeCoverageIgnoreEnd

?>
