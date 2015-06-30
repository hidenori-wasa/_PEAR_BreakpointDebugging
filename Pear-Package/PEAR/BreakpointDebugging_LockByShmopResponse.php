<?php

/**
 * This page is other process for response of "BreakpointDebugging_LockByShmopRequest" class.
 *
 * LICENSE:
 * Copyright (c) 2014-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Shmop as BS;

/**
 * This class is only one process for response of "BreakpointDebugging_LockByShmopRequest" class.
 *
 * PHP version 5.3.2-5.4.x
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_LockByShmopResponse
{
    /**
     * Timeout seconds.
     *
     * @var int
     */
    private static $_timeoutSeconds = 86400; // 1 day.

    /**
     * Response timeout seconds.
     *
     * @var int
     */
    private static $_responseTimeoutSeconds = 10;

    /**
     * Shared memory key file path.
     *
     * @var stirng
     */
    private static $_shmopKeyFilePath = '';

    /**
     * Shared memory ID.
     *
     * @var int
     */
    private static $_shmopID;

    /**
     * Shared memory key file pointer.
     *
     * @var resource
     */
    private static $_pFile;

    /**
     * Location that unit test stops this process.
     *
     * @var int
     */
    private static $_stopLocation;

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
        } else if ($assertion === false) {
            $errorInfo = <<<EOD
<pre>
<strong>Assertion failed.</strong>
FILE: $errorFile
LINE: $errorLine
</pre>
EOD;
        } else {
            return;
        }
        \BreakpointDebugging_Window::exitForError($errorInfo);
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
        self::$_stopLocation = $lockingLocation + 1;
        // Gets shared file path.
        self::$_shmopKeyFilePath = BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'LockByShmopRequest.txt';
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
            $shmopId = self::_buildSharedMemory(self::$_stopLocation + 1);
            break;
        }
        // Initialilze shared memory.
        $result = shmop_write($shmopId, str_repeat("\x20", self::$_stopLocation + 1), 0);
        self::_assert($result !== false);
        self::$_shmopID = $shmopId;

        // `"C:/Program Files/Mozilla Firefox/firefox.exe" "https://localhost/Pear-Package/index.php"`; // For debug.
        // Resets the timeout.
        $startTime = time();
        while (true) {
            // If other process stops this process. (For unit test)
            $isStop = shmop_read($shmopId, self::$_stopLocation, 1);
            if ($isStop === '1') {
                exit;
            }
            // If the timeout.
            if (time() - $startTime > self::$_timeoutSeconds) {
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
            // Resets the response timeout.
            $startTime = time();
            // Waits requested process until accept response for timeout.
            while (true) {
                // If other process stops this process. (For unit test)
                $isStop = shmop_read($shmopId, self::$_stopLocation, 1);
                if ($isStop === '1') {
                    exit;
                }
                // If response-timeout.
                if (time() - $startTime > self::$_responseTimeoutSeconds) {
                    // Initializes shared memory.
                    $result = shmop_write($shmopId, str_repeat("\x20", self::$_stopLocation + 1), 0);
                    self::_assert($result !== false);
                    break;
                }
                // If request process accepted response.
                $isLocking = shmop_read($shmopId, $lockingLocation, 1);
                self::_assert($isLocking !== false);
                if ($isLocking === '1') {
                    break;
                }
                // If it has been unlocked.
                $isWrittenResponse = shmop_read($shmopId, $writtenResponseLocation, 1);
                self::_assert($isWrittenResponse !== false);
                if ($isWrittenResponse !== '1') {
                    break;
                }
            }
            // Resets the timeout.
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
        // Closes the file pointer.
        $result = fclose(self::$_pFile);
        self::_assert($result === true);
        // Says this process shutdown.
        $result = shmop_write(self::$_shmopID, '0', self::$_stopLocation);
        self::_assert($result !== false);
        // Closes the shared memory.
        shmop_close(self::$_shmopID);
        // Returns a result.
        echo 'Done.';
    }

}

// Pushes the shutdown class method.
register_shutdown_function('\BreakpointDebugging_LockByShmopResponse::shutdown');

\BreakpointDebugging_LockByShmopResponse::acceptRequest();
