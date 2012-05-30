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
require_once './BreakpointDebugging_MySetting.php';

use BreakpointDebugging as B;

/**
 * Class which lock by file existing.
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
     * @var int The lock count.
     */
    private $_lockCount = 0;

    /**
     * @var string The lock flag file path.
     */
    protected $_lockingFlagFilePath;

    /**
     * @var resource File pointer of lock flag file.
     */
    protected $_pFile;

    /**
     * @var int Seconds of timeout.
     */
    private $_timeout;

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath The file path for lock.
     *                              This file must have reading permission.
     * @param int    $timeout      Seconds of timeout.
     */
    function __construct($lockFilePath, $timeout = 60) // $timeout = 5)
    {
        $this->_timeout = $timeout;
        $lockFlagDir = realpath(B::$workDir) . '/Flag'; // Flag directory must exist.
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $lockFlagDir = strtolower($lockFlagDir);
            $lockFilePath = strtolower($lockFilePath);
        }
        $lockFlagDir = str_replace('\\', '/', $lockFlagDir);
        assert(is_dir($lockFlagDir));
        $path = realpath($lockFilePath);
        if ($path === false) {
            B::internalException("Param1 file must have reading permission. ($lockFilePath)");
        }
        $path = str_replace('\\', '/', $path);
        $lockingFlagFilePath = $lockFlagDir . '/' . substr($path, strpos($path, '/') + 1);
        $this->_lockingFlagFilePath = $lockingFlagFilePath;
        if (strlen($this->_lockingFlagFilePath) > PHP_MAXPATHLEN) {
            B::internalException('Param1 is too long because result which merged flag file exceeded PHP_MAXPATHLEN. ' . $this->_lockingFlagFilePath . ' PHP_MAXPATHLEN = ' . PHP_MAXPATHLEN);
        }
        clearstatcache();
        if (!file_exists(dirname($this->_lockingFlagFilePath))) {
            restore_error_handler();
            // Make directory of the lock-flag file for initialize.
            @mkdir(dirname($this->_lockingFlagFilePath), 0600, true);
            set_error_handler('BreakpointDebugging::errorHandler', -1);
        }
        // The file does not exist.
        if (!is_file($this->_lockingFlagFilePath)) {
            return;
        }
        $stat = stat($this->_lockingFlagFilePath);
        // Locking flag file is too old.
        if (time() - $stat['mtime'] > 300) { // 300) {
            restore_error_handler();
            // Delete locking flag file.
            @unlink($this->_lockingFlagFilePath);
            set_error_handler('BreakpointDebugging::errorHandler', -1);
        }
    }

    function __destruct()
    {
        assert($this->_lockCount === 0);
    }

    /**
     * Locking condition.
     *
     * @return bool "false" in case of not being the condition.
     */
    abstract protected function _lockingCondition();

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
        set_time_limit($this->_timeout + 10);
        $startTime = time();
        restore_error_handler();
        while ($this->_lockingCondition() === false) {
            if (time() - $startTime > $this->_timeout) {
                B::internalException('This process has been timeouted.');
            }
            // Wait 0.01 second.
            usleep(10000);
        }
        set_error_handler('BreakpointDebugging::errorHandler', -1);

        assert($this->_lockCount === 0);
        $this->_lockCount++;
    }

    /**
     * Unlocking condition.
     *
     * @return bool "false" in case of not being the condition.
     */
    abstract protected function _unlockingCondition();

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
        assert($this->_lockCount === 0);

        fclose($this->_pFile);
        restore_error_handler();
        while ($this->_unlockingCondition() === false) {
            // Wait 0.01 second.
            usleep(10000);
        }
        set_error_handler('BreakpointDebugging::errorHandler', -1);
    }

}

?>
