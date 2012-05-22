<?php

/**
 * This locks part code by lock flag file.
 * However, it still sometimes fails in file lock.
 *
 * This lock class by file is needed because the environment which doesn't have "flock()" exists.
 * This permits going through the only process or thread, then other processes or threads have been waited.
 * Therefore, the code part execution can be limited to the single process or the thread.
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
 * This locks part code by lock flag file.
 * However, it still sometimes fails in file lock.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 *
 * @example $lockByFile1 = new \BreakpointDebugging_LockByFile('./SomethingDir/FileWhichWantsToLock.txt');
 *           // Something execution...
 *           $lockByFile2 = new \BreakpointDebugging_LockByFile('./SomethingDir/FileWhichWantsToLock2.txt');
 *           // Something execution...
 *           $lockByFile3 = new \BreakpointDebugging_LockByFile('./SomethingDir3/FileWhichWantsToLock.txt');
 *           // Something execution...
 *           $lockByFile2->lock();
 *           // Something execution...
 *           $lockByFile1->lock();
 *           // Something execution...
 *           $lockByFile1->unlock();
 *           // Something execution...
 *           $lockByFile3->lock();
 *           // Something execution...
 *           $lockByFile1->lock();
 *           // Something execution...
 *           $lockByFile1->unlock();
 *           // Something execution...
 *           $lockByFile2->unlock();
 *           // Something execution...
 *           $lockByFile1->lock();
 *           // Something execution...
 *           $lockByFile1->unlock();
 *           // Something execution...
 *           $lockByFile3->unlock();
 *           // Something execution...
 */
final class BreakpointDebugging_LockByFile
{

    /**
     * @var int The lock count.
     */
    private $_lockCount = 0;

    /**
     * @var string The lock flag file path.
     */
    private $_lockingFlagFilePath;

    /**
     * @var string Unique id of multiprocess.
     */
    private $_uniqueId;

//    /**
//     * @var string Unique id of multiprocess to write.
//     */
//    private $_uniqueId12;

    /**
     * @var resource File pointer of unique id of multiprocess.
     */
    //private $_pUniqueId;
    public $_pUniqueId; // For debug.

