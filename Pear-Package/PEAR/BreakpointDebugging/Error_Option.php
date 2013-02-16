<?php

/**
 * Debugs "BreakpointDebugging_Error_InAllCase" class.
 *
 * "*_Option.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2013, Hidenori Wasa
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
 * Debugs "BreakpointDebugging_Error_InAllCase" class.
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
    /**
     * For debug.
     *
     * @param array &$tags Same as parent.
     *
     * @return void
     */
    protected function setHTMLTags(&$tags)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert(is_array($tags), 2);

        parent::setHTMLTags($tags);
    }

    /**
     * Makes HTML tags.
     *
     * @return void
     */
    function __construct()
    {
        B::limitAccess('BreakpointDebugging.php');
        B::assert(func_num_args() === 0);

        if (B::getStatic('$exeMode') & (B::RELEASE | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of the logging.
            parent::__construct();
        } else { // In case of not the logging.
            $this->_loggedCallStacks = array ();
            $this->_loggedArrays = array ();
            $this->_loggedObjects = array ();
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
     * For debug.
     *
     * @param string $string Same as parent.
     *
     * @return Same as parent.
     */
    protected function convertMbString($string)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert(is_string($string), 2);

        return parent::convertMbString($string);
    }

    /**
     * For debug.
     *
     * @param mixed  &$pTmpLog2  Same as parent.
     * @param mixed  &$pTmpLog   Same as parent.
     * @param bool   &$onceFlag2 Same as parent.
     * @param string $func       Same as parent.
     * @param string $class      Same as parent.
     * @param mixed  $line       Same as parent.
     * @param string $tabs       Same as parent.
     *
     * @return Same as parent.
     */
    protected function addFunctionValuesToLog(&$pTmpLog2, &$pTmpLog, &$onceFlag2, $func, $class, $line, $tabs = '')
    {
        $paramNumber = func_num_args();
        B::assert($paramNumber <= 7, 1);
        B::assert(is_array($pTmpLog2) || is_resource($pTmpLog2) || $pTmpLog2 === null, 2);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null, 3);
        B::assert(is_bool($onceFlag2), 4);
        B::assert(is_string($func), 5);
        B::assert(is_string($class), 6);
        B::assert(is_string($line) || is_int($line), 7);
        B::assert(is_string($tabs), 8);

        parent::addFunctionValuesToLog($pTmpLog2, $pTmpLog, $onceFlag2, $func, $class, $line, $tabs);
    }

    /**
     * For debug.
     *
     * @param mixed &$pTmpLog  Same as parent.
     * @param mixed $paramName Same as parent.
     * @param array $array     Same as parent.
     * @param int   $tabNumber Same as parent.
     *
     * @return Same as parent.
     */
    protected function reflectArray(&$pTmpLog, $paramName, $array, $tabNumber = 1)
    {
        B::assert(func_num_args() <= 4, 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog), 2);
        B::assert(is_string($paramName) || is_int($paramName), 3);
        B::assert(is_array($array), 4);
        B::assert(is_int($tabNumber), 5);

        parent::reflectArray($pTmpLog, $paramName, $array, $tabNumber);
    }

    /**
     * For debug.
     *
     * @param mixed  &$pTmpLog  Same as parent.
     * @param mixed  $paramName Same as parent.
     * @param object $object    Same as parent.
     * @param int    $tabNumber Same as parent.
     *
     * @return Same as parent.
     */
    protected function reflectObject(&$pTmpLog, $paramName, $object, $tabNumber = 1)
    {
        $className = get_class($object);

        B::assert(func_num_args() <= 4, 1);
        B::assert(is_string($paramName) || is_int($paramName), 2);
        B::assert(is_object($object), 3);
        B::assert(is_string($className), 4);
        B::assert(is_int($tabNumber), 5);

        parent::reflectObject($pTmpLog, $paramName, $object, $tabNumber);
    }

    /**
     * For debug.
     *
     * @param object $pException Same as parent.
     * @param string $prependLog Same as parent.
     *
     * @return Same as parent.
     */
    function exceptionHandler2($pException, $prependLog)
    {
        B::assert(func_num_args() === 2, 1);
        B::assert($pException instanceof \Exception, 2);
        B::assert(is_string($prependLog), 3);

        // Forces unlocking to avoid lock-count assertion error if forces a exit.
        \BreakpointDebugging_Lock::forceUnlocking();
        parent::exceptionHandler2($pException, $prependLog);
    }

    /**
     * For debug.
     *
     * @param int    $errorNumber  Same as parent.
     * @param string $errorMessage Same as parent.
     * @param string $prependLog   Same as parent.
     * @param array  $callStack    Same as parent.
     *
     * @return Same as parent.
     */
    function errorHandler2($errorNumber, $errorMessage, $prependLog, $callStack)
    {
        B::assert(func_num_args() === 4, 1);
        B::assert(is_int($errorNumber), 2);
        B::assert(is_string($errorMessage), 3);
        B::assert(is_string($prependLog), 4);
        B::assert(is_array($callStack), 5);

        parent::errorHandler2($errorNumber, $errorMessage, $prependLog, $callStack);
    }

    /**
     * For debug.
     *
     * @param mixed  &$pTmpLog Same as parent.
     * @param string $file     Same as parent.
     * @param mixed  $line     Same as parent.
     * @param string $func     Same as parent.
     * @param string $class    Same as parent.
     *
     * @return Same as parent.
     */
    protected function addParameterHeaderToLog(&$pTmpLog, $file, $line, $func, $class)
    {
        B::assert(func_num_args() === 5, 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null, 2);
        B::assert(is_string($file), 3);
        B::assert(is_string($line) || is_int($line), 4);
        B::assert(is_string($func), 5);
        B::assert(is_string($class), 6);

        parent::addParameterHeaderToLog($pTmpLog, $file, $line, $func, $class);
    }

    /**
     * For debug.
     *
     * @param array  $backTrace  Same as parent.
     * @param mixed  &$pTmpLog   Same as parent.
     * @param bool   &$onceFlag2 Same as parent.
     * @param mixed  &$func      Same as parent.
     * @param mixed  &$class     Same as parent.
     * @param string $file       Same as parent.
     * @param string $line       Same as parent.
     * @param string $tabs       Same as parent.
     *
     * @return Same as parent.
     */
    protected function outputFixedFunctionToLogging($backTrace, &$pTmpLog, &$onceFlag2, &$func, &$class, $file, $line, $tabs = '')
    {
        B::assert(func_num_args() <= 8, 1);
        B::assert(is_array($backTrace), 2);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null, 3);
        B::assert(is_bool($onceFlag2), 4);
        B::assert(is_null($func) || is_string($func), 5);
        B::assert(is_null($class) || is_string($class), 6);
        B::assert(is_string($file), 7);
        B::assert(is_string($line) || is_int($line), 8);
        B::assert(is_string($tabs), 9);

        parent::outputFixedFunctionToLogging($backTrace, $pTmpLog, $onceFlag2, $func, $class, $file, $line, $tabs);
    }

    /**
     * For debug.
     *
     * @param string $errorKind    Same as parent.
     * @param string $errorMessage Same as parent.
     * @param string $prependLog   Same as parent.
     *
     * @return Same as parent.
     */
    protected function outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog = '')
    {
        B::assert(func_num_args() <= 3, 1);
        B::assert(is_string($errorKind), 2);
        B::assert(is_string($errorMessage), 3);
        B::assert(is_string($prependLog), 4);

        parent::outputErrorCallStackLog2($errorKind, $errorMessage, $prependLog);
    }

    /**
     * For debug.
     *
     * @param array  $tags       Same as parent.
     * @param string $type       Same as parent.
     * @param mixed  $paramValue Same as parent.
     *
     * @return Same as parent.
     */
    protected function getParamInfo($tags, $type, $paramValue)
    {
        B::assert(func_num_args() === 3, 1);
        B::assert(is_array($tags), 2);
        B::assert(is_string($type), 3);

        return parent::getParamInfo($tags, $type, $paramValue);
    }

    /**
     * For debug.
     *
     * @param mixed &$pTmpLog   Same as parent.
     * @param mixed $paramName  Same as parent.
     * @param mixed $paramValue Same as parent.
     * @param int   $tabNumber  Same as parent.
     *
     * @return Same as parent.
     */
    protected function getTypeAndValue(&$pTmpLog, $paramName, $paramValue, $tabNumber)
    {
        B::assert(func_num_args() === 4, 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null, 2);
        B::assert(is_string($paramName) || is_int($paramName), 3);
        B::assert(is_int($tabNumber), 4);

        parent::getTypeAndValue($pTmpLog, $paramName, $paramValue, $tabNumber);
    }

    /**
     * For debug.
     *
     * @param mixed &$pTmpLog        Same as parent.
     * @param array $backtraceParams Same as parent.
     * @param int   $tabNumber       Same as parent.
     *
     * @return Same as parent.
     */
    protected function searchDebugBacktraceArgsToString(&$pTmpLog, $backtraceParams, $tabNumber = 1)
    {
        B::assert(func_num_args() <= 3, 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null, 2);
        B::assert(is_array($backtraceParams), 3);
        B::assert(is_int($tabNumber), 4);

        parent::searchDebugBacktraceArgsToString($pTmpLog, $backtraceParams, $tabNumber);
    }

    /**
     * For debug.
     *
     * @return Same as parent.
     */
    protected function logPointerOpening()
    {
        B::assert(func_num_args() === 0, 1);

        if (B::getStatic('$exeMode') & (B::LOCAL_DEBUG | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of local host.
            return array ();
        } else { // In case of remote host.
            return parent::logPointerOpening();
        }
    }

    /**
     * For debug.
     *
     * @param mixed &$pTmpLog Same as parent.
     *
     * @return Same as parent.
     */
    protected function logPointerClosing(&$pTmpLog)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null, 2);

        if (B::getStatic('$exeMode') & B::REMOTE_DEBUG) {
            fclose($pTmpLog);
            $pTmpLog = null;
        } else if (B::getStatic('$exeMode') & B::RELEASE) {
            parent::logPointerClosing($pTmpLog);
        } else {
            $pTmpLog = null;
        }
    }

    /**
     * For debug.
     *
     * @param mixed &$pTmpLog Same as parent.
     *
     * @return Same as parent.
     */
    protected function logWriting(&$pTmpLog)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog), 2);

        switch (B::getStatic('$exeMode') & ~(B::UNIT_TEST | B::IGNORING_BREAK_POINT)) {
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
                    fwrite($this->pErrorLogFile, $log);
                }
                break;
            case B::RELEASE: // For unit test.
                parent::logWriting($pTmpLog);
                break;
            default:
                B::internalException('', 3);
        }
        $pTmpLog = null;
    }

    /**
     * For debug.
     *
     * @param mixed  &$pLogBuffer Same as parent.
     * @param string $log         Same as parent.
     *
     * @return Same as parent.
     */
    protected function logBufferWriting(&$pLogBuffer, $log)
    {
        B::assert(func_num_args() === 2, 1);
        B::assert(is_array($pLogBuffer) || is_resource($pLogBuffer) || $pLogBuffer === null, 2);
        B::assert(is_string($log), 3);

        switch (B::getStatic('$exeMode') & ~(B::UNIT_TEST | B::IGNORING_BREAK_POINT)) {
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
                    fwrite($this->pErrorLogFile, $log);
                } else {
                    $pLogBuffer[] = $log;
                }
                break;
            case B::RELEASE: // For unit test.
                parent::logBufferWriting($pLogBuffer, $log);
                break;
            default:
                B::internalException('', 4);
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
        B::assert(func_num_args() === 2, 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog), 2);
        B::assert(is_array($pTmpLog2) || is_resource($pTmpLog2), 3);
        B::assert($pTmpLog2 !== null, 4);

        switch (B::getStatic('$exeMode') & ~(B::UNIT_TEST | B::IGNORING_BREAK_POINT)) {
            case B::LOCAL_DEBUG:
                if (count($pTmpLog) === 0) {
                    if (count($pTmpLog2) !== 0) {
                        $pTmpLog = $pTmpLog2;
                    }
                } else if (count($pTmpLog2) !== 0) {
                    $pTmpLog = array_merge($pTmpLog, $pTmpLog2);
                }
                break;
            case B::REMOTE_DEBUG:
                rewind($pTmpLog2);
                while (!feof($pTmpLog2)) {
                    fwrite($pTmpLog, fread($pTmpLog2, 4096));
                }
                break;
            case B::LOCAL_DEBUG_OF_RELEASE:
                if (count($pTmpLog) === 0) {
                    if (count($pTmpLog2) !== 0) {
                        $pTmpLog = $pTmpLog2;
                    }
                } else if (count($pTmpLog2) !== 0) {
                    $pTmpLog = array_merge($pTmpLog, $pTmpLog2);
                }
                break;
            case B::RELEASE: // For unit test.
                parent::logCombination($pTmpLog, $pTmpLog2);
                break;
            default:
                B::internalException('', 5);
        }
        $this->logPointerClosing($pTmpLog2);
    }

}

?>
