<?php

/**
 * This locks part code by lock flag directory.
 *
 * This lock class by directory is needed because the environment which doesn't have "flock()" exists.
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

/**
 * This locks part code by lock flag directory.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 *
 * @example $lockByMkdir1 = new \BreakpointDebugging_LockByMkdir('./SomethingDir/FileWhichWantsToLock.txt');
 *           // Something execution...
 *           $lockByMkdir2 = new \BreakpointDebugging_LockByMkdir('./SomethingDir/FileWhichWantsToLock2.txt');
 *           // Something execution...
 *           $lockByMkdir3 = new \BreakpointDebugging_LockByMkdir('./SomethingDir3/FileWhichWantsToLock.txt');
 *           // Something execution...
 *           $lockByMkdir2->lock();
 *           // Something execution...
 *           $lockByMkdir1->lock();
 *           // Something execution...
 *           $lockByMkdir1->unlock();
 *           // Something execution...
 *           $lockByMkdir3->lock();
 *           // Something execution...
 *           $lockByMkdir1->lock();
 *           // Something execution...
 *           $lockByMkdir1->unlock();
 *           // Something execution...
 *           $lockByMkdir2->unlock();
 *           // Something execution...
 *           $lockByMkdir1->lock();
 *           // Something execution...
 *           $lockByMkdir1->unlock();
 *           // Something execution...
 *           $lockByMkdir3->unlock();
 *           // Something execution...
 */
final class BreakpointDebugging_LockByMkdir
{

    /**
     * @var int The lock count.
     */
    private $_lockCount = 0;

    /**
     * @var int The timeout.
     */
    private $_timeout;

    /**
     * @var string The lock flag directory path.
     */
    private $_lockingFlagDirPath;

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath The file path which wants the lock.
     *                              This file must have reading permission.
     * @param int    $timeout      The timeout.
     */
    public function __construct($lockFilePath, $timeout = 60)
    {
        $className = substr(__CLASS__, strpos(__CLASS__, '_') + 1);
        $flagDir = __DIR__ . '/data/' . $className . '/Flag'; // Flag directory must exist.
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $flagDir = strtolower($flagDir);
            $lockFilePath = strtolower($lockFilePath);
        }
        $flagDir = str_replace('\\', '/', $flagDir);
        assert(is_dir($flagDir));
        $this->_timeout = $timeout;
        $path = realpath($lockFilePath);
        if ($path === false) {
            $this->_throwErrorException("Param1 file must have reading permission. ($lockFilePath)");
        }
        $path = str_replace('\\', '/', $path);
        $this->_lockingFlagDirPath = $flagDir . '/' . substr($path, strpos($path, '/') + 1);
        if (strlen($this->_lockingFlagDirPath) > PHP_MAXPATHLEN) {
            $this->_throwErrorException("Param1 is too long because result which merged flag directory exceeded PHP_MAXPATHLEN. ({$this->_lockingFlagDirPath}) PHP_MAXPATHLEN = " . PHP_MAXPATHLEN);
        }
        // Initialize flag directory for server freeze handling.
        clearstatcache();
        if (file_exists($this->_lockingFlagDirPath)) {
            // Delete the lock-flag directory for initialize.
            rmdir($this->_lockingFlagDirPath);
        }
        if (!file_exists(dirname($this->_lockingFlagDirPath))) {
            // Make directory of the lock-flag directory for initialize.
            mkdir(dirname($this->_lockingFlagDirPath), 0600, true);
        }
    }

    /**
     * Throw error exception.
     *
     * @param type $message Error message.
     *
     * @return void
     */
    private function _throwErrorException($message)
    {
        throw new BreakpointDebugging_Error_Exception($message);
    }

    /**
     * Permit going through the only process or thread, then other processes or threads have been waited.
     *
     * @return void
     */
    public function lock()
    {
        restore_error_handler();
        $start = microtime(true);
        // Make the lock-flag-directory.
        while (!@mkdir($this->_lockingFlagDirPath, 0000)) {
            // In case of timeout.
            if (microtime(true) - $start > $this->_timeout) {
                $this->_throwErrorException('The lock timeout.');
            }
            // Wait 0.1 second.
            usleep(100000);
        }
        set_error_handler('BreakpointDebugging::errorHandler', -1);

        assert($this->_lockCount === 0);
        $this->_lockCount++;
    }

    /**
     * This permits going through other processes or threads.
     *
     * @return void
     */
    public function unlock()
    {
        $this->_lockCount--;
        assert($this->_lockCount === 0);

        // Delete the lock flag file.
        rmdir($this->_lockingFlagDirPath);
    }

}

?>
