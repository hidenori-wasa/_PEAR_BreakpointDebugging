<?php

/**
 * The abstract base class which lock php-code.
 *
 * LICENSE:
 * Copyright (c) 2012-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/docs/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
/**
 * The abstract base class which lock php-code.
 *
 * PHP version 5.3.2-5.4.x
 *
 * A synchronous object must be singleton because dead-lock occurs.
 * And, if you use derived class of this class, don't do other synchronous-process ( flock() and so on ) because dead-lock occurs.
 *
 * Example of dead-lock.
 *      Process A locks file A, and process B locks file B.
 *      Then, process A is waiting for file B, and process B is waiting for file A.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class BreakpointDebugging_Lock
{
    /**
     * Maintains a instance of internal.
     *
     * @var object
     */
    private static $_internalInstance = null;

    /**
     * Maintains a instance.
     *
     * @var object
     */
    private static $_instance = null;

    /**
     * The lock count.
     *
     * @var int
     */
    protected $lockCount;

    /**
     * Lock-flag-file path.
     *
     * @var string
     */
    protected $lockFilePath;

    /**
     * Seconds number of timeout.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Micro seconds to sleep.
     *
     * @var int
     */
    protected $sleepMicroSeconds;

    /**
     * Shared memory ID.
     *
     * @var int
     */
    protected static $sharedMemoryID;

    /**
     * Current object class name.
     *
     * @var string
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
        \BreakpointDebugging::assert(is_string($className));
        \BreakpointDebugging::assert(is_string($lockFilePath));
        \BreakpointDebugging::assert(is_int($timeout) && 0 <= $timeout);
        \BreakpointDebugging::assert(is_int($expire) && 0 <= $expire);
        \BreakpointDebugging::assert(is_int($sleepMicroSeconds) && 0 <= $sleepMicroSeconds);
        \BreakpointDebugging::assert(is_bool($isInternal));

        if ($isInternal) {
            // This code is executed in case of "\BreakpointDebugging_LockByFileExisting" unit test.
            if (self::$_internalInstance === null) {
                self::$_internalInstance = new $className($lockFilePath, $timeout, $expire, $sleepMicroSeconds);
            }
            return self::$_internalInstance;
        } else {
            // Synchronous class has to be any one of derived classes because dead-lock occurs.
            \BreakpointDebugging::assert(self::$_currentClassName === null || self::$_currentClassName === $className, 101);
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
        \BreakpointDebugging::assert(false, 101);
        // @codeCoverageIgnoreStart
        // Because "\BreakpointDebugging::assert()" throws exception.
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
        \BreakpointDebugging::assert(func_num_args() === 3);
        \BreakpointDebugging::assert(is_string($lockFilePath));
        \BreakpointDebugging::assert(is_int($timeout));
        \BreakpointDebugging::assert(is_int($sleepMicroSeconds));

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
        // Unlocks all. Unit test must be unlocked before "\BreakpointDebugging::assert" to throw exception.
        while ($this->lockCount > 0) {
            // This code is executed in case of debug unit test because assertion test is executed in case of debug mode.
            $this->unlock();
        }
        \BreakpointDebugging::assert($lockCount <= 0, 101);
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
        \BreakpointDebugging::assert(func_num_args() === 0);

        if ($this->lockCount > 0) {
            $this->lockCount++;
            return;
        }
        // Extend maximum execution time.
        set_time_limit($this->timeout + 10);

        set_error_handler('\BreakpointDebugging::handleError', 0);
        $this->loopLocking();
        restore_error_handler();

        \BreakpointDebugging::assert($this->lockCount === 0, 101);
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
        \BreakpointDebugging::assert(func_num_args() === 0);

        if ($this->lockCount > 1) {
            $this->lockCount--;
            return;
        }
        $this->lockCount--;
        \BreakpointDebugging::assert($this->lockCount === 0, 101);

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
        \BreakpointDebugging::assert(func_num_args() === 0);
        \BreakpointDebugging::limitAccess(array ('BreakpointDebugging.php', 'BreakpointDebugging/Error.php'));

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
