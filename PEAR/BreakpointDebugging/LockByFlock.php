<?php

/**
 * Class which locks php-code by "flock()".
 *
 * This class has to be environment which can use "flock()".
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
 * Class which locks php-code by "flock()".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_LockByFlock extends \BreakpointDebugging_Lock
{
    /**
     * Singleton method.
     *
     * @param int    $timeout            The timeout.
     * @param int    $sleepMicroSeconds  Micro seconds to sleep.
     *
     * @return object Instance of this class.
     */
    static function &singleton($timeout = 60, $sleepMicroSeconds = 100000)
    {
        return parent::singletonBase('\\' . __CLASS__, B::$workDir . '/LockByFlock.txt', $timeout, 0, $sleepMicroSeconds);
        //$object = &parent::singletonBase('\\' . __CLASS__, B::$workDir . '/LockByFlock.txt', $timeout, 0, $sleepMicroSeconds);
        //return $object;
    }

    /**
     * Construct the lock system.
     *
     * @param string $lockFilePath      Lock-flag-file path.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $dummy             Dummy.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     */
    protected function __construct($lockFilePath, $timeout, $dummy, $sleepMicroSeconds)
    {
        parent::__construct($lockFilePath, $timeout, $sleepMicroSeconds);

        $this->pFile = fopen($lockFilePath, 'a+b');
        chmod($lockFilePath, 0600);
        B::internalAssert(stream_supports_lock($this->pFile));
    }

//    function __destruct()
//    {
//        fclose($this->pFile);
//        parent::__destruct();
//    }
    /**
     * Locking loop.
     *
     * @return void
     */
    protected function lockingLoop()
    {
        flock($this->pFile, LOCK_EX);
    }

    /**
     * Unlocking loop.
     *
     * @return void
     */
    protected function unlockingLoop()
    {
        flock($this->pFile, LOCK_UN);
    }

}

?>
