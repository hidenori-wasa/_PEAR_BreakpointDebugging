<?php

/**
 * Debugs "BreakpointDebugging_Error_InAllCase" class.
 *
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * PHP version 5.3.x, 5.4.x
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
        B::assert(func_num_args() === 1);
        B::assert(is_array($tags));

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

        $this->maxLogFileByteSize = B::getStatic('$_maxLogFileByteSize');
        $this->isLogging = false;
        $this->mark = '&diams;';
        // When "Xdebug" exists.
        if (B::getXebugExists()) {
            $this->tags['pre'] = '<pre class=\'xdebug-var-dump\' dir=\'ltr\'>';
            //$this->tags['font']['caution'] = '<font color=\'#ff8080\'>';
            $this->tags['font']['caution'] = '<span style="color:#ff8080;">';
            //$this->tags['font']['bool'] = '<font color=\'#ff80ff\'>';
            $this->tags['font']['bool'] = '<span style="color:#ff80ff;">';
            //$this->tags['font']['int'] = '<font color=\'lime\'>';
            $this->tags['font']['int'] = '<span style="color:lime;">';
            //$this->tags['font']['float'] = '<font color=\'#ffc000\'>';
            $this->tags['font']['float'] = '<span style="color:#ffc000;">';
            //$this->tags['font']['string'] = '<font color=\'yellow\'>';
            $this->tags['font']['string'] = '<span style="color:yellow;">';
            //$this->tags['font']['null'] = '<font color=\'#8080ff\'>';
            $this->tags['font']['null'] = '<span style="color:#8080ff;">';
            //$this->tags['font']['resource'] = '<font color=\'#c0c0ff\'>';
            $this->tags['font']['resource'] = '<span style="color:#c0c0ff;">';
            //$this->tags['font']['=>'] = '<font color=\'silver\'>';
            $this->tags['font']['=>'] = '<span style="color:silver;">';
            //$this->tags['/font'] = '</font>';
            $this->tags['/font'] = '</span>';
            //$this->tags['small'] = '<small>';
            $this->tags['small'] = '<span style="font-size:small;">';
            //$this->tags['/small'] = '</small>';
            $this->tags['/small'] = '</span>';
        } else { // When "Xdebug" does not exist.
            $this->tags['pre'] = '<pre>';
            $this->setHTMLTags($this->tags);
        }
        $this->tags['/pre'] = '</pre>';
        //$this->tags['i'] = '<i>';
        $this->tags['i'] = '<span style="font-style:italic;">';
        //$this->tags['/i'] = '</i>';
        $this->tags['/i'] = '</span>';
        //$this->tags['b'] = '<b>';
        $this->tags['b'] = '<span style="font-weight:bold;">';
        //$this->tags['/b'] = '</b>';
        $this->tags['/b'] = '</span>';
        if (B::getStatic('$exeMode') & B::UNIT_TEST) {
            $this->tags['uint test anchor href'] = $this->uintTestAnchorHref;
            $this->tags['uint test anchor name'] = $this->uintTestAnchorName;
        } else {
            $this->tags['uint test anchor href'] = '';
            $this->tags['uint test anchor name'] = '';
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
        B::assert(func_num_args() === 1);
        B::assert(is_string($string));

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
        B::assert($paramNumber <= 7);
        B::assert(is_array($pTmpLog2) || is_resource($pTmpLog2) || $pTmpLog2 === null);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null);
        B::assert(is_bool($onceFlag2));
        B::assert(is_string($func));
        B::assert(is_string($class));
        B::assert(is_string($line) || is_int($line));
        B::assert(is_string($tabs));

        parent::addFunctionValuesToLog($pTmpLog2, $pTmpLog, $onceFlag2, $func, $class, $line, $tabs);
    }

    /**
     * For debug.
     *
     * @param object $pException Same as parent.
     * @param string $prependLog Same as parent.
     *
     * @return Same as parent.
     */
    function handleException2($pException, $prependLog)
    {
        B::assert(func_num_args() === 2);
        B::assert($pException instanceof \Exception);
        B::assert(is_string($prependLog));

        // Forces unlocking to avoid lock-count assertion error if forces a exit.
        \BreakpointDebugging_Lock::forceUnlocking();
        parent::handleException2($pException, $prependLog);
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
    function handleError2($errorNumber, $errorMessage, $prependLog, $callStack)
    {
        B::assert(func_num_args() === 4);
        B::assert(is_int($errorNumber));
        B::assert(is_string($errorMessage));
        B::assert(is_string($prependLog));
        B::assert(is_array($callStack));

        parent::handleError2($errorNumber, $errorMessage, $prependLog, $callStack);
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
        B::assert(func_num_args() === 5);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null);
        B::assert(is_string($file));
        B::assert(is_string($line) || is_int($line));
        B::assert(is_string($func));
        B::assert(is_string($class));

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
        B::assert(func_num_args() <= 8);
        B::assert(is_array($backTrace) || is_string($backTrace));
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null);
        B::assert(is_bool($onceFlag2));
        B::assert(is_null($func) || is_string($func));
        B::assert(is_null($class) || is_string($class));
        B::assert(is_string($file));
        B::assert(is_string($line) || is_int($line));
        B::assert(is_string($tabs));

        parent::outputFixedFunctionToLogging($backTrace, $pTmpLog, $onceFlag2, $func, $class, $file, $line, $tabs);
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
        $continuingMark = PHP_EOL . str_repeat("\t", 1) . '.';
        $continuingMark = PHP_EOL . '### Omits since then because it exceeded logfile maximum capacity. ###' . $continuingMark . $continuingMark . $continuingMark;
        $this->logBufferWriting($pTmpLog, $continuingMark);
        $this->logWriting($pTmpLog);
        // This exception is caught inside handler.
        throw new \BreakpointDebugging_OutOfLogRangeException('', 101);
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
        if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
            parent::checkLogByteSize($pTmpLog);
        } else { // In case of local.
            $tmpLogSize = 0;
            foreach ($pTmpLog as $tmpLogLine) {
                $tmpLogSize += strlen($tmpLogLine);
            }
            if ($this->logByteSize + $tmpLogSize > $this->maxLogFileByteSize) {
                $this->changeLogFile($pTmpLog);
                // @codeCoverageIgnoreStart
                // Because "$this->changeLogFile()" class method throws exception.
            }
            // @codeCoverageIgnoreEnd
        }
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
        B::assert(func_num_args() <= 3);
        B::assert(is_string($errorKind));
        B::assert(is_string($errorMessage));
        B::assert(is_string($prependLog));

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
        B::assert(func_num_args() === 3);
        B::assert(is_array($tags));
        B::assert(is_string($type));

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
        B::assert(func_num_args() === 4);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null);
        B::assert(is_string($paramName) || is_int($paramName));
        B::assert(is_int($tabNumber));

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
        B::assert(func_num_args() <= 3);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null);
        B::assert(is_array($backtraceParams));
        B::assert(is_int($tabNumber));

        parent::searchDebugBacktraceArgsToString($pTmpLog, $backtraceParams, $tabNumber);
    }

    /**
     * For debug.
     *
     * @return Same as parent.
     */
    protected function logPointerOpening()
    {
        B::assert(func_num_args() === 0);

        if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
            return parent::logPointerOpening();
        } else { // In case of local.
            return array ();
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
        B::assert(func_num_args() === 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null);

        if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote debug.
            fclose($pTmpLog);
            $pTmpLog = null;
        } else {
            $pTmpLog = null;
        }
    }

    /**
     * For debug.
     *
     * @param mixed &$pTmpLog Same as parent.
     * @param mixed $pLog     Same as parent.
     *
     * @return Same as parent.
     */
    protected function logWriting(&$pTmpLog, $pLog = false)
    {
        B::assert(func_num_args() <= 2);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog));
        B::assert(is_resource($pLog) || $pLog === false);

        $tmpLog = '';
        if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
            rewind($pTmpLog);
            while (!feof($pTmpLog)) {
                $tmpLog .= fread($pTmpLog, 4096);
                $this->logByteSize += strlen($tmpLog);
            }
            // Deletes temporary file.
            fclose($pTmpLog);
        } else { // In case of local.
            foreach ($pTmpLog as $log) {
                $tmpLog .= $log;
                $this->logByteSize += strlen($log);
            }
        }
        echo $tmpLog;
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
        B::assert(func_num_args() === 2);
        B::assert(is_array($pLogBuffer) || is_resource($pLogBuffer) || $pLogBuffer === null);
        B::assert(is_string($log));

        if ($pLogBuffer === null) {
            echo $log;
            $this->logByteSize += strlen($log);
        } else {
            if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
                fwrite($pLogBuffer, $log);
            } else { // In case of local.
                $pLogBuffer[] = $log;
            }
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
        B::assert(func_num_args() === 2);

        if (!isset($pTmpLog)) {
            return;
        }
        if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
            B::assert(is_resource($pTmpLog));
            B::assert(is_resource($pTmpLog2));
            rewind($pTmpLog2);
            while (!feof($pTmpLog2)) {
                fwrite($pTmpLog, fread($pTmpLog2, 4096));
            }
        } else { // In case of local.
            B::assert(is_array($pTmpLog));
            B::assert(is_array($pTmpLog2));
            if (count($pTmpLog2) !== 0) {
                $pTmpLog = array_merge($pTmpLog, $pTmpLog2);
            }
        }
        $this->logPointerClosing($pTmpLog2);
    }

}

?>
