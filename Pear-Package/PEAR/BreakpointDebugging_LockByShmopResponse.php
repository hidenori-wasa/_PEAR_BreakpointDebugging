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
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Shmop as BS;

/**
 * This class is only one process for response of "BreakpointDebugging_LockByShmopRequest" class.
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
    private static $_timeOutSeconds = 300; // 86400; // 1 day.

    /**
     * @var stirng Shared memory key file path.
     */
    private static $_shmopKeyFilePath = '';

    /**
     * @var int Shared memory ID.
     */
    private static $_shmopID;

    /**
     * @var resource Shared memory key file pointer.
     */
    private static $_pFile;

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
     * Builds shared memory, then registers shared memory key into file.
     *
     * @param int $sharedMemorySize Shared memory byte size.
     *
     * @return int Shared memory ID.
     */
    private static function _buildSharedMemory($sharedMemorySize)
    {
        list($sharedMemoryKey, $shmopID) = BS::buildSharedMemory($sharedMemorySize);
        // Register shared memory key.
        $result = rewind(self::$_pFile);
        self::_assert($result !== false);
        $result = fwrite(self::$_pFile, sprintf('0x%08X', $sharedMemoryKey));
        self::_assert($result !== false);
        $result = fflush(self::$_pFile);
        self::_assert($result !== false);
        // Initialilze shared memory.
        $result = shmop_write($shmopID, str_repeat("\x20", $sharedMemorySize), 0);
        self::_assert($result !== false);
        return $shmopID;
    }

    /**
     * Accepts request of "\BreakpointDebugging_LockByShmopRequest" class.
     *
     * @return void
     */
    static function acceptRequest()
    {
        // Calculates shared memory data locations.
        $uniqueIdSize = strlen(uniqid('', true));
        $uniqueIdRequestLocation = 1;
        $writtenRequestLocation = $uniqueIdRequestLocation + $uniqueIdSize * 2;
        $uniqueIdResponseLocation = $writtenRequestLocation + 1;
        $writtenResponseLocation = $uniqueIdResponseLocation + $uniqueIdSize;
        $lockingLocation = $writtenResponseLocation + 1;
        // Gets shared file path.
        self::$_shmopKeyFilePath = B::getStatic('$_workDir') . '/LockByShmopRequest.txt';
        $shmopKeyFilePath = self::$_shmopKeyFilePath;
        while (true) {
            // Cleans up file status cache.
            clearstatcache();
            // Checks the key existing of shared memory key file.
            if (is_file($shmopKeyFilePath)) {
                if (filesize($shmopKeyFilePath) === 10) {
                    // Opens reading and writing mode because this file may be truncated.
                    self::$_pFile = B::fopen(array ($shmopKeyFilePath, 'r+b'));
                    $shmopId = BS::getSharedMemoryID(self::$_pFile);
                    // If success.
                    if ($shmopId) {
                        break;
                    }
                }
            }
            // Destroys file then opens as writing mode.
            self::$_pFile = B::fopen(array ($shmopKeyFilePath, 'w+b'));
            // Builds shared memory, then registers shared memory key into file.
            $shmopId = self::_buildSharedMemory($lockingLocation + 1);
            break;
        }
        self::$_shmopID = $shmopId;

        //`"C:/Program Files/Mozilla Firefox/firefox.exe" "https://localhost/Pear-Package/index.php?BREAKPOINTDEBUGGING_MODE=DEBUG"`; // For debug.
        // Resets maximum execution time.
        $startTime = time();
        while (true) {
            // If the timeout.
            if (time() - $startTime > self::$_timeOutSeconds) {
                exit;
            }
            // 0.1 second sleep.
            usleep(100000);

            // For debug ===>
            // // Waits until unlocking.
            // $isLocked = shmop_read($shmopId, $lockingLocation, 1);
            // self::_assert($isLocked !== false);
            // self::_assert($isLocked !== '1');
            // // If other process is writing.
            // $writingRequestLocation = 0;
            // $IsWritingRequest = shmop_read($shmopId, $writingRequestLocation, 1);
            // self::_assert($IsWritingRequest !== false);
            // self::_assert($IsWritingRequest !== '1');
            // // Writes locking request.
            // $uniqueID = uniqid('', true);
            // $result = shmop_write($shmopId, '1' . $uniqueID . $uniqueID . '1', 0);
            // self::_assert($result !== false);
            // <=== For debug
            //
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
            $result = shmop_write($shmopId, $isWrittenRequest1 . '1', $uniqueIdResponseLocation);
            self::_assert($result !== false);

            // For debug ===>
            // // If response process has written response.
            // $wasWrittenResponse = shmop_read($shmopId, $writtenResponseLocation, 1);
            // self::_assert($wasWrittenResponse !== false);
            // self::_assert($wasWrittenResponse === '1');
            // // If response is unique ID of this process.
            // $uniqueID2 = shmop_read($shmopId, $uniqueIdResponseLocation, $uniqueIdSize);
            // self::_assert($uniqueID2 === $uniqueID);
            // // This process accepted response.
            // $result = shmop_write($shmopId, '1', $lockingLocation);
            // self::_assert($result !== false);
            // <=== For debug
            //
            // Waits requested process until accept response for timeout.
            while (true) {
                // If the timeout.
                if (time() - $startTime > self::$_timeOutSeconds) {
                    exit;
                }
                // If request process accepted response.
                $isLocking = shmop_read($shmopId, $lockingLocation, 1);
                self::_assert($isLocking !== false);
                if ($isLocking === '1') {
                    break;
                }
                //// 0.1 second sleep.
                //usleep(100000);
                // If it has been unlocked.
                $isWrittenResponse = shmop_read($shmopId, $writtenResponseLocation, 1);
                self::_assert($isWrittenResponse !== false);
                if ($isWrittenResponse !== '1') {
                    //continue;
                    // break;
                    self::_assert(false);
                }
            }
            // Resets maximum execution time.
            $startTime = time();
        }
    }

    /**
     * Deletes shared memory, then truncates shared memory key file, then shutdowns.
     *
     * @return void
     */
    static function shutdown()
    {
        // Deletes shared memory.
        $result = shmop_delete(self::$_shmopID);
        self::_assert($result === true);
        // Closes the shared memory.
        shmop_close(self::$_shmopID);
        //// Cleans up file status cache.
        //clearstatcache();
        //// If shared file exists.
        //self::_assert(is_file(self::$_shmopKeyFilePath));
        //// Truncates shared memory key file.
        //$result = ftruncate(self::$_pFile, 0);
        //self::_assert($result === true);
        // Closes the file pointer.
        $result = fclose(self::$_pFile);
        self::_assert($result === true);
    }

}

// Pushes the shutdown class method.
register_shutdown_function('\BreakpointDebugging_LockByShmopResponse::shutdown');

\BreakpointDebugging_LockByShmopResponse::acceptRequest();
