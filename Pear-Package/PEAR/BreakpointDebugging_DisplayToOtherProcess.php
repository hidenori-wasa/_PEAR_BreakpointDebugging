<?php

/**
 * This page is other process to execute JavaScript because session unit test must not send header.
 *
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2014, Hidenori Wasa
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
$_GET['BREAKPOINTDEBUGGING_MODE'] = "DEBUG";
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;

/**
 * Gets JavaScript character string from other process, and executes it.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_DisplayToOtherProcess
{
    /**
     * @var int Time out seconds.
     */
    private static $_timeOutSeconds = 300;

    /**
     * @var stirng Shared file path.
     */
    private static $_sharedFilePath = '';

    /**
     * @var int JavaScript reading pointer in shared memory.
     */
    private static $_javaScriptReadingPtr = '0x00000017';

    /**
     * @var int Shared memory ID or file pointer.
     */
    private static $_resourceID;

    /**
     * @var bool Flag of once.
     */
    private static $_onceFlag = true;

    /**
     * Displays error information if assertion is false.
     *
     * @param bool $assertion Assertion.
     *
     * @return void
     */
    private static function _assert($assertion)
    {
        $callStackInfo = debug_backtrace();
        reset($callStackInfo);
        if (!empty($callStackInfo)) {
            $call = each($callStackInfo);
            $call = $call['value'];
            if (array_key_exists('file', $call)) {
                $errorFile = $call['file'];
            }
            if (array_key_exists('line', $call)) {
                $errorLine = $call['line'];
            }
        }
        if (!is_bool($assertion)) {
            $errorInfo = <<<EOD
<pre>
<strong>Assertion must be bool.</strong>
FILE: $errorFile
LINE: $errorLine
</pre>
EOD;
            exit($errorInfo);
        }

        if ($assertion === false) {
            $errorInfo = <<<EOD
<pre>
<strong>Assertion failed.</strong>
FILE: $errorFile
LINE: $errorLine
</pre>
EOD;
            exit($errorInfo);
        }
    }

    /**
     * Gets JavaScript character string from other process, and executes it.
     *
     * @return void
     */
    static function displayToOtherProcess()
    {
        self::_assert(self::$_onceFlag);
        self::$_onceFlag = false;

        // Resets maximum execution time.
        set_time_limit(self::$_timeOutSeconds);
        // Displays the caution.
        echo 'This window exists for "JavaScript character string receipt from main process" and "execution".<br />'
        . '<hr />'
        . '<strong>Do not stop or delete this window</strong> because this window must exist except timeout\'s ' . self::$_timeOutSeconds . ' seconds from data receipt.<br />'
        . 'You must wait until timeout if you stopped or deleted.<br />';
        // Gets shared file path.
        self::$_sharedFilePath = B::getStatic('$_workDir') . '/SharedFileForOtherProcessDisplay.txt';
        $sharedFilePath = self::$_sharedFilePath;
        // Defines JavaScript functions of "DOM.js" file.
        BW::executeJavaScript(file_get_contents('BreakpointDebugging/js/DOM.js', true));
        // If "shmop" extention is valid.
        if (extension_loaded('shmop')) {
            while (true) {
                // 1 second sleep.
                usleep(1000000);
                // Waits until file creation.
                while (!is_file($sharedFilePath)) {
                    // 1 second sleep.
                    usleep(1000000);
                }
                // Gets shared memory key which main process created.
                $shmopKey = file_get_contents($sharedFilePath);
                if ($shmopKey === false) {
                    continue;
                }
                set_error_handler('\BreakpointDebugging::handleError', 0);
                // Opens shared memory which main process created.
                $shmopId = @shmop_open($shmopKey, 'w', 0, 0);
                self::$_resourceID = $shmopId;
                restore_error_handler();
                if (empty($shmopId)) {
                    continue;
                }
                while (true) {
                    // Locks the shared memory.
                    if (BW::lockOn2Processes(1, 0, $shmopId) === false) {
                        continue 2;
                    }
                    // Gets writing pointer of shared resource.
                    $javaScriptWritingPtr = shmop_read($shmopId, 3, 10);
                    if ($javaScriptWritingPtr === false) {
                        continue 2;
                    }
                    $javaScript = '';
                    // If area to write overrun shared memory area.
                    if (self::$_javaScriptReadingPtr > $javaScriptWritingPtr) {
                        // Calculates read-length.
                        $readLen = BW::SHARED_MEMORY_SIZE - self::$_javaScriptReadingPtr;
                        // Gets dispatched JavaScript character string from main process until end of shared memory area.
                        $result = shmop_read($shmopId, self::$_javaScriptReadingPtr + 0, $readLen);
                        if ($result === false) {
                            continue 2;
                        }
                        $javaScript .= $result;
                        // Initializes the shared memory reading pointer.
                        self::$_javaScriptReadingPtr = 23;
                    }
                    // Calculates read-length.
                    $readLen = $javaScriptWritingPtr - self::$_javaScriptReadingPtr;
                    self::_assert($readLen <= BW::SHARED_MEMORY_SIZE);
                    // If this process received data from main process.
                    if ($readLen > 0) {
                        // Resets maximum execution time.
                        set_time_limit(self::$_timeOutSeconds);
                        // Gets dispatched JavaScript character string from main process.
                        //$javaScript = shmop_read($shmopId, self::$_javaScriptReadingPtr + 0, $readLen);
                        $result = shmop_read($shmopId, self::$_javaScriptReadingPtr + 0, $readLen);
                        //if ($javaScript === false) {
                        if ($result === false) {
                            continue 2;
                        }
                        $javaScript .= $result;
                        // Moves the shared memory reading pointer.
                        self::$_javaScriptReadingPtr = $javaScriptWritingPtr;
                        // Registers the shared memory reading pointer.
                        $result = shmop_write($shmopId, self::$_javaScriptReadingPtr, 13);
                        if ($result === false) {
                            continue 2;
                        }
                        // Executes JavaScript.
                        echo $javaScript;
                        flush();
                    }
                    // Unlocks the shared memory.
                    if (BW::unlockOn2Processes(1, $shmopId) === false) {
                        continue 2;
                    }
                    // 1 second sleep.
                    usleep(1000000);
                }
            }
        } else { // If "shmop" extension is invalid.
            while (true) {
                // 1 second sleep.
                usleep(1000000);
                // Waits until file creation.
                while (!is_file($sharedFilePath)) {
                    // 1 second sleep.
                    usleep(1000000);
                }
                // Opens the file with read only.
                $pFile = B::fopen(array ($sharedFilePath, 'r+b'));
                if ($pFile === false) {
                    continue;
                }
                self::$_resourceID = $pFile;
                while (true) {
                    // 1 second sleep.
                    usleep(1000000);
                    // If file does not exist.
                    if (!is_file($sharedFilePath)) {
                        continue 2;
                    }
                    // Gets dispatched JavaScript character string from main process.
                    $javaScript = '';
                    do {
                        $readData = fread($pFile, 4096);
                        self::_assert($readData !== false);
                        $javaScript .= $readData;
                    } while ($readData !== '');
                    // If this process got dispatched JavaScript character string from main process.
                    if ($javaScript !== '') {
                        // Resets maximum execution time.
                        set_time_limit(self::$_timeOutSeconds);
                        // Executes JavaScript.
                        echo $javaScript;
                        flush();
                        if ($javaScript === ' ') {
                            // For health check.
                            $result = fwrite($pFile, ' ');
                            self::_assert($result !== false);
                            $result = fflush($pFile);
                            self::_assert($result !== false);
                        }
                    }
                }
            }
        }
    }

    /**
     * Unlinks shared file at shutdown.
     *
     * @return void
     */
    static function shutdown()
    {
        // If "shmop" extention is valid.
        if (extension_loaded('shmop')) {
            // Deletes shared memory.
            $result = shmop_delete(self::$_resourceID);
            self::_assert($result !== false);
            // If shared file exists.
            if (is_file(self::$_sharedFilePath)) {
                // Unlinks shared file.
                B::unlink(array (self::$_sharedFilePath));
            }
        } else { // If "shmop" extention is invalid.
            // Truncates the shared file to size 1.
            $result = ftruncate(self::$_resourceID, 1);
            self::_assert($result !== false);
            // Closes the shared file.
            $result = fclose(self::$_resourceID);
            self::_assert($result !== false);
        }
    }

}

// Pushes the shutdown class method.
register_shutdown_function('\BreakpointDebugging_DisplayToOtherProcess::shutdown');

\BreakpointDebugging_DisplayToOtherProcess::displayToOtherProcess();

// For debug.
// "C:/Program Files/Mozilla Firefox/firefox.exe" "https://localhost/Pear-Package/index.php?BREAKPOINTDEBUGGING_MODE=DEBUG"
