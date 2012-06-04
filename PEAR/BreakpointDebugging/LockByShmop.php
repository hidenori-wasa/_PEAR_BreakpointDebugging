<?php

/**
 * Class which locks by shared memory operation.
 *
 * This class is same as BreakpointDebugging_LockByFileExisting.
 * However, hard disk is not accessed when "lock()" and "unlock()" is called.
 * Shared memory size is "self::MEMORY_BLOCK_SIZE".
 * Shared memory structure:
 *      First "self::HEXADECIMAL_SIZE" byte is current process number.
 *      Next "self::HEXADECIMAL_SIZE" byte is minimum empty process number.
 *      Next "self::HEXADECIMAL_SIZE" byte is maximum process number.
 *      Next "self::HEXADECIMAL_SIZE" byte is access time to shared memory.
 *      Next byte is lock-process-valid-flag.
 *      Next byte is current-process-valid-flag.
 *      Next byte is a process flag.
 *          .
 *          .
 *          .
 *      Last byte is a process flag.
 * The Kind of lock-process-valid-flag and current-process-valid-flag.
 *      '0' means that process unlocks.
 *      '1' means that process locks.
 * Process flag kind:
 *      '0' means that process is empty.
 *      '1' means that process is not between "lock()" and "unlock()".
 *      '2' means that process is between "lock()" and "unlock()".
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012, Hidenori Wasa
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
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
require_once './BreakpointDebugging_MySetting.php';

use BreakpointDebugging as B;

/**
 * Class which lock by shared memory operation.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_LockByShmop extends \BreakpointDebugging_Lock
{

    /**
     * @var object Maintains a instance.
     */
    private static $_instance;

    /**
     * @var object The object for lock.
     */
    private $_lockingObject;

    /**
     * Hexadecimal character string size.
     */

    const HEXADECIMAL_SIZE = 10;

    /**
     * @var int Shared memory ID.
     */
    private $_sharedMemoryID;

    /**
     * @var int This process number.
     */
    private $_processNumber;

    /**
     * Memory block size.
     */

    const MEMORY_BLOCK_SIZE = 4096;

    /**
     * We must use singleton method because processes is executed in turn.
     *
     * @param string $lockFilePath       The file path which wants the lock.
     *                                   This file must have reading permission.
     * @param int    $timeout            The timeout.
     * @param int    $sharedMemoryExpire The number of seconds which shared memory expires.
     * @param int    $sleepMicroSeconds  Micro seconds to sleep.
     *
     * @return object Instance of this class.
     */
    public static function singleton($lockFilePath, $timeout = 60, $sharedMemoryExpire = 300, $sleepMicroSeconds = 100000)
    {
        static $storeLockFilePath = null;

        if ($lockFilePath !== $storeLockFilePath) {
            assert($storeLockFilePath === null);
            $storeLockFilePath = $lockFilePath;
        }
        if (!isset(self::$_instance)) {
            $c = '\\' . __CLASS__;
            self::$_instance = new $c($lockFilePath, $timeout, $sharedMemoryExpire, $sleepMicroSeconds);
        }
        return self::$_instance;
    }

    /**
     * Prevents duplicating an instance.
     *
     * @return void
     */
    public function __clone()
    {
        B::internalException('Clone is not allowed.');
    }

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath       The file path which wants the lock.
     *                                   This file must have reading permission.
     * @param int    $timeout            The timeout.
     * @param int    $sharedMemoryExpire The number of seconds which shared memory expires.
     * @param int    $sleepMicroSeconds  Micro seconds to sleep.
     */
    protected function __construct($lockFilePath, $timeout, $sharedMemoryExpire, $sleepMicroSeconds)
    {
        parent::__construct($lockFilePath, $timeout, $sleepMicroSeconds);

        $this->_lockingObject = new BreakpointDebugging_LockByFileExisting(__FILE__);
        // Lock php code.
        $this->_lockingObject->lock();

        restore_error_handler();
        $this->pFile = @fopen($this->lockingFlagFilePath, 'x+b');
        set_error_handler('BreakpointDebugging::errorHandler', -1);
        while (true) {
            // In case of existing file.
            if ($this->pFile === false) {
                // The file header is opened reading and writing mode.
                $this->pFile = fopen($this->lockingFlagFilePath, 'r+b');
                // Does not use writing and reading buffer.
                stream_set_write_buffer($this->pFile, 0);

                $isContinue = false;
                // Shared memory does not exist.
                if ($this->_getSharedMemoryID() === false) {
                    $isContinue = true;
                } else {
                    // Shared memory is too old.
                    $sharedMemoryAccessTime = shmop_read($this->_sharedMemoryID, self::HEXADECIMAL_SIZE * 3, self::HEXADECIMAL_SIZE) + 0;
                    if (time() - $sharedMemoryAccessTime > $sharedMemoryExpire) {
                        var_dump('Shared memory expired.'); // For debug.
                        // Delete shared memory.
                        if (shmop_delete($this->_sharedMemoryID) === false) {
                            B::internalException('This process failed to delete shared memory.');
                        }
                        shmop_close($this->_sharedMemoryID);
                        $isContinue = true;
                    }
                }
                if ($isContinue) {
                    fclose($this->pFile);
                    // Delete locking flag file.
                    $this->pFile = fopen($this->lockingFlagFilePath, 'w+b');
                    continue;
                }
            } else { // In case of not existing file.
                // Does not use writing and reading buffer.
                stream_set_write_buffer($this->pFile, 0);

                // Build shared memory.
                $this->_buildSharedMemory();
            }
            break;
        }
        // Close file handle.
        fclose($this->pFile);

        // Lock for current process of between "$this->lockingLoop()" and "$this->unlockingLoop()".
        $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4, self::HEXADECIMAL_SIZE * 4 + 1);

        // Get minimum empty process number.
        $minimumEmptyProcessNumber = shmop_read($this->_sharedMemoryID, self::HEXADECIMAL_SIZE, self::HEXADECIMAL_SIZE) + 0;
        // Register this process.
        shmop_write($this->_sharedMemoryID, '1', $minimumEmptyProcessNumber);
        // Register this process number.
        $this->_processNumber = $minimumEmptyProcessNumber;
        // Get maximum process number.
        $maximumProcessNumber = shmop_read($this->_sharedMemoryID, self::HEXADECIMAL_SIZE * 2, self::HEXADECIMAL_SIZE) + 0;
        if ($maximumProcessNumber < $this->_processNumber) {
            // Register maximum process number.
            shmop_write($this->_sharedMemoryID, sprintf('0x%08X', $this->_processNumber), self::HEXADECIMAL_SIZE * 2);
        }

        // Search minimum empty process number.
        while (true) {
            for ($searchLocation = $minimumEmptyProcessNumber; $searchLocation < self::MEMORY_BLOCK_SIZE; $searchLocation++) {
                if (shmop_read($this->_sharedMemoryID, $searchLocation, 1) === '0') {
                    break 2;
                }
            }
            // Unlock for current process.
            $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4);
            // Unlock php code.
            $this->_lockingObject->unlock();
            // Sleep 1 second.
            usleep(1000000);
            // Lock php code.
            $this->_lockingObject->lock();
            // Lock for current process.
            $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4, self::HEXADECIMAL_SIZE * 4 + 1);
        }
        // Register minimum empty process number.
        shmop_write($this->_sharedMemoryID, sprintf('0x%08X', $searchLocation), self::HEXADECIMAL_SIZE);

        // Unlock for current process.
        $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4);

        // Unlock php code.
        $this->_lockingObject->unlock();
    }

    function __destruct()
    {
        // Lock php code.
        $this->_lockingObject->lock();

        // Lock for current process of between "$this->lockingLoop()" and "$this->unlockingLoop()".
        $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4, self::HEXADECIMAL_SIZE * 4 + 1);

        // When current process number is this process.
        if (shmop_read($this->_sharedMemoryID, 0, self::HEXADECIMAL_SIZE) + 0 === $this->_processNumber) {
            // Get next current process number.
            $nextCurrentProcessNumber = $this->_getNextCurrentProcessNumber();
            // When other process has been created.
            if ($nextCurrentProcessNumber !== false) {
                var_dump('Other process has been created.'); // For debug.
                // Register next current process number as current process number.
                shmop_write($this->_sharedMemoryID, sprintf('0x%08X', $nextCurrentProcessNumber), 0);
            } else { // When all processes run out.
                // Initialilze shared memory.
                shmop_write($this->_sharedMemoryID, str_repeat('0', self::MEMORY_BLOCK_SIZE), 0);
                // Register current process number, minimum empty process number, maximum process number, lock-process-valid-flag and current-process-valid-flag.
                $location = self::HEXADECIMAL_SIZE * 4 + 2;
                shmop_write($this->_sharedMemoryID, sprintf('0x%08X0x%08X0x%08X0x%08X', $location, $location, $location, time()), 0);
            }
        }
        // Delete process flag.
        shmop_write($this->_sharedMemoryID, '0', $this->_processNumber);
        // Get minimum empty process number.
        $minimumEmptyProcessNumber = shmop_read($this->_sharedMemoryID, self::HEXADECIMAL_SIZE, self::HEXADECIMAL_SIZE) + 0;
        if ($this->_processNumber < $minimumEmptyProcessNumber) {
            // Register minimum empty process number.
            shmop_write($this->_sharedMemoryID, sprintf('0x%08X', $this->_processNumber), self::HEXADECIMAL_SIZE);
        }
        // Unlock for current process.
        $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4);

        // Unlock php code.
        $this->_lockingObject->unlock();

        parent::__destruct();
    }

    /**
     * Get shared memory ID.
     *
     * @return bool Did succeed?
     */
    private function _getSharedMemoryID()
    {
        restore_error_handler();
        for ($count = 0; $count < 100; $count++) {
            rewind($this->pFile);
            $sharedMemoryKey = fread($this->pFile, 10);
            // Open shared memory to read and write.
            $sharedMemoryID = @shmop_open($sharedMemoryKey, 'w', 0, 0);
            if ($sharedMemoryID === false) {
                // Wait 0.1 second.
                usleep(100000);
                continue;
            }
            break;
        }
        set_error_handler('BreakpointDebugging::errorHandler', -1);
        if ($sharedMemoryID === false || $sharedMemoryKey === '') {
            return false;
        }
        $this->_sharedMemoryID = $sharedMemoryID;
        return true;
    }

    /**
     * Build shared memory.
     *
     * @return void
     */
    private function _buildSharedMemory()
    {
        restore_error_handler();
        for ($count = 0; $count < 1000; $count++) {
            $sharedMemoryKey = (microtime(true) * 10000) & 0xFFFFFFFF;
            if ($sharedMemoryKey === -1) {
                continue;
            }
            // It allocates shared memory area as current process number and minimum process number.
            $this->_sharedMemoryID = @shmop_open($sharedMemoryKey, 'n', 0600, self::MEMORY_BLOCK_SIZE);
            if ($this->_sharedMemoryID === false) {
                continue;
            }
            break;
        }
        set_error_handler('BreakpointDebugging::errorHandler', -1);
        if ($this->_sharedMemoryID === false) {
            B::internalException('New shared memory operation opening failed.');
        }
        // Register shared memory key.
        rewind($this->pFile);
        fwrite($this->pFile, sprintf('0x%08X', $sharedMemoryKey));
        // Initialilze shared memory.
        shmop_write($this->_sharedMemoryID, str_repeat('0', self::MEMORY_BLOCK_SIZE), 0);
        // Register current process number, minimum empty process number, maximum process number, lock-process-valid-flag and current-process-valid-flag.
        $location = self::HEXADECIMAL_SIZE * 4 + 2;
        shmop_write($this->_sharedMemoryID, sprintf('0x%08X0x%08X0x%08X0x%08X', $location, $location, $location, time()), 0);
    }

    /**
     * Get next current process number.
     *
     * @return mixed Next current process number, otherwise "false" when not found out.
     */
    private function _getNextCurrentProcessNumber()
    {
        $startProcessNumber = $this->_processNumber + 1;
        $maxProcessNumber = shmop_read($this->_sharedMemoryID, self::HEXADECIMAL_SIZE * 2, self::HEXADECIMAL_SIZE) + 0;
        $searchProcessNumbers = shmop_read($this->_sharedMemoryID, 0, $maxProcessNumber + 1);
        for ($searchProcessNumber = $startProcessNumber; $searchProcessNumber <= $maxProcessNumber; $searchProcessNumber++) {
            if ($searchProcessNumbers[$searchProcessNumber] !== '2') {
                continue;
            }
            return $searchProcessNumber;
        }
        for ($searchProcessNumber = self::HEXADECIMAL_SIZE * 4 + 2; $searchProcessNumber < $startProcessNumber; $searchProcessNumber++) {
            if ($searchProcessNumbers[$searchProcessNumber] !== '2') {
                continue;
            }
            return $searchProcessNumber;
        }
        return false;
    }

    /**
     * Lock on 2 processes.
     *
     * @param int $lockFlagLocationOfItself  The lock flag location of itself.
     * @param int $lockFlagLocationOfpartner The lock flag location of partner.
     */
    private function _lockOn2Processes($lockFlagLocationOfItself, $lockFlagLocationOfpartner)
    {
        $startTime = time();
        while (true) {
            shmop_write($this->_sharedMemoryID, '1', $lockFlagLocationOfItself);
            if (shmop_read($this->_sharedMemoryID, $lockFlagLocationOfpartner, 1) === '1') {
                shmop_write($this->_sharedMemoryID, '0', $lockFlagLocationOfItself);
                if (time() - $startTime > $this->timeout) {
                    BreakpointDebugging::internalException('This process has been timeouted.');
                }
                // Wait micro seconds.
                usleep($this->sleepMicroSeconds);
                continue;
            }
            break;
        }
    }

    /**
     * Unlock on 2 processes.
     *
     * @param int $lockFlagLocationOfItself  The lock flag location of itself.
     */
    private function _unlockOn2Processes($lockFlagLocationOfItself)
    {
        shmop_write($this->_sharedMemoryID, '0', $lockFlagLocationOfItself);
    }

    /**
     * Locking loop.
     *
     * @return void
     */
    protected function lockingLoop()
    {
        // Update shared memory access time.
        shmop_write($this->_sharedMemoryID, sprintf('0x%08X', time()), self::HEXADECIMAL_SIZE * 3);
        // Is it registered as current process that this process is between "lock()" and "unlock()".
        shmop_write($this->_sharedMemoryID, '2', $this->_processNumber);
        $startTime = time();
        while (($isSuccess = shmop_read($this->_sharedMemoryID, 0, self::HEXADECIMAL_SIZE) + 0) !== $this->_processNumber) {
            assert($isSuccess !== false);
            if (time() - $startTime > $this->timeout) {
                BreakpointDebugging::internalException('This process has been timeouted.');
            }
            // Wait micro seconds.
            usleep($this->sleepMicroSeconds);
        }
    }

    /**
     * Unlocking loop.
     *
     * @return void
     */
    protected function unlockingLoop()
    {
        // Lock for a process which is locked by file existing.
        $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4 + 1, self::HEXADECIMAL_SIZE * 4);

        // Register next current process number as current process number.
        shmop_write($this->_sharedMemoryID, sprintf('0x%08X', $this->_getNextCurrentProcessNumber()), 0);
        // Is it registered that this process is not between "lock()" and "unlock()".
        shmop_write($this->_sharedMemoryID, '1', $this->_processNumber);

        // Unlock for a process which is locked by file existing.
        $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4 + 1);
    }

}

?>
