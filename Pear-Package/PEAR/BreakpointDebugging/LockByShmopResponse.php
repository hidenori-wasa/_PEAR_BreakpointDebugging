<?php

/**
 * This page is other process for response of "BreakpointDebugging_LockByShmopRequest" class.
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
use \BreakpointDebugging_Shmop as BS;
use \BreakpointDebugging_LockByShmopRequest as BLSR;

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
final class BreakpointDebugging_LockByShmopResponse
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
    static function acceptRequest()
    {
        self::_assert(self::$_onceFlag);
        self::$_onceFlag = false;

        // Resets maximum execution time.
        set_time_limit(self::$_timeOutSeconds);
        // Calculates shared memory data locations.
        $uniqueIdSize = &BLSR::refUniqueIdSize();
        $uniqueIdRequestLocation = 1;
        $writtenRequestLocation = $uniqueIdRequestLocation + $uniqueIdSize * 2;
        $writingResponseLocation = $writtenRequestLocation + 1;
        $writtenResponseLocation = $writingResponseLocation + $uniqueIdSize;
        $lockingLocation = $writtenResponseLocation + 1;
        // Gets shared file path.
        self::$_sharedFilePath = B::getStatic('$_workDir') . '/LockByShmopRequest.txt';
        $sharedFilePath = self::$_sharedFilePath;

        while (true) {
//            // 1 second sleep.
//            usleep(1000000);
//            // Waits until file creation.
//            while (!is_file($sharedFilePath)) {
//                continue;
//            }
//            // Gets shared memory key which main process created.
//            $shmopKey = file_get_contents($sharedFilePath);
//            if ($shmopKey === false) {
//                continue;
//            }
//            set_error_handler('\BreakpointDebugging::handleError', 0);
//            // Opens shared memory which main process created.
//            $shmopId = @shmop_open($shmopKey, 'w', 0, 0);
//            self::$_resourceID = $shmopId;
//            restore_error_handler();
//            if (empty($shmopId)) {
//                continue;
//            }
            // The file header is opened reading and writing mode.
            $pFile = B::fopen(array ($sharedFilePath, 'r+b'));
            $shmopId = BS::getSharedMemoryID($pFile);
            while (true) {
                // 0.1 second sleep.
                usleep(100000);
                // If request has not been written.
                $isWrittenRequest = shmop_read($shmopId, $writtenRequestLocation, 1);
                self::_assert($isWrittenRequest !== false);
                if ($isWrittenRequest !== '1') {
                    continue;
                }
                // If it is not correct request.
                $isWrittenRequest1 = shmop_read($shmopId, $uniqueIdRequestLocation, $uniqueIdSize);
                self::_assert($isWrittenRequest1 !== false);
                $isWrittenRequest2 = shmop_read($shmopId, $uniqueIdRequestLocation + $uniqueIdSize, $uniqueIdSize);
                self::_assert($isWrittenRequest2 !== false);
                if ($isWrittenRequest1 !== $isWrittenRequest2) {
                    continue;
                }
                // Writes response because it may be correct request.
                $result = shmop_write($shmopId, $isWrittenRequest1 . '1', $writingResponseLocation);
                self::_assert($result !== false);
                // Waits requested process until accept response for timeout.
                while (true) {
                    // If request process accepted response.
                    $isLocking = shmop_read($shmopId, $lockingLocation, 1);
                    self::_assert($isLocking !== false);
                    if ($isLocking === '1') {
                        break;
                    }
                    // 0.1 second sleep.
                    usleep(100000);
                    // If it has been unlocked.
                    $isWrittenResponse = shmop_read($shmopId, $writtenResponseLocation, 1);
                    self::_assert($isWrittenResponse !== false);
                    if ($isWrittenResponse !== '1') {
                        continue 2;
                    }
                }
                // Resets maximum execution time.
                set_time_limit(self::$_timeOutSeconds);
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
        // Deletes shared memory.
        $result = shmop_delete(self::$_resourceID);
        self::_assert($result !== false);
        // If shared file exists.
        if (is_file(self::$_sharedFilePath)) {
            // Unlinks shared file.
            B::unlink(array (self::$_sharedFilePath));
        }
    }

}

// Pushes the shutdown class method.
register_shutdown_function('\BreakpointDebugging_LockByShmopResponse::shutdown');

\BreakpointDebugging_LockByShmopResponse::acceptRequest();

// For debug.
// "C:/Program Files/Mozilla Firefox/firefox.exe" "https://localhost/Pear-Package/index.php?BREAKPOINTDEBUGGING_MODE=DEBUG"
