<?php

/**
 * Class which locks php-code by "flock()".
 *
 * LICENSE:
 * Copyright (c) 2012-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
use \BreakpointDebugging as B;

/**
 * Class which locks php-code by "flock()".
 *
 * PHP version 5.3.2-5.4.x
 *
 * This class has to be environment which can use "flock()".
 * We can synchronize applications by setting the same directory
 * to "$workDir = &B::refStatic('$_workDir'); $workDir = <work directory>;"
 * of "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'".
 *
 * <pre>
 * Example of usage.
 *
 * <code>
 *      $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(); // Creates a lock instance.
 *      $lockByFlock->lock(); // Locks php-code.
 *      try {
 *          $pFile = \BreakpointDebugging::fopen(array ('file.txt', 'w+b')); // Truncates data.
 *          $data = fread($pFile, 1); // Reads data.
 *          $data++; // Changes data.
 *          fwrite($pFile, $data); // Writes data.
 *          fclose($pFile); // Flushes data, and releases file pointer resource.
 *      } catch (\Exception $e) {
 *          $lockByFlock->unlock(); // Unlocks php-code.
 *          throw $e;
 *      }
 *      $lockByFlock->unlock(); // Unlocks php-code.
 * </code>
 *
 * </pre>
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_LockByFlock extends \BreakpointDebugging_Lock
{
    /**
     * File pointer of lock flag file.
     *
     * @var resource
     */
    private $_pFile;

    /**
     * Singleton method.
     *
     * @param int $timeout           The timeout.
     * @param int $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return object Instance of this class.
     */
    static function &singleton($timeout = 60, $sleepMicroSeconds = 100000)
    {
        return parent::singletonBase('\\' . __CLASS__, B::getStatic('$_workDir') . '/LockByFlock.txt', $timeout, 0, $sleepMicroSeconds);
    }

    /**
     * Constructs the lock system.
     *
     * @param string $lockFilePath      Lock-flag-file path.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $dummy             Dummy.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    protected function __construct($lockFilePath, $timeout, $dummy, $sleepMicroSeconds)
    {
        parent::__construct($lockFilePath, $timeout, $sleepMicroSeconds);

        $this->_pFile = B::fopen(array ($lockFilePath, 'ab'));
        \BreakpointDebugging::assert(stream_supports_lock($this->_pFile), 101);
    }

    /**
     * Destructs the lock system.
     */
    function __destruct()
    {
        parent::__destruct();

        if (is_resource($this->_pFile)) {
            fclose($this->_pFile);
        }
    }

    /**
     * Loops locking.
     *
     * @return void
     */
    protected function loopLocking()
    {
        flock($this->_pFile, LOCK_EX);
    }

    /**
     * Loops unlocking.
     *
     * @return void
     */
    protected function loopUnlocking()
    {
        \BreakpointDebugging::assert(is_resource($this->_pFile));
        flock($this->_pFile, LOCK_UN);
    }

}
