<?php

/**
 * The abstract base class which lock php-code.
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
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use BreakpointDebugging as B;

/**
 * Class which locks php-code.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class BreakpointDebugging_Lock
{
    /**
     * @var object Maintains a instance.
     */
    private static $_instance;

    /**
     * @var int The lock count.
     */
    private $_lockCount = 0;

    /**
     * @var string Full lock flag file path.
     */
    protected $fullLockFilePath;

    /**
     * @var resource File pointer of lock flag file.
     */
    protected $pFile;

    /**
     * @var int Seconds number of timeout.
     */
    protected $timeout;

    /**
     * @var int Micro seconds to sleep.
     */
    protected $sleepMicroSeconds;

    /**
     * Get full lock file path.
     *
     * @param string $lockFilePath      The file path for lock.
     *                                  This file must have reading permission.
     *
     * @return string Full lock file path.
     */
    //protected function getFullLockFilePath($lockFilePath)
    private static function getFullLockFilePath($lockFilePath)
    {
        $lockFlagDir = B::$workDir . '/Flag';
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $lockFlagDir = strtolower($lockFlagDir);
            $lockFilePath = strtolower($lockFilePath);
        }
        $lockFlagDir = str_replace('\\', '/', $lockFlagDir);
        if (!is_dir($lockFlagDir)) {
            mkdir($lockFlagDir, 0700);
        }
        $path = realpath($lockFilePath);
        if ($path === false) {
            B::internalException("Param1 file must have reading permission. ($lockFilePath)");
        }
        $path = str_replace('\\', '/', $path);
        $fullLockFilePath = $lockFlagDir . '/' . substr($path, strpos($path, '/') + 1);
        if (strlen($fullLockFilePath) > PHP_MAXPATHLEN) {
            B::internalException('Param1 is too long because result which merged flag file exceeded PHP_MAXPATHLEN. ' . $fullLockFilePath . ' PHP_MAXPATHLEN = ' . PHP_MAXPATHLEN);
        }
        clearstatcache(true, $fullLockFilePath);
        if (!file_exists(dirname($fullLockFilePath))) {
            restore_error_handler();
            // Make directory of the lock-flag file for initialize.
            @mkdir(dirname($fullLockFilePath), 0700, true);
            set_error_handler('BreakpointDebugging::errorHandler', -1);
        }
        return $fullLockFilePath;
    }

    /**
     * A synchronous object must be singleton because dead-lock occurs.
     * Example:Process A locks file A, and process B locks file B.
     *         Then, process A is waiting for file B, and process B is waiting for file A.
     *
     * @param string $className          Object class name.
     * @param string $lockFilePath       The file path which wants the lock.
     *                                   This file must have reading permission.
     * @param int    $timeout            The timeout.
     * @param int    $expire             The number of seconds which lock-flag-file expires.
     * @param int    $sleepMicroSeconds  Micro seconds to sleep.
     *
     * @return object Instance of this class.
     */
    protected static function singletonBase($className, $lockFilePath, $timeout, $expire, $sleepMicroSeconds)
    {
//        //static $storeLockFilePath = null;
//        static $storeFullLockFilePath = null;
//
//        //$fullLockFilePath = getFullLockFilePath($lockFilePath);
//        $fullLockFilePath = self::getFullLockFilePath($lockFilePath);
//        //if ($lockFilePath !== $storeLockFilePath) {
//        while (true) {
//            if ($fullLockFilePath !== $storeFullLockFilePath) {
//                $callStack = debug_backtrace();
//                foreach ($callStack as $call) {
//                    // In case of inside of error handler or exception handler.
//                    if (array_key_exists('class', $call) && $call['class'] === 'BreakpointDebugging_Error') {
//                        break 2;
//                    }
//                }
//                //assert($storeLockFilePath === null);
//                B::internalAssert($storeFullLockFilePath === null);
//                //$storeLockFilePath = $lockFilePath;
//                $storeFullLockFilePath = $fullLockFilePath;
//            }
//            break;
//        }
        if (!isset(self::$_instance)) {
            //$c = '\\' . __CLASS__;
            //self::$_instance = new $c($fullLockFilePath, $timeout, $expire, $sleepMicroSeconds);
            //self::$_instance = new $className($fullLockFilePath, $timeout, $expire, $sleepMicroSeconds);
            self::$_instance = new $className(self::getFullLockFilePath($lockFilePath), $timeout, $expire, $sleepMicroSeconds);
        }
        return self::$_instance;
    }

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath      Full lock flag file path.
     *                                  //This file must have reading permission.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    //protected function __construct($lockFilePath, $timeout, $sleepMicroSeconds)
    protected function __construct($fullLockFilePath, $timeout, $sleepMicroSeconds)
    {
        global $_BreakpointDebugging;

        // Extend maximum execution time.
        set_time_limit($timeout + 10);
        $this->timeout = $timeout;
        $this->sleepMicroSeconds = $sleepMicroSeconds;

//        $lockFlagDir = B::$workDir . '/Flag';
//        if (substr(PHP_OS, 0, 3) === 'WIN') {
//            $lockFlagDir = strtolower($lockFlagDir);
//            $lockFilePath = strtolower($lockFilePath);
//        }
//        $lockFlagDir = str_replace('\\', '/', $lockFlagDir);
//        if (!is_dir($lockFlagDir)) {
//            mkdir($lockFlagDir, 0700);
//        }
//        $path = realpath($lockFilePath);
//        if ($path === false) {
//            B::internalException("Param1 file must have reading permission. ($lockFilePath)");
//        }
//        $path = str_replace('\\', '/', $path);
//        $lockingFlagFilePath = $lockFlagDir . '/' . substr($path, strpos($path, '/') + 1);
//        $this->fullLockFilePath = $lockingFlagFilePath;
//        if (strlen($this->fullLockFilePath) > PHP_MAXPATHLEN) {
//            B::internalException('Param1 is too long because result which merged flag file exceeded PHP_MAXPATHLEN. ' . $this->fullLockFilePath . ' PHP_MAXPATHLEN = ' . PHP_MAXPATHLEN);
//        }
//        clearstatcache(true, $this->fullLockFilePath);
//        if (!file_exists(dirname($this->fullLockFilePath))) {
//            restore_error_handler();
//            // Make directory of the lock-flag file for initialize.
//            @mkdir(dirname($this->fullLockFilePath), 0700, true);
//            set_error_handler('BreakpointDebugging::errorHandler', -1);
//        }
        $this->fullLockFilePath = $fullLockFilePath;
//        // Register this object.
//        $_BreakpointDebugging->lockByFileExistingObjects[] = $this;
    }

    function __destruct()
    {
//        global $_BreakpointDebugging;
//
//        // Search this object.
//        foreach ($_BreakpointDebugging->lockByFileExistingObjects as &$lockByFileExistingObject) {
//            if ($lockByFileExistingObject === $this) {
//                // Unregister this object.
//                unset($lockByFileExistingObject);
//                if (B::$onceErrorDispFlag) {
//                    return;
//                }
//                B::internalAssert($this->_lockCount === 0);
//                return;
//            }
//        }
//        B::internalAssert(false);
        if (B::$onceErrorDispFlag) {
            return;
        }
        B::internalAssert($this->_lockCount === 0);
    }

    /**
     * Locking loop.
     *
     * @return void
     */
    abstract protected function lockingLoop();
    /**
     * Lock php-code.
     * Permit going through the only process or thread, then other processes or threads have been waited.
     *
     * @return void
     */
    function lock()
    {
        if ($this->_lockCount > 0) {
            $this->_lockCount++;
            return;
        }
        // Extend maximum execution time.
        set_time_limit($this->timeout + 10);
        restore_error_handler();
        $this->lockingLoop();
        set_error_handler('BreakpointDebugging::errorHandler', -1);

        B::internalAssert($this->_lockCount === 0);
        $this->_lockCount++;
    }

    /**
     * Unlocking loop.
     *
     * @return void
     */
    abstract protected function unlockingLoop();
    /**
     * Unlock php-code.
     * This permits going through other processes or threads.
     *
     * @return void
     */
    function unlock()
    {
        if ($this->_lockCount > 1) {
            $this->_lockCount--;
            return;
        }
        $this->_lockCount--;
        B::internalAssert($this->_lockCount === 0);

        restore_error_handler();
        $this->unlockingLoop();
        set_error_handler('BreakpointDebugging::errorHandler', -1);
    }

}

?>