    /**
     * @var int The timeout.
     */
    private $_timeout;

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath The file path which wants the lock.
     *                              This file must have reading permission.
     * @param int    $timeout      The timeout.
     */
    public function __construct($lockFilePath, $timeout = 50) // $timeout = 5)
    {
        $this->_timeout = $timeout;
        $className = substr(__CLASS__, strpos(__CLASS__, '_') + 1);
        $flagDir = __DIR__ . '/data/' . $className . '/Flag'; // Flag directory must exist.
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $flagDir = strtolower($flagDir);
            $lockFilePath = strtolower($lockFilePath);
        }
        $flagDir = str_replace('\\', '/', $flagDir);
        assert(is_dir($flagDir));
        $path = realpath($lockFilePath);
        if ($path === false) {
            B::internalException("Param1 file must have reading permission. ($lockFilePath)");
        }
        $path = str_replace('\\', '/', $path);
        $this->_lockingFlagFilePath = $flagDir . '/' . substr($path, strpos($path, '/') + 1);
        if (strlen($this->_lockingFlagFilePath) > PHP_MAXPATHLEN) {
            B::internalException("Param1 is too long because result which merged flag file exceeded PHP_MAXPATHLEN. ({$this->_lockingFlagFilePath}) PHP_MAXPATHLEN = " . PHP_MAXPATHLEN);
        }
        clearstatcache();
        if (!file_exists(dirname($this->_lockingFlagFilePath))) {
            restore_error_handler();
            // Make directory of the lock-flag file for initialize.
            @mkdir(dirname($this->_lockingFlagFilePath), 0600, true);
            set_error_handler('BreakpointDebugging::errorHandler', -1);
        }
        // Each process makes unique id.
        $this->_uniqueId = uniqid('', true);
        //$this->_uniqueId12 = str_repeat($this->_uniqueId, 12);
        // The file is opened append mode.
        $this->_pUniqueId = fopen($this->_lockingFlagFilePath, 'a+b');
        // Does not use writing buffer.
        $return = stream_set_write_buffer($this->_pUniqueId, 0);
        assert($return !== false);
    }

    function __destruct()
    {
        if (is_resource($this->_pUniqueId)) {
            fclose($this->_pUniqueId);
        }
    }

    /**
     * Reset timeout.
     *
     * @param int &$timeOfTimeout Time of timeout.
     */
    private function _resetTimeout(&$timeOfTimeout)
    {
        // Extend maximum execution time.
        set_time_limit($this->_timeout + 10);
        // Reset timeout.
        $timeOfTimeout = time() + $this->_timeout;
    }

    /**
     * Lock a code.
     * Permit going through the only process or thread, then other processes or threads have been waited.
     * This method may fail to lock negligible probability only.
     *
     * @return void
     */
    function lock()
    {
        // This copy avoids conflict by decreasing execution time of process.
        $pUniqueIdOfMultiprocess = $this->_pUniqueId;
        //// Each process makes unique id.
        //$uniqueIdOfMultiprocess = uniqid('', true);
        $uniqueIdOfMultiprocess = $this->_uniqueId;
        //$uniqueId12 = $this->_uniqueId12;

        $this->_resetTimeout($timeOfTimeout);
        while (true) {
            // A: When other process isn't processing.
            $fileStatus = fstat($pUniqueIdOfMultiprocess);
            if ($fileStatus['size'] === 0) {
                // At this point, other process doesn't enter from "A".
                // However, many processes may be entering.

                //$return = flock($pUniqueIdOfMultiprocess, LOCK_EX); // For debug.
                //assert($return !== false);

                fwrite($pUniqueIdOfMultiprocess, $uniqueIdOfMultiprocess);
                //// This writes 12 times for handling to slip of the lead pointer.
                //fwrite($pUniqueIdOfMultiprocess, $uniqueId12);

                //$return = flock($pUniqueIdOfMultiprocess, LOCK_UN); // For debug.
                //assert($return !== false);

//                while (true) {
//                    // Comparison can limit to one process because other process doesn't enter from "A" when this process is reading "unique ID".
//                    rewind($pUniqueIdOfMultiprocess);
//                    $return = fread($pUniqueIdOfMultiprocess, 23);
//                    if (ftell($pUniqueIdOfMultiprocess) === 23) {
//                        if ($return === $uniqueIdOfMultiprocess) {
//                            break 2;
//                        } else {
//                            break;
//                        }
//                    }
//                }
                // Comparison limits to one process because other process doesn't enter from "A" when this process is reading "unique ID".
                //// But, it may fail by slip of file read pointer by other process influence.
                rewind($pUniqueIdOfMultiprocess);
                if (fread($pUniqueIdOfMultiprocess, 23) === $uniqueIdOfMultiprocess) {
                    break;
                }
            }

            // In case of timeout.
            if (time() > $timeOfTimeout) {
                // Initialize flag file for server freeze handling.
                // Lay the lock flag.
                ftruncate($pUniqueIdOfMultiprocess, 0);
                try {
                    B::internalException('Timeouted.');
                } catch (\BreakpointDebugging_Error_Exception $pException) {
                    B::exceptionHandler($pException);
                }
                $this->_resetTimeout($timeOfTimeout);
            }
            //// Wait 0.5-2 second.
            //usleep(rand(500000, 2000000));
            // Wait 2 second.
            usleep(2000000);
        }

        assert($this->_lockCount === 0);
        $this->_lockCount++;
    }

    /**
     * Unlock a code.
     * This permits going through other processes or threads.
     *
     * @return void
     */
    function unlock()
    {
        $this->_lockCount--;
        assert($this->_lockCount === 0);

        // Lay the lock flag.
        ftruncate($this->_pUniqueId, 0);
    }

}

?>
