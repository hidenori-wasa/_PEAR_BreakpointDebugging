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
 * However, hard disk has only a few access.
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
use \BreakpointDebugging_BlackList as BB;

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
    private static $_shmopKeyFilePath;
    private static $_uniqueIdSize;
    private $_uniqueID;
    private static $_writingRequestLocation;
    private static $_uniqueIdResponseLocation;
    private static $_writtenResponseLocation;
    private static $_lockingLocation;
    private static $_stopLocation;
    private static $_lockingObject;
    private $_sharedMemoryID;
    private $_pPipe;

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
     * Gets shared memory key file pointer and ID if it does not exist.
     *
     * @param resource $pFile          Current shared memory key file pointer.
     * @param int      $sharedMemoryID Current shared memory ID.
     *
     * @return array Shared memory key file pointer and ID.
     */
    private function _getShmopKeyFilePointerAndID($pFile, $sharedMemoryID)
    {
        if (is_resource($pFile)) {
            rewind($pFile);
        } else {
            set_error_handler('\BreakpointDebugging::handleError', 0);
            // Opens shared memory key file.
            $pFile = @fopen(self::$_shmopKeyFilePath, 'rb');
            restore_error_handler();
        }
        if (is_resource($pFile)) {
            if (!$sharedMemoryID) {
                $sharedMemoryID = BS::getSharedMemoryID($pFile);
                if (!$sharedMemoryID) {
                    $result = fclose($pFile);
                    B::assert($result === true);
                }
            }
        }
        return array ($pFile, $sharedMemoryID);
    }

    /**
     * Opens command line process pipe.
     *
     * @param string $fullFilePath Full file path to open a pipe as page.
     * @param array  $queryString  A query character string.
     *
     * @return resource Opened process pipe.
     * @throws \BreakpointDebugging_ErrorException
     */
    static function popen($fullFilePath, $queryString)
    {
        // Creates and runs a test process.
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // For Windows.
            // include_once $fullFilePath; // For debug.
            $pPipe = popen('php.exe -f ' . $fullFilePath . ' -- ' . $queryString, 'r');
            if ($pPipe === false) {
                throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
            }
        } else { // For Unix.
            // include_once $fullFilePath; // For debug.
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
        return $pPipe;
    }

    /**
     * Waits for multiple processes, then returns its results.
     *
     * @param array $pPipes The pipes of multiple processes which opened by "popen()".
     *
     * @return array Results of multiple processes.
     * @throws \BreakpointDebugging_ErrorException
     */
    static function waitForMultipleProcesses($pPipes)
    {
        $results = array ();
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            foreach ($pPipes as $pPipe) {
                $results[] = stream_get_contents($pPipe);
            }
        } else {
            foreach ($pPipes as $pPipe) {
                // Waits until command execution end.
                if (!stream_set_blocking($pPipe, 1)) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "stream_set_blocking($pPipe, 1)".');
                }
                // Gets command's stdout.
                $results[] = stream_get_contents($pPipe);
            }
        }
        return $results;
    }

    /**
     * The after treatment.
     *
     * @return void
     */
    private function _afterTreatment()
    {
        // Waits for process return.
        self::waitForMultipleProcesses(array ($this->_pPipe));
        // Closes the pipe.
        $result = pclose($this->_pPipe);
        B::assert($result !== -1);
        // Closes the shared memory.
        shmop_close($this->_sharedMemoryID);
        // Initializes the shared memory ID.
        $this->_sharedMemoryID = null;
    }

    /**
     * Creates response process.
     *
     * @param type $pFile
     * @param type $sharedMemoryID
     *
     * @return array Shared memory key file pointer and ID.
     *
     * @throws Exception
     * @throws \BreakpointDebugging_ErrorException
     */
    private function _createResponseProcess($pFile, $sharedMemoryID)
    {
        // Locks the "php" code.
        self::$_lockingObject->lock();
        try {
            // Gets shared memory key file pointer and ID if it does not exist.
            list($pFile, $sharedMemoryID) = self::_getShmopKeyFilePointerAndID($pFile, $sharedMemoryID);
            // If shared memory exists.
            if ($sharedMemoryID) {
                // If response process exists.
                if (shmop_read($sharedMemoryID, self::$_stopLocation, 1) === ' ') {
                    goto AFTER_TREATMENT;
                }
            }
            // Copies response page to current work directory.
            B::copyResourceToCWD('BreakpointDebugging_LockByShmopResponse.php', '');
            // Creates response process.
            $fullFilePath = './BreakpointDebugging_LockByShmopResponse.php';
            $queryString = '"' . B::httpBuildQuery(array ()) . '"';
            $this->_pPipe = &BB::refLockByShmopRequestPPipe();
            $this->_pPipe = self::popen($fullFilePath, $queryString);
            // Waits until shared memory initializing.
            while (true) {
                // 0.1 second sleep.
                usleep(100000);
                // Gets shared memory key file pointer and ID if it does not exist.
                list($pFile, $sharedMemoryID) = self::_getShmopKeyFilePointerAndID($pFile, $sharedMemoryID);
                // If shared memory exists.
                if ($sharedMemoryID) {
                    $isInit = shmop_read($sharedMemoryID, self::$_stopLocation, 1);
                    B::assert($isInit !== false);
                    if ($isInit === ' ') {
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            // Unlocks "php" code.
            self::$_lockingObject->unlock();
            throw $e;
        }
        // If this process is not unit test.
        if (!(B::getStatic('$exeMode') & B::UNIT_TEST)) {
            // Unlocks "php" code.
            self::$_lockingObject->unlock();
            echo '<strong>Sorry, please retry.</strong>';
            flush();
            $this->_afterTreatment();
            exit;
        }

        AFTER_TREATMENT:
        // Unlocks "php" code.
        self::$_lockingObject->unlock();

        return array ($pFile, $sharedMemoryID);
    }

    /**
     * Constructs the lock system.
     *
     * @param string $shmopKeyFilePath        Shared memory key file path.
     * @param int    $timeout                 The timeout.
     * @param int    $dummySharedMemoryExpire The number of seconds which shared memory expires.
     * @param int    $sleepMicroSeconds       Micro seconds to sleep.
     */
    protected function __construct($shmopKeyFilePath, $timeout, $dummySharedMemoryExpire, $sleepMicroSeconds)
    {
        parent::__construct($shmopKeyFilePath, $timeout, $sleepMicroSeconds);

        self::$_shmopKeyFilePath = $shmopKeyFilePath;
        // Gets unique ID.
        $this->_uniqueID = uniqid('', true);
        // Calculates shared memory data locations.
        self::$_uniqueIdSize = strlen($this->_uniqueID);
        self::$_writingRequestLocation = 0;
        self::$_uniqueIdResponseLocation = self::$_uniqueIdSize * 2 + 2;
        self::$_writtenResponseLocation = self::$_uniqueIdResponseLocation + self::$_uniqueIdSize;
        self::$_lockingLocation = self::$_writtenResponseLocation + 1;
        self::$_stopLocation = self::$_lockingLocation + 1;
        self::$_lockingObject = &\BreakpointDebugging_LockByFileExisting::internalSingleton();

        $pFile = null;
        $this->_sharedMemoryID = &BB::refLockByShmopRequestSharedMemoryID();
        while (true) {
            // Gets shared memory key file pointer and ID if it does not exist.
            list($pFile, $this->_sharedMemoryID) = self::_getShmopKeyFilePointerAndID($pFile, $this->_sharedMemoryID);
            // If shared memory exists.
            if ($this->_sharedMemoryID) {
                // Closes the file pointer.
                $result = fclose($pFile);
                B::assert($result === true);
                // If response process was not shutdowned.
                if (shmop_read($this->_sharedMemoryID, self::$_stopLocation, 1) !== '0') {
                    return;
                }
            }
            // Creates response process.
            list($pFile, $this->_sharedMemoryID) = self::_createResponseProcess($pFile, $this->_sharedMemoryID);
        }
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
     * Loops locking.
     *
     * @return void
     */
    protected function loopLocking()
    {
        $judgeTimeout = function ($startTime, $timeout) {
            if (time() - $startTime > $timeout) {
                throw new \BreakpointDebugging_ErrorException('This process has been timeouted.', 101);
            }
        };

        $pFile = null;
        $startTime = time();
        while (true) {
            set_error_handler('\BreakpointDebugging::handleError', 0);
            while (true) {
                // Waits until unlocking.
                while (true) {
                    // If response process was shutdowned.
                    if (shmop_read($this->_sharedMemoryID, self::$_stopLocation, 1) === '0') {
                        break 2;
                    }
                    $isLocked = shmop_read($this->_sharedMemoryID, self::$_lockingLocation, 1);
                    B::assert($isLocked !== false);
                    if ($isLocked !== '1') {
                        break;
                    }
                    $judgeTimeout($startTime, $this->timeout);
                    // Waits micro seconds.
                    usleep($this->sleepMicroSeconds);
                }

                $IsWritingRequest = shmop_read($this->_sharedMemoryID, self::$_writingRequestLocation, 1);
                B::assert($IsWritingRequest !== false);
                // If other process is writing.
                if ($IsWritingRequest === '1') {
                    continue;
                }
                // Writes locking request.
                $result = shmop_write($this->_sharedMemoryID, '1' . $this->_uniqueID . $this->_uniqueID . '1', 0);
                B::assert($result !== false);
                // Waits until response.
                while (true) {
                    // If response process was shutdowned.
                    if (shmop_read($this->_sharedMemoryID, self::$_stopLocation, 1) === '0') {
                        break 2;
                    }
                    $wasWrittenResponse = shmop_read($this->_sharedMemoryID, self::$_writtenResponseLocation, 1);
                    B::assert($wasWrittenResponse !== false);
                    // If response process has written response.
                    if ($wasWrittenResponse === '1') {
                        break;
                    }
                    $judgeTimeout($startTime, $this->timeout);
                }
                $uniqueID = shmop_read($this->_sharedMemoryID, self::$_uniqueIdResponseLocation, self::$_uniqueIdSize);
                // If response is not unique ID of this process.
                if ($uniqueID !== $this->_uniqueID) {
                    continue;
                }
                // This process accepted response.
                $result = shmop_write($this->_sharedMemoryID, '1', self::$_lockingLocation);
                break 2;
            }
            restore_error_handler();
            $this->_afterTreatment();
            // Creates response process because response process was shutdowned.
            list($pFile, $this->_sharedMemoryID) = self::_createResponseProcess($pFile, $this->_sharedMemoryID);
        }
        restore_error_handler();
        if (is_resource($pFile)) {
            // Closes the file pointer.
            $result = fclose($pFile);
            B::assert($result === true);
        }
    }

    /**
     * Loops unlocking.
     *
     * @return void
     */
    protected function loopUnlocking()
    {
        // Initializes shared memory.
        $result = shmop_write($this->_sharedMemoryID, str_repeat("\x20", self::$_stopLocation + 1), 0);
        B::assert($result !== false);
    }

    static function shutdown()
    {
        $sharedMemoryID = &BB::refLockByShmopRequestSharedMemoryID();
        if ($sharedMemoryID !== null) {
            // Closes the shared memory.
            shmop_close($sharedMemoryID);
            // Initializes the shared memory ID.
            $sharedMemoryID = null;
        }
    }

}

// Pushes the shutdown class method.
register_shutdown_function('\BreakpointDebugging_LockByShmopRequest::shutdown');
