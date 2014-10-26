<?php

/**
 * Does error or exception handling.
 *
 * There is this file to increase speed when does not do error or exception handling.
 * In other words, this file does not cause "__autoload()" because does not read except for error or exception handling.
 *
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2013-2014, Hidenori Wasa
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
 * Does error or exception handling.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_Error extends \BreakpointDebugging_ErrorInAllCase
{
    /**
     * Makes HTML tags.
     */
    function __construct()
    {
        B::limitAccess('BreakpointDebugging.php');
        B::assert(func_num_args() === 0);

        if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release mode.
            parent::__construct();
        } else { // In case of debug mode.
            $this->maxLogFileByteSize = B::getStatic('$_maxLogFileByteSize');
            $this->isLogging = false;
            $this->mark = '&diams;';
            // When "Xdebug" exists.
            if (B::getXebugExists()) {
                $this->tags['pre'] = '<pre class=\'xdebug-var-dump\' dir=\'ltr\'>';
                $this->tags['font']['caution'] = '<span style="color:#ff8080;">';
                $this->tags['font']['bool'] = '<span style="color:#ff80ff;">';
                $this->tags['font']['int'] = '<span style="color:lime;">';
                $this->tags['font']['float'] = '<span style="color:#ffc000;">';
                $this->tags['font']['string'] = '<span style="color:yellow;">';
                $this->tags['font']['null'] = '<span style="color:#8080ff;">';
                $this->tags['font']['resource'] = '<span style="color:#c0c0ff;">';
                $this->tags['font']['=>'] = '<span style="color:silver;">';
                $this->tags['/font'] = '</span>';
                $this->tags['small'] = '<span style="font-size:small;">';
                $this->tags['/small'] = '</span>';
            } else { // When "Xdebug" does not exist.
                $this->tags['pre'] = '<pre>';
                $this->setHTMLTags($this->tags);
            }
            $this->tags['/pre'] = '</pre>';
            $this->tags['i'] = '<span style="font-style:italic;">';
            $this->tags['/i'] = '</span>';
            $this->tags['b'] = '<span style="font-weight:bold;">';
            $this->tags['/b'] = '</span>';
            if (B::getStatic('$exeMode') & B::UNIT_TEST) {
                $this->tags['uint test anchor href'] = $this->uintTestAnchorHref;
                $this->tags['uint test anchor name'] = $this->uintTestAnchorName;
            } else {
                $this->tags['uint test anchor href'] = '';
                $this->tags['uint test anchor name'] = '';
            }
        }
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
        B::assert(func_num_args() === 2);
        B::assert($pException instanceof \Exception);
        B::assert(is_string($prependLog));

        if (!(B::getStatic('$exeMode') & B::RELEASE)) { // In case of debug mode.
            // Forces unlocking to avoid lock-count assertion error if forces a exit.
            \BreakpointDebugging_Lock::forceUnlocking();
        }
        parent::handleException2($pException, $prependLog);
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
        if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release mode.
            parent::changeLogFile($pTmpLog);
        } else { // In case of debug mode.
            $continuingMark = PHP_EOL . str_repeat("\t", 1) . '.';
            $continuingMark = PHP_EOL . '### Omits since then because it exceeded logfile maximum capacity. ###' . $continuingMark . $continuingMark . $continuingMark;
            $this->logBufferWriting($pTmpLog, $continuingMark);
            $this->logWriting($pTmpLog);
            // This exception is caught inside handler.
            throw new \BreakpointDebugging_OutOfLogRangeException('', 101);
        }
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
        $exemode = B::getStatic('$exeMode');
        // In case of remote mode or release mode.
        if (($exemode & B::REMOTE) //
            || ($exemode & B::RELEASE) //
        ) {
            parent::checkLogByteSize($pTmpLog);
        } else { // In case of local debug mode.
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
     * Open error-log-pointer.
     *
     * @return mixed Error log pointer.
     */
    protected function logPointerOpening()
    {
        B::assert(func_num_args() === 0);

        $exemode = B::getStatic('$exeMode');
        // In case of remote mode or release mode.
        if (($exemode & B::REMOTE) //
            || ($exemode & B::RELEASE) //
        ) {
            return tmpfile();
        } else { // In case of local debug mode.
            return array ();
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
        B::assert(func_num_args() === 1);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog) || $pTmpLog === null);

        $exemode = B::getStatic('$exeMode');
        // In case of remote mode or release mode.
        if (($exemode & B::REMOTE) //
            || ($exemode & B::RELEASE) //
        ) {
            fclose($pTmpLog);
        }
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
        B::assert(func_num_args() <= 2);
        B::assert(is_array($pTmpLog) || is_resource($pTmpLog));
        B::assert(is_resource($pLog) || $pLog === false);

        if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release mode.
            parent::logWriting($pTmpLog, $pLog);
        } else { // In case of debug mode.
            $tmpLog = '';
            if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote mode.
                rewind($pTmpLog);
                while (!feof($pTmpLog)) {
                    $tmpLog .= fread($pTmpLog, 4096);
                    $this->logByteSize += strlen($tmpLog);
                }
                // Deletes temporary file.
                fclose($pTmpLog);
            } else { // In case of local mode.
                foreach ($pTmpLog as $log) {
                    $tmpLog .= $log;
                    $this->logByteSize += strlen($log);
                }
            }
            echo $tmpLog;
            $pTmpLog = null;
        }
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
        B::assert(func_num_args() === 2);
        B::assert(is_array($pLogBuffer) || is_resource($pLogBuffer) || $pLogBuffer === null);
        B::assert(is_string($log));

        if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release mode.
            parent::logBufferWriting($pLogBuffer, $log);
        } else { // In case of debug mode.
            if ($pLogBuffer === null) {
                echo $log;
                $this->logByteSize += strlen($log);
            } else {
                if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote mode.
                    fwrite($pLogBuffer, $log);
                } else { // In case of local.
                    $pLogBuffer[] = $log;
                }
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
        $exemode = B::getStatic('$exeMode');
        // In case of remote mode or release mode.
        if (($exemode & B::REMOTE) //
            || ($exemode & B::RELEASE) //
        ) {
            B::assert(is_resource($pTmpLog));
            B::assert(is_resource($pTmpLog2));
            rewind($pTmpLog2);
            while (!feof($pTmpLog2)) {
                fwrite($pTmpLog, fread($pTmpLog2, 4096));
            }
        } else { // In case of local debug mode.
            B::assert(is_array($pTmpLog));
            B::assert(is_array($pTmpLog2));
            if (count($pTmpLog2) !== 0) {
                $pTmpLog = array_merge($pTmpLog, $pTmpLog2);
            }
        }
        $this->logPointerClosing($pTmpLog2);
    }

}
