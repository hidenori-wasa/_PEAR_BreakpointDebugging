<?php

/**
 * Class which locks php-code by file existing.
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
 * Class which locks php-code by file existing.
 *
 * PHP version 5.3.2-5.4.x
 *
 * This class is required for environment where "flock()" doesn't exist.
 * We can synchronize applications by setting the same directory to "define('BREAKPOINTDEBUGGING_WORK_DIR_NAME', './<work directory>/');" of "./BreakpointDebugging_Inclusion.php".
 *
 * <pre>
 * Example of usage.
 *
 * <code>
 *      $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(); // Creates a lock instance.
 *      $lockByFileExisting->lock(); // Locks php-code.
 *      try {
 *          $pFile = \BreakpointDebugging::fopen(array ('file.txt', 'w+b')); // Truncates data.
 *          $data = fread($pFile, 1); // Reads data.
 *          $data++; // Changes data.
 *          fwrite($pFile, $data); // Writes data.
 *          fclose($pFile); // Flushes data, and releases file pointer resource.
 *      } catch (\Exception $e) {
 *          $lockByFileExisting->unlock(); // Unlocks php-code.
 *          throw $e;
 *      }
 *      $lockByFileExisting->unlock(); // Unlocks php-code.
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
    static function &singleton($timeout = 60, $expire = 300, $sleepMicroSeconds = 1000000)
    {
        return parent::singletonBase('\\' . __CLASS__, BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'LockByFileExisting.txt', $timeout, $expire, $sleepMicroSeconds);
    }

    /**
     * Singleton method of internal.
     *
     * @return object Instance of this class.
     */
    static function &internalSingleton()
    {
        return parent::singletonBase('\\' . __CLASS__, BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'LockByFileExistingOfInternal.txt', 60, 300, 1000000, true);
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
        // @codeCoverageIgnoreStart
        // Because the following isn't executed in case of single process.
        $stat = stat($lockFilePath);
        // Locking flag file is too old.
        if (time() - $stat['mtime'] > $flagFileExpire) {
            // Delete locking flag file.
            B::unlink(array ($lockFilePath), $timeout, $sleepMicroSeconds);
        }
    }

    // @codeCoverageIgnoreEnd
    /**
     * Loops locking.
     *
     * @return void
     */
    protected function loopLocking()
    {
        $pFile = B::fopen(array ($this->lockFilePath, 'x+b'), 0600, $this->timeout, $this->sleepMicroSeconds);
        // Closes the file because "popen()" copies file resource. Therefore, the file can do "unlink()".
        fclose($pFile);
    }

    /**
     * Loops unlocking.
     *
     * @return void
     */
    protected function loopUnlocking()
    {
        if (!is_file($this->lockFilePath)) {
            // @codeCoverageIgnoreStart
            // This code may not be executed but it remains for safety.
            return;
            // @codeCoverageIgnoreEnd
        }
        B::unlink(array ($this->lockFilePath), $this->timeout, $this->sleepMicroSeconds);
    }

}
