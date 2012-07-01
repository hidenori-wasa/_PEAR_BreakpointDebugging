<?php

/**
 * Class which locks php-code by file existing.
 *
 * This class is required for environment where "flock()" doesn't exist.
 * We can synchronize applications by setting the same directory to "B::$workDir" of "BreakpointDebugging_MySetting.php".
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
use BreakpointDebugging as B;

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
//    /**
//     * Construct the lock system object per file.
//     *
//     * @param string $lockFilePath      The file path for lock.
//     *                                  This file must have reading permission.
//     * @param int    $timeout           Seconds number of timeout.
//     * @param int    $flagFileExpire    Seconds number which flag-file expires.
//     * @param int    $sleepMicroSeconds Micro seconds to sleep.
//     */
//    static function singletonPerFile($lockFilePath, $timeout = 60, $flagFileExpire = 300, $sleepMicroSeconds = 100000)
//    {
//        global $_BreakpointDebugging;
//
//        $fullLockFilePath = getFullLockFilePath($lockFilePath);
//        // Search about whether or not the object exists per file.
//        foreach ($_BreakpointDebugging->lockByFileExistingObjects as $lockByFileExistingObject) {
//            if ($lockByFileExistingObject->fullLockFilePath === $fullLockFilePath) {
//                return $lockByFileExistingObject;
//            }
//        }
//        $c = '\\' . __CLASS__;
//        return new $c($fullLockFilePath, $timeout, $flagFileExpire, $sleepMicroSeconds);
//    }
    /**
     * Singleton method.
     *
     * @param string $lockFilePath       The file path which wants the lock.
     *                                   This file must have reading permission.
     * @param int    $timeout            The timeout.
     * @param int    $expire             The number of seconds which lock-flag-file expires.
     * @param int    $sleepMicroSeconds  Micro seconds to sleep.
     *
     * @return object Instance of this class.
     */
    static function singleton($lockFilePath, $timeout = 60, $expire = 300, $sleepMicroSeconds = 100000)
    {
        return parent::singletonBase('\\' . __CLASS__, $lockFilePath, $timeout, $expire, $sleepMicroSeconds);
    }

    /**
     * Construct the lock system.
     *
     * @param string $fullLockFilePath  Full lock flag file path.
     *                                  //This file must have reading permission.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $flagFileExpire    Seconds number which flag-file expires.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    //protected function __construct($lockFilePath, $timeout = 60, $flagFileExpire = 300, $sleepMicroSeconds = 100000)
    protected function __construct($fullLockFilePath, $timeout, $flagFileExpire, $sleepMicroSeconds)
    {
        //parent::__construct($lockFilePath, $timeout, $sleepMicroSeconds);
        parent::__construct($fullLockFilePath, $timeout, $sleepMicroSeconds);

        // The file does not exist.
        //if (!is_file($this->fullLockFilePath)) {
        if (!is_file($fullLockFilePath)) {
            return;
        }
        //$stat = stat($this->fullLockFilePath);
        $stat = stat($fullLockFilePath);
        // Locking flag file is too old.
        if (time() - $stat['mtime'] > $flagFileExpire) {
            restore_error_handler();
            // Delete locking flag file.
            //@unlink($this->fullLockFilePath);
            @unlink($fullLockFilePath);
            set_error_handler('BreakpointDebugging::errorHandler', -1);
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
        while (($this->pFile = @fopen($this->fullLockFilePath, 'x+b')) === false) {
            if (time() - $startTime > $this->timeout) {
                B::internalException('This process has been timeouted.');
                // We do not delete locking flag file here because we cannot lock php code.
                break;
            }
            // Wait micro seconds.
            usleep($this->sleepMicroSeconds);
        }
        chmod($this->fullLockFilePath, 0600);
    }

    /**
     * Unlocking loop.
     *
     * @return void
     */
    protected function unlockingLoop()
    {
        fclose($this->pFile);
        while (@unlink($this->fullLockFilePath) === false) {
            // Wait micro seconds.
            usleep($this->sleepMicroSeconds);
        }
    }

}

?>
