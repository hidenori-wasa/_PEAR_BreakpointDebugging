<?php

/**
 * Class which locks php-code by shared memory operation.
 *
 * This class requires "shmop" extension.
 * We can synchronize applications by setting the same directory
 * to "$workDir = &B::refStatic('$_workDir'); $workDir = <work directory>;"
 * of "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'".
 *
 * @example of usage.
 *      $lockByShmop = &\BreakpointDebugging_LockByShmop::singleton(); // Creates a lock instance.
 *      $lockByShmop->lock(); // Locks php-code.
 *      try {
 *          $pFile = \BreakpointDebugging::fopen(array ('file.txt', 'w+b')); // Truncates data.
 *          $data = fread($pFile, 1); // Reads data.
 *          $data++; // Changes data.
 *          fwrite($pFile, $data); // Writes data.
 *          fclose($pFile); // Flushes data, and releases file pointer resource.
 *      } catch (\Exception $e) {
 *          $lockByShmop->unlock(); // Unlocks php-code.
 *          throw $e;
 *      }
 *      $lockByShmop->unlock(); // Unlocks php-code.
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
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012-2013, Hidenori Wasa
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
 * Class which locks php-code by shared memory operation.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_LockByShmop extends \BreakpointDebugging_Lock
{
    /**
     * @var object The object for lock.
     */
    private static $_lockingObject;

    /**
     * Hexadecimal character string size.
     */
    const HEXADECIMAL_SIZE = 10;

    /**
     * @var int This process number.
     */
    private static $_processNumber;

    /**
     * Memory block size.
     */
    const MEMORY_BLOCK_SIZE = 4096;

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

        return parent::singletonBase('\\' . __CLASS__, B::getStatic('$_workDir') . '/LockByShmop.txt', $timeout, $expire, $sleepMicroSeconds);
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

        self::$_lockingObject[0] = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
        // Lock php code.
        self::$_lockingObject[0]->lock();
        try {
            while (true) {
                // In case of existing file.
                if (is_file($lockFilePath)) {
                    // The file header is opened reading and writing mode.
                    $this->pFile = B::fopen(array ($lockFilePath, 'r+b'));

                    $isContinue = false;
                    // Shared memory does not exist.
                    if ($this->_getSharedMemoryID() === false) {
                        $isContinue = true;
                    } else {
                        // Shared memory is too old.
                        $sharedMemoryAccessTime = shmop_read(self::$sharedMemoryID, self::HEXADECIMAL_SIZE * 3, self::HEXADECIMAL_SIZE);
                        B::assert($sharedMemoryAccessTime !== false);
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

            // Lock for current process of between "$this->loopLocking()" and "$this->loopUnlocking()".
            $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4, self::HEXADECIMAL_SIZE * 4 + 1);

            // Get minimum empty process number.
            $minimumEmptyProcessNumber = shmop_read(self::$sharedMemoryID, self::HEXADECIMAL_SIZE, self::HEXADECIMAL_SIZE);
            B::assert($minimumEmptyProcessNumber !== false);
            $minimumEmptyProcessNumber += 0;
            // Register this process.
            $result = shmop_write(self::$sharedMemoryID, '1', $minimumEmptyProcessNumber);
            B::assert($result !== false);
            // Register this process number.
            self::$_processNumber = $minimumEmptyProcessNumber;
            // Get maximum process number.
            $maximumProcessNumber = shmop_read(self::$sharedMemoryID, self::HEXADECIMAL_SIZE * 2, self::HEXADECIMAL_SIZE);
            B::assert($maximumProcessNumber !== false);
            $maximumProcessNumber += 0;
            if ($maximumProcessNumber < self::$_processNumber) {
                // @codeCoverageIgnoreStart
                // Because the following isn't executed in case of single process.
                // Register maximum process number.
                $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X', self::$_processNumber), self::HEXADECIMAL_SIZE * 2);
                B::assert($result !== false);
            }
            // @codeCoverageIgnoreEnd
            // Search minimum empty process number.
            while (true) {
                for ($searchLocation = $minimumEmptyProcessNumber; $searchLocation < self::MEMORY_BLOCK_SIZE; $searchLocation++) {
                    $result = shmop_read(self::$sharedMemoryID, $searchLocation, 1);
                    if ($result === '0') {
                        break 2;
                    }
                    B::assert($result !== false);
                }
                // @codeCoverageIgnoreStart
                // Because the following isn't executed in case of single process.
                // Unlock for current process.
                $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4);
                // Unlock php code.
                self::$_lockingObject[0]->unlock();
                // Sleep 1 second.
                usleep(1000000);
                // Lock php code.
                self::$_lockingObject[0]->lock();
                // Lock for current process.
                $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4, self::HEXADECIMAL_SIZE * 4 + 1);
            }
            // @codeCoverageIgnoreEnd
            // Register minimum empty process number.
            $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X', $searchLocation), self::HEXADECIMAL_SIZE);
            B::assert($result !== false);

            // Unlock for current process.
            $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4);
        } catch (\Exception $e) {
            // Unlock php code.
            self::$_lockingObject[0]->unlock();
            throw $e;
        }
        // Unlock php code.
        self::$_lockingObject[0]->unlock();
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

        // Lock php code.
        self::$_lockingObject[0]->lock();

        // Lock for current process of between "$this->loopLocking()" and "$this->loopUnlocking()".
        $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4, self::HEXADECIMAL_SIZE * 4 + 1);

        // When current process number is this process.
        $result = shmop_read(self::$sharedMemoryID, 0, self::HEXADECIMAL_SIZE);
        B::assert($result !== false);
        if ($result + 0 === self::$_processNumber) {
            // Get next current process number.
            $nextCurrentProcessNumber = $this->_getNextCurrentProcessNumber();
            // When other process has been created.
            if ($nextCurrentProcessNumber !== false) {
                // Register next current process number as current process number.
                $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X', $nextCurrentProcessNumber), 0);
                B::assert($result !== false);
            } else { // When all processes run out.
                // Initialilze shared memory.
                $result = shmop_write(self::$sharedMemoryID, str_repeat('0', self::MEMORY_BLOCK_SIZE), 0);
                B::assert($result !== false);
                // Register current process number, minimum empty process number, maximum process number, access time to shared memory, lock-process-valid-flag and current-process-valid-flag.
                $location = self::HEXADECIMAL_SIZE * 4 + 2;
                $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X0x%08X0x%08X0x%08X', $location, $location, $location, time()), 0);
                B::assert($result !== false);
            }
        }
        // Delete process flag.
        $result = shmop_write(self::$sharedMemoryID, '0', self::$_processNumber);
        B::assert($result !== false);
        // Get minimum empty process number.
        $minimumEmptyProcessNumber = shmop_read(self::$sharedMemoryID, self::HEXADECIMAL_SIZE, self::HEXADECIMAL_SIZE);
        B::assert($minimumEmptyProcessNumber !== false);
        $minimumEmptyProcessNumber += 0;
        if (self::$_processNumber < $minimumEmptyProcessNumber) {
            // Register minimum empty process number.
            $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X', self::$_processNumber), self::HEXADECIMAL_SIZE);
            B::assert($result !== false);
        }
        // Unlock for current process.
        $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4);

        // Unlock php code.
        self::$_lockingObject[0]->unlock();

        parent::__destruct();
    }

    /**
     * Get shared memory ID.
     *
     * @return bool Did succeed?
     */
    private function _getSharedMemoryID()
    {
        set_error_handler('\BreakpointDebugging::handleError', 0);
        $sharedMemoryKey = fread($this->pFile, 10);
        B::assert(!empty($sharedMemoryKey));
        // Open shared memory to read and write.
        $sharedMemoryID = @shmop_open($sharedMemoryKey, 'w', 0, 0);
        restore_error_handler();

        if (empty($sharedMemoryID)) {
            return false;
        }
        self::$sharedMemoryID = $sharedMemoryID;
        return true;
    }

    /**
     * Build shared memory.
     *
     * @return void
     */
    private function _buildSharedMemory()
    {
        set_error_handler('\BreakpointDebugging::handleError', 0);
        for ($count = 0; $count < 1000; $count++) {
            $sharedMemoryKey = (microtime(true) * 10000) & 0xFFFFFFFF;
            if ($sharedMemoryKey === -1) {
                // @codeCoverageIgnoreStart
                // Because this is a few probability.
                continue;
                // @codeCoverageIgnoreEnd
            }
            // It allocates shared memory area as current process number and minimum process number.
            self::$sharedMemoryID = @shmop_open($sharedMemoryKey, 'n', 0600, self::MEMORY_BLOCK_SIZE);
            if (self::$sharedMemoryID === false //
                || self::$sharedMemoryID === null //
            ) {
                // @codeCoverageIgnoreStart
                // Because this is a few probability.
                continue;
                // @codeCoverageIgnoreEnd
            }
            break;
        }
        restore_error_handler();

        if (self::$sharedMemoryID === false) {
            // @codeCoverageIgnoreStart
            // Because this is a few probability.
            throw new \BreakpointDebugging_ErrorException('New shared memory operation opening failed.', 101);
            // @codeCoverageIgnoreEnd
        }
        // Register shared memory key.
        $result = rewind($this->pFile);
        B::assert($result !== false);
        $result = fwrite($this->pFile, sprintf('0x%08X', $sharedMemoryKey));
        B::assert($result !== false);
        $result = fflush($this->pFile);
        B::assert($result !== false);
        // Initialilze shared memory.
        $result = shmop_write(self::$sharedMemoryID, str_repeat('0', self::MEMORY_BLOCK_SIZE), 0);
        B::assert($result !== false);
        // Register current process number, minimum empty process number, maximum process number, access time to shared memory, lock-process-valid-flag and current-process-valid-flag.
        $location = self::HEXADECIMAL_SIZE * 4 + 2;
        $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X0x%08X0x%08X0x%08X', $location, $location, $location, time()), 0);
        B::assert($result !== false);
    }

    /**
     * Get next current process number.
     *
     * @return mixed Next current process number, otherwise "false" when not found out.
     */
    private function _getNextCurrentProcessNumber()
    {
        $startProcessNumber = self::$_processNumber + 1;
        $maxProcessNumber = shmop_read(self::$sharedMemoryID, self::HEXADECIMAL_SIZE * 2, self::HEXADECIMAL_SIZE);
        B::assert($maxProcessNumber !== false);
        $maxProcessNumber += 0;
        $searchProcessNumbers = shmop_read(self::$sharedMemoryID, 0, $maxProcessNumber + 1);
        B::assert($searchProcessNumbers !== false);
        for ($searchProcessNumber = $startProcessNumber; $searchProcessNumber <= $maxProcessNumber; $searchProcessNumber++) {
            // @codeCoverageIgnoreStart
            // Because the following isn't executed in case of single process.
            if ($searchProcessNumbers[$searchProcessNumber] !== '2') {
                continue;
            }
            return $searchProcessNumber;
            // @codeCoverageIgnoreEnd
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
     *
     * @return void
     */
    private function _lockOn2Processes($lockFlagLocationOfItself, $lockFlagLocationOfpartner)
    {
        $startTime = time();
        while (true) {
            $result = shmop_write(self::$sharedMemoryID, '1', $lockFlagLocationOfItself);
            B::assert($result !== false);
            $result = shmop_read(self::$sharedMemoryID, $lockFlagLocationOfpartner, 1);
            B::assert($result !== false);
            if ($result === '1') {
                // @codeCoverageIgnoreStart
                // Because the following isn't executed in case of single process.
                $result = shmop_write(self::$sharedMemoryID, '0', $lockFlagLocationOfItself);
                B::assert($result !== false);
                if (time() - $startTime > $this->timeout) {
                    throw new \BreakpointDebugging_ErrorException('This process has been timeouted.', 101);
                }
                // Wait micro seconds.
                usleep($this->sleepMicroSeconds);
                continue;
                // @codeCoverageIgnoreEnd
            }
            break;
        }
    }

    /**
     * Unlock on 2 processes.
     *
     * @param int $lockFlagLocationOfItself The lock flag location of itself.
     *
     * @return void
     */
    private function _unlockOn2Processes($lockFlagLocationOfItself)
    {
        $result = shmop_write(self::$sharedMemoryID, '0', $lockFlagLocationOfItself);
        B::assert($result !== false);
    }

    /**
     * Loops locking.
     *
     * @return void
     */
    protected function loopLocking()
    {
        // Update shared memory access time.
        $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X', time()), self::HEXADECIMAL_SIZE * 3);
        B::assert($result !== false);
        // Is it registered as current process that this process is between "lock()" and "unlock()".
        $result = shmop_write(self::$sharedMemoryID, '2', self::$_processNumber);
        B::assert($result !== false);
        $startTime = time();
        while (($isSuccess = shmop_read(self::$sharedMemoryID, 0, self::HEXADECIMAL_SIZE) + 0) !== self::$_processNumber) {
            // @codeCoverageIgnoreStart
            // Because the following isn't executed in case of single process.
            B::assert($isSuccess !== false, 101);
            if (time() - $startTime > $this->timeout) {
                throw new \BreakpointDebugging_ErrorException('This process has been timeouted.', 101);
            }
            // Wait micro seconds.
            usleep($this->sleepMicroSeconds);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Loops unlocking.
     *
     * @return void
     */
    protected function loopUnlocking()
    {
        // Lock for a process which is locked by file existing.
        $this->_lockOn2Processes(self::HEXADECIMAL_SIZE * 4 + 1, self::HEXADECIMAL_SIZE * 4);

        $nextCurrentProcessNumber = $this->_getNextCurrentProcessNumber();
        // If next current process number exists.
        if ($nextCurrentProcessNumber !== false) {
            // Register next current process number as current process number.
            $result = shmop_write(self::$sharedMemoryID, sprintf('0x%08X', $nextCurrentProcessNumber), 0);
            B::assert($result !== false);
        }
        // It is registered that this process is not between "lock()" and "unlock()".
        $result = shmop_write(self::$sharedMemoryID, '1', self::$_processNumber);
        B::assert($result !== false);

        // Unlock for a process which is locked by file existing.
        $this->_unlockOn2Processes(self::HEXADECIMAL_SIZE * 4 + 1);
    }

}

?>
