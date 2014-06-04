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
class BreakpointDebugging_DisplayToOtherProcess
{
    /**
     * @var int JavaScript reading pointer in shared memory.
     */
    private static $_javaScriptReadingPtr = '0x00000017';

    /**
     * @var bool Flag of once.
     */
    private static $_onceFlag = true;

    /**
     * Gets JavaScript character string from other process, and executes it.
     *
     * @return void
     */
    static function displayToOtherProcess()
    {
        B::assert(self::$_onceFlag);
        self::$_onceFlag = false;

        $shmopFullFilePath = B::getStatic('$_workDir') . '/OtherProcessDisplayByShmop.txt';
        if (extension_loaded('shmop')) { // If "shmop" extention is valid.
            // Defines JavaScript functions of "DOM.js" file.
            BW::executeJavaScript(file_get_contents('BreakpointDebugging/js/DOM.js', true));
            while (true) {
                // 1 second sleep.
                usleep(1000000);
                // Waits until file creation.
                while (!is_file($shmopFullFilePath)) {
                    // Extends maximum execution time.
                    set_time_limit(300);
                    // 1 second sleep.
                    usleep(1000000);
                }
                $shmopKey = file_get_contents($shmopFullFilePath);
                if ($shmopKey === false) {
                    continue;
                }
                set_error_handler('\BreakpointDebugging::handleError', 0);
                // Gets the shared memory reading pointer.
                $shmopId = @shmop_open($shmopKey, 'w', 0, 0);
                restore_error_handler();
                if (empty($shmopId)) {
                    continue;
                }
                while (true) {
                    // Extends maximum execution time.
                    set_time_limit(300);
                    // Locks the shared memory.
                    if (BW::lockOn2Processes(1, 0, $shmopId) === false) {
                        continue 2;
                    }
                    // Gets writing pointer of shared resource.
                    $javaScriptWritingPtr = shmop_read($shmopId, 3, 10);
                    if ($javaScriptWritingPtr === false) {
                        continue 2;
                    }
                    // Calculates read-length.
                    $readLen = $javaScriptWritingPtr - self::$_javaScriptReadingPtr;
                    if ($readLen > 0) {
                        // Gets dispatched JavaScript character string from other process
                        $javaScript = shmop_read($shmopId, self::$_javaScriptReadingPtr + 0, $readLen);
                        if ($javaScript === false) {
                            continue 2;
                        }
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
        } else { // If "shmop" extension has not been loaded.
            $javaScriptFullFilePath = B::getStatic('$_workDir') . '/OtherProcessDisplayByJavaScript.txt';
            while (true) {
                // Waits until file creation.
                while (!is_file($javaScriptFullFilePath)) {
                    // Extends maximum execution time.
                    set_time_limit(300);
                    // 1 second sleep.
                    usleep(1000000);
                }
                // Opens the file with read only.
                $pFile = B::fopen(array ($javaScriptFullFilePath, 'rb'));
                if ($pFile === false) {
                    continue;
                }
                $javaScript = '';
                while (true) {
                    // Extends maximum execution time.
                    set_time_limit(300);
                    $readData = fread($pFile, 4096);
                    if ($readData === false) {
                        continue 2;
                    }
                    $javaScript .= $readData;
                    if ($readData === '') {
                        // Executes JavaScript.
                        echo $javaScript;
                        flush();
                        $javaScript = '';
                    }
                    // 1 second sleep.
                    usleep(1000000);
                }
            }
        }
    }

}

\BreakpointDebugging_DisplayToOtherProcess::displayToOtherProcess();
