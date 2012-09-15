<?php

/**
 * The abstract base class which lock php-code.
 *
 * A synchronous object must be singleton because dead-lock occurs.
 * And, if you use derived class of this class, don't do other synchronous-process ( flock() and so on ) because dead-lock occurs.
 *
 * @example of dead-lock.
 *      Process A locks file A, and process B locks file B.
 *      Then, process A is waiting for file B, and process B is waiting for file A.
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

use \BreakpointDebugging as B;

/**
 * The abstract base class which lock php-code.
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
     * @var object Maintains a instance of internal.
     */
    private static $_internalInstance = null;

    /**
     * @var object Maintains a instance.
     */
    private static $_instance = null;

    /**
     * @var int The lock count.
     */
    private $_lockCount;

    /**
     * @var string Lock-flag-file path.
     */
    protected $lockFilePath;

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
     * @var int Shared memory ID.
     */
    protected static $sharedMemoryID;

    /**
     * Base of all synchronous singleton method.
     *
     * @param string $className         Object class name.
     * @param string $lockFilePath      The file path which wants the lock.
     *                                  This file must have reading permission.
     * @param int    $timeout           The timeout.
     * @param int    $expire            The number of seconds which lock-flag-file expires.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     * @param bool   $isInternal        Is it internal call?
     *
     * @return object Instance of this class.
     */
    protected static function &singletonBase($className, $lockFilePath, $timeout, $expire, $sleepMicroSeconds, $isInternal = false)
    {
        static $currentClassName = null;

        if ($isInternal) {
            if (self::$_internalInstance === null) {
                self::$_internalInstance = new $className($lockFilePath, $timeout, $expire, $sleepMicroSeconds);
            }
            return self::$_internalInstance;
        } else {
            // Synchronous class has to be any one of derived classes because dead-lock occurs.
            B::internalAssert($currentClassName === null || $currentClassName === $className);
            $currentClassName = $className;
            if (self::$_instance === null) {
                self::$_instance = new $className($lockFilePath, $timeout, $expire, $sleepMicroSeconds);
            }
            return self::$_instance;
        }
    }

    /**
     * Prevents duplicating an instance.
     *
     * @return void
     */
    function __clone()
    {
        $this->_throwErrorException('Clone is not allowed.');
    }

    /**
     * Constructs the lock system.
     *
     * @param string $lockFilePath      Lock-flag-file path.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    protected function __construct($lockFilePath, $timeout, $sleepMicroSeconds)
    {
        // Extend maximum execution time.
        set_time_limit($timeout + 10);
        $this->_lockCount = 0;
        $this->timeout = $timeout;
        $this->sleepMicroSeconds = $sleepMicroSeconds;
        $this->lockFilePath = $lockFilePath;
        $this->pFile = null;
    }

    /**
     * Destructs the lock system.
     */
    function __destruct()
    {
        $_lockCount = $this->_lockCount;
        // Unlocks all. We must unlock before "B::internalAssert" because if we execute unit test, it throws exception.
        while ($this->_lockCount > 0) {
            $this->unlock();
        }
        // In case of lock by shared memory operation.
        if ($this instanceof \BreakpointDebugging_LockByShmop) {
            // Closes the shared memory.
            shmop_close(self::$sharedMemoryID);
        }
        B::internalAssert($_lockCount <= 0);
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
        set_error_handler('\BreakpointDebugging::errorHandler', -1);

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
        set_error_handler('\BreakpointDebugging::errorHandler', -1);
    }

    /**
     * Forces unlocking to avoid lock-count assertion error if forces a exit.
     *
     * @return void
     */
    static function forceUnlocking()
    {
        if (is_object(self::$_internalInstance)) {
            while (self::$_internalInstance->_lockCount > 0) {
                self::$_internalInstance->unlock();
            }
        }
        if (is_object(self::$_instance)) {
            while (self::$_instance->_lockCount > 0) {
                self::$_instance->unlock();
            }
        }
    }

}

?>
