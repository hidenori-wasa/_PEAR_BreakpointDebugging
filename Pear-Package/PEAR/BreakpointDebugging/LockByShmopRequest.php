<?php

/**
 * Class which locks php-code by shared memory operation.
 *
 * This class requires "shmop" extension.
 * We can synchronize applications by setting the same directory
 * to "$workDir = &B::refStatic('$_workDir'); $workDir = <work directory>;"
 * of "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'".
 *
 * Example of usage.
 *      $LockByShmopRequest = &\BreakpointDebugging_LockByShmopRequest::singleton(); // Creates a lock instance.
 *      $LockByShmopRequest->lock(); // Locks php-code.
 *      try {
 *          $pFile = \BreakpointDebugging::fopen(array ('file.txt', 'w+b')); // Truncates data.
 *          $data = fread($pFile, 1); // Reads data.
 *          $data++; // Changes data.
 *          fwrite($pFile, $data); // Writes data.
 *          fclose($pFile); // Flushes data, and releases file pointer resource.
 *      } catch (\Exception $e) {
 *          $LockByShmopRequest->unlock(); // Unlocks php-code.
 *          throw $e;
 *      }
 *      $LockByShmopRequest->unlock(); // Unlocks php-code.
 *
 * This class is same as "BreakpointDebugging_LockByFileExisting".
 * However, hard disk is not accessed.
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
use \BreakpointDebugging as B;
use \BreakpointDebugging_Shmop as BS;

/**
 * Class which locks php-code by shared memory operation.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_LockByShmopRequest extends \BreakpointDebugging_Lock
{
    private static $_uniqueIdSize;
    private $_uniqueID;
    private static $_writingRequestLocation;
    private static $_uniqueIdResponseLocation;
    private static $_writtenResponseLocation;
    private static $_lockingLocation;

    /**
     * Singleton method.
     *
     * @param int $timeout           The timeout.
     * @param int $expire            The number of seconds which lock-flag-file expires.
     * @param int $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return object Instance of this class.
     */
    static function &singleton($timeout = 60, $expire = 300, $sleepMicroSeconds = 100000)
    {
        B::assert(extension_loaded('shmop'), 101);

        return parent::singletonBase('\\' . __CLASS__, B::getStatic('$_workDir') . '/LockByShmopRequest.txt', $timeout, $expire, $sleepMicroSeconds);
    }

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath       Lock-flag-file path.
     * @param int    $timeout            The timeout.
     * @param int    $sharedMemoryExpire The number of seconds which shared memory expires.
     * @param int    $sleepMicroSeconds  Micro seconds to sleep.
     */
    protected function __construct($lockFilePath, $timeout, $sharedMemoryExpire, $sleepMicroSeconds)
    {
        parent::__construct($lockFilePath, $timeout, $sleepMicroSeconds);

        // Gets unique ID.
        $this->_uniqueID = uniqid('', true);
        // Calculates shared memory data locations.
        self::$_uniqueIdSize = strlen($this->_uniqueID);
        self::$_writingRequestLocation = 0;
        self::$_uniqueIdResponseLocation = self::$_uniqueIdSize * 2 + 2;
        self::$_writtenResponseLocation = self::$_uniqueIdResponseLocation + self::$_uniqueIdSize;
        self::$_lockingLocation = self::$_writtenResponseLocation + 1;

        // If shared memory key file exists.
        if (is_file($lockFilePath)) {
            // If shared memory exists.
            if ($this->_getSharedMemoryID()) {
                return;
            }
        }

        $lockingObject = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
        // Lock php code.
        $lockingObject->lock();
        try {
            while (true) {
                // In case of existing file.
                if (is_file($lockFilePath)) {
                    // The file header is opened reading and writing mode.
                    $this->pFile = B::fopen(array ($lockFilePath, 'r+b'));

                    $isContinue = false;
                    self::$sharedMemoryID = BS::getSharedMemoryID($this->pFile);
                    // Shared memory does not exist.
                    //if ($this->_getSharedMemoryID() === false) {
                    if (self::$sharedMemoryID === false) {
                        $isContinue = true;
                    } else {
                        // Shared memory is too old.
                        //$sharedMemoryAccessTime = shmop_read(self::$sharedMemoryID, self::HEXADECIMAL_SIZE * 3, self::HEXADECIMAL_SIZE);
                        $sharedMemoryAccessTime = fstat($this->pFile);
                        B::assert($sharedMemoryAccessTime !== false);
                        $sharedMemoryAccessTime = ['atime'];
                        $sharedMemoryAccessTime += 0;
                        if (time() - $sharedMemoryAccessTime >= $sharedMemoryExpire) {
                            // Delete shared memory.
                            $result = shmop_delete(self::$sharedMemoryID);
                            B::assert($result !== false);
                            shmop_close(self::$sharedMemoryID);
                            $isContinue = true;
                        }
                    }
                    if ($isContinue) {
                        $result = fclose($this->pFile);
                        B::assert($result !== false);
                        // Delete locking flag file.
                        B::unlink(array ($lockFilePath));
                        continue;
                    }
                } else { // In case of not existing file.
                    $this->pFile = B::fopen(array ($lockFilePath, 'w+b'));
                    // Build shared memory.
                    $this->_buildSharedMemory();
                }
                break;
            }
            // Close file handle.
            $result = fclose($this->pFile);
            B::assert($result !== false);

            // Creates response process.
            $fullFilePath = __DIR__ . '/LockByShmopResponse.php';
            $queryString = '"' . B::httpBuildQuery(array ()) . '"';
            if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // For Windows.
                $pPipe = popen('php.exe -f ' . $fullFilePath . ' -- ' . $queryString, 'r');
                if ($pPipe === false) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
                }
            } else { // For Unix.
                // "&" is the background execution of command.
                $pPipe = popen('php -f ' . $fullFilePath . ' -- ' . $queryString . ' &', 'r');
                if ($pPipe === false) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
                }
                // Executes command to asynchronization.
                if (!stream_set_blocking($pPipe, 0)) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "stream_set_blocking($pPipe, 0)".');
                }
            }
        } catch (\Exception $e) {
            // Unlock php code.
            $lockingObject->unlock();
            throw $e;
        }
        // Unlock php code.
        $lockingObject->unlock();
    }

    /**
     * Destructs this instance.
     */
    function __destruct()
    {
        $callStack = debug_backtrace();
        if (array_key_exists('line', $callStack[0])) {
            if ($callStack[0]['line'] === 0) { // In case of clone error.
                return;
            }
        }

        parent::__destruct();
    }

    /**
     * Build shared memory.
     *
     * @return void
     */
    private function _buildSharedMemory()
    {
        //list($sharedMemoryKey, self::$sharedMemoryID) = BW::buildSharedMemory(self::MEMORY_BLOCK_SIZE);
        $sharedMemorySize = self::$_lockingLocation + 1;
        list($sharedMemoryKey, self::$sharedMemoryID) = BS::buildSharedMemory($sharedMemorySize);
        // Register shared memory key.
        $result = rewind($this->pFile);
        B::assert($result !== false);
        $result = fwrite($this->pFile, sprintf('0x%08X', $sharedMemoryKey));
        B::assert($result !== false);
        $result = fflush($this->pFile);
        B::assert($result !== false);
        // Initialilze shared memory.
        $result = shmop_write(self::$sharedMemoryID, str_repeat("\x20", $sharedMemorySize), 0);
        B::assert($result !== false);
    }

    /**
     * References "self::$_uniqueIdSize".
     *
     * @return int& "self::$_uniqueIdSize".
     */
    static function &refUniqueIdSize()
    {
        B::limitAccess('BreakpointDebugging/LockByShmopResponse.php');

        return self::$_uniqueIdSize;
    }

    /**
     * Loops locking.
     *
     * @return void
     */
    protected function loopLocking()
    {
        $judgeTimeout = function ($startTime) {
            if (time() - $startTime > $this->timeout) {
                throw new \BreakpointDebugging_ErrorException('This process has been timeouted.', 101);
            }
        };

        $startTime = time();
        while (true) {
            // Waits until unlocking.
            while (true) {
                $isLocked = shmop_read(self::$sharedMemoryID, self::$_lockingLocation, 1);
                B::assert($isLocked !== false);
                if ($isLocked !== '1') {
                    break;
                }
                $judgeTimeout($startTime);
                // Wait micro seconds.
                usleep($this->sleepMicroSeconds);
            }
            // If other process is writing.
            $IsWritingRequest = shmop_read(self::$sharedMemoryID, self::$_writingRequestLocation, 1);
            B::assert($IsWritingRequest !== false);
            if ($IsWritingRequest === '1') {
                continue;
            }
            // Writes locking request.
            $result = shmop_write(self::$sharedMemoryID, sprintf('1%s%s1', $this->_uniqueID, $this->_uniqueID), 0);
            B::assert($result !== false);
            while (true) {
                // If response process has written response.
                $wasWrittenResponse = shmop_read(self::$sharedMemoryID, self::$_writtenResponseLocation, 1);
                B::assert($wasWrittenResponse !== false);
                if ($wasWrittenResponse === '1') {
                    break;
                }
                $judgeTimeout($startTime);
            }
            // If response is unique ID of this process.
            $uniqueID = shmop_read(self::$sharedMemoryID, self::$_uniqueIdResponseLocation, self::$_uniqueIdSize);
            if ($uniqueID === $this->_uniqueID) {
                break;
            }
        }
    }

    /**
     * Loops unlocking.
     *
     * @return void
     */
    protected function loopUnlocking()
    {
        // Initialilze shared memory.
        $result = shmop_write(self::$sharedMemoryID, str_repeat("\x20", $sharedMemorySize), 0);
        B::assert($result !== false);
    }

}

?>
