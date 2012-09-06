<?php

/**
 * Class which locks php-code by file existing.
 *
 * This class is required for environment where "flock()" doesn't exist.
 * We can synchronize applications by setting the same directory to "B::$workDir" of "BreakpointDebugging_MySetting.php".
 *
 * @example of usage.
 *      $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(); // Creates a lock instance.
 *      $lockByFileExisting->lock(); // Locks php-code.
 *      $pFile = fopen('file.txt', 'w+b'); // Truncates data.
 *      $data = fread($pFile, 1); // Reads data.
 *      $data++; // Changes data.
 *      fwrite($pFile, $data); // Writes data.
 *      fclose($pFile); // Flushes data.
 *      $lockByFileExisting->unlock(); // Unlocks php-code.
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
use \BreakpointDebugging as B;

/**
 * Class which locks php-code by file existing.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_LockByFileExisting extends \BreakpointDebugging_Lock
{
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
        return parent::singletonBase('\\' . __CLASS__, B::$workDir . '/LockByFileExisting.txt', $timeout, $expire, $sleepMicroSeconds);
    }

    /**
     * Singleton method of internal.
     *
     * @return object Instance of this class.
     */
    static function &internalSingleton()
    {
        return parent::singletonBase('\\' . __CLASS__, B::$workDir . '/LockByFileExistingOfInternal.txt', 60, 300, 100000, true);
    }

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath      Lock-flag-file path.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $flagFileExpire    Seconds number which flag-file expires.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    protected function __construct($lockFilePath, $timeout, $flagFileExpire, $sleepMicroSeconds)
    {
        parent::__construct($lockFilePath, $timeout, $sleepMicroSeconds);

        // The file does not exist.
        if (!is_file($lockFilePath)) {
            return;
        }
        $stat = stat($lockFilePath);
        // Locking flag file is too old.
        if (time() - $stat['mtime'] > $flagFileExpire) {
            restore_error_handler();
            // Delete locking flag file.
            @unlink($lockFilePath);
            set_error_handler('\BreakpointDebugging::errorHandler', -1);
        }
    }

    /**
     * Locking loop.
     *
     * @return void
     */
    protected function lockingLoop()
    {
        $startTime = time();
        while (($this->pFile = @B::fopen($this->lockFilePath, 'x+b', 0600)) === false) {
            if (time() - $startTime > $this->timeout) {
                if (B::internalException()) {
                    throw new \BreakpointDebugging_Error_Exception('This process has been timeouted.');
                }
                // We do not delete locking flag file here because we cannot lock php code.
                break;
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
        fclose($this->pFile);
        while (@unlink($this->lockFilePath) === false) {
            // Wait micro seconds.
            usleep($this->sleepMicroSeconds);
        }
    }

}

?>
