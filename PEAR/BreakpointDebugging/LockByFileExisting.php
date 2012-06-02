<?php

/**
 * Class which lock php-code by file existing.
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
 * Class which lock php-code by file existing.
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
     * Construct the lock system.
     *
     * @param string $lockFilePath      The file path for lock.
     *                                  This file must have reading permission.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $flagFileExpire    Seconds number which flag-file expires.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    function __construct($lockFilePath, $timeout = 60, $flagFileExpire = 300, $sleepMicroSeconds = 100000)
    {
        parent::__construct($lockFilePath, $timeout, $sleepMicroSeconds);

        // The file does not exist.
        if (!is_file($this->lockingFlagFilePath)) {
            return;
        }
        $stat = stat($this->lockingFlagFilePath);
        // Locking flag file is too old.
        if (time() - $stat['mtime'] > $flagFileExpire) {
            restore_error_handler();
            // Delete locking flag file.
            @unlink($this->lockingFlagFilePath);
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
        while (($this->pFile = @fopen($this->lockingFlagFilePath, 'x+b')) === false) {
            if (time() - $startTime > $this->timeout) {
                BreakpointDebugging::internalException('This process has been timeouted.');
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
        while (@unlink($this->lockingFlagFilePath) === false) {
            // Wait micro seconds.
            usleep($this->sleepMicroSeconds);
        }
    }

}

?>
