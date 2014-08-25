<?php

/**
 * The abstract base class which lock php-code.
 *
 * A synchronous object must be singleton because dead-lock occurs.
 * And, if you use derived class of this class, don't do other synchronous-process ( flock() and so on ) because dead-lock occurs.
 *
 * Example of dead-lock.
 *      Process A locks file A, and process B locks file B.
 *      Then, process A is waiting for file B, and process B is waiting for file A.
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
 * The abstract base class which lock php-code.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
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
    protected $lockCount;

    /**
     * @var string Lock-flag-file path.
     */
    protected $lockFilePath;

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
     * @var type
     */
    private static $_currentClassName = null;

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
        B::assert(is_string($className));
        B::assert(is_string($lockFilePath));
        B::assert(is_int($timeout) && 0 <= $timeout);
        B::assert(is_int($expire) && 0 <= $expire);
        B::assert(is_int($sleepMicroSeconds) && 0 <= $sleepMicroSeconds);
        B::assert(is_bool($isInternal));

        if ($isInternal) {
            // This code is executed in case of "\BreakpointDebugging_LockByFileExisting" unit test.
            if (self::$_internalInstance === null) {
                self::$_internalInstance = new $className($lockFilePath, $timeout, $expire, $sleepMicroSeconds);
            }
            return self::$_internalInstance;
        } else {
            // Synchronous class has to be any one of derived classes because dead-lock occurs.
            B::assert(self::$_currentClassName === null || self::$_currentClassName === $className, 101);
            self::$_currentClassName = $className;
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
        // This code is executed in case of debug unit test because assertion test is executed in case of debug mode.
        B::assert(false, 101);
        // @codeCoverageIgnoreStart
        // Because "B::assert()" throws exception.
    }

    // @codeCoverageIgnoreEnd
    /**
     * Constructs the lock system.
     *
     * @param string $lockFilePath      Lock-flag-file path.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    protected function __construct($lockFilePath, $timeout, $sleepMicroSeconds)
    {
        B::assert(func_num_args() === 3);
        B::assert(is_string($lockFilePath));
        B::assert(is_int($timeout));
        B::assert(is_int($sleepMicroSeconds));

        // Extend maximum execution time.
        set_time_limit($timeout + 10);
        $this->lockCount = 0;
        $this->timeout = $timeout;
        $this->sleepMicroSeconds = $sleepMicroSeconds;
        $this->lockFilePath = $lockFilePath;
    }

    /**
     * Destructs the lock system.
     */
    function __destruct()
    {
        $lockCount = $this->lockCount;
        // Unlocks all. We must unlock before "B::assert" because if we execute unit test, it throws exception.
        while ($this->lockCount > 0) {
            // This code is executed in case of debug unit test because assertion test is executed in case of debug mode.
            $this->unlock();
        }
        B::assert($lockCount <= 0, 101);
    }

    /**
     * Loops locking.
     *
     * @return void
     */
    abstract protected function loopLocking();
    /**
     * Lock php-code.
     * Permit going through the only process or thread, then other processes or threads have been waited.
     *
     * @return void
     */
    function lock()
    {
        B::assert(func_num_args() === 0);

        if ($this->lockCount > 0) {
            $this->lockCount++;
            return;
        }
        // Extend maximum execution time.
        set_time_limit($this->timeout + 10);

        set_error_handler('\BreakpointDebugging::handleError', 0);
        $this->loopLocking();
        restore_error_handler();

        B::assert($this->lockCount === 0, 101);
        $this->lockCount++;
    }

    /**
     * Loops unlocking.
     *
     * @return void
     */
    abstract protected function loopUnlocking();
    /**
     * Unlock php-code.
     * This permits going through other processes or threads.
     *
     * @return void
     */
    function unlock()
    {
        B::assert(func_num_args() === 0);

        if ($this->lockCount > 1) {
            $this->lockCount--;
            return;
        }
        $this->lockCount--;
        B::assert($this->lockCount === 0, 101);

        set_error_handler('\BreakpointDebugging::handleError', 0);
        $this->loopUnlocking();
        restore_error_handler();
    }

    /**
     * Forces unlocking to avoid lock-count assertion error if forces a exit.
     *
     * @return void
     */
    static function forceUnlocking()
    {
        B::assert(func_num_args() === 0);

        B::limitAccess(
            array (
                'BreakpointDebugging/Error.php',
                'BreakpointDebugging/Error_InDebug.php'
            )
        );

        if (is_object(self::$_internalInstance)) {
            // This code is executed in case of "\BreakpointDebugging_LockByFileExisting" unit test.
            while (self::$_internalInstance->lockCount > 0) {
                self::$_internalInstance->unlock();
            }
        }
        if (is_object(self::$_instance)) {
            while (self::$_instance->lockCount > 0) {
                self::$_instance->unlock();
            }
        }
    }

}

?>
