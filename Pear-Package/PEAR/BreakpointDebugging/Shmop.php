<?php

/**
 * Shared memory operation utility class.
 *
 * This class requires "shmop" extension.
 *
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2014, Hidenori Wasa
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
 * Class which locks php-code by shared memory operation.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_Shmop
{
    /**
     * Builds shared memory.
     *
     * @param int $sharedMemoryBlockSize Shared memory block size.
     *
     * @return array Shared memory key and shared memory ID.
     * @throws \BreakpointDebugging_ErrorException
     */
    static function buildSharedMemory($sharedMemoryBlockSize)
    {
        B::limitAccess(
            array ('BreakpointDebugging/LockByShmop.php',
                'BreakpointDebugging/Window.php',
            )
        );

        set_error_handler('\BreakpointDebugging::handleError', 0);
        for ($count = 0; $count < 1000; $count++) {
            $sharedMemoryKey = (microtime(true) * 10000) & 0xFFFFFFFF;
            if ($sharedMemoryKey === -1) {
                // @codeCoverageIgnoreStart
                // Because this is a few probability.
                continue;
                // @codeCoverageIgnoreEnd
            }
            // Allocates shared memory area.
            $sharedMemoryID = @shmop_open($sharedMemoryKey, 'n', 0600, $sharedMemoryBlockSize);
            if ($sharedMemoryID === false //
                || $sharedMemoryID === null //
            ) {
                // @codeCoverageIgnoreStart
                // Because this is a few probability.
                continue;
                // @codeCoverageIgnoreEnd
            }
            break;
        }
        restore_error_handler();

        if ($sharedMemoryID === false) {
            // @codeCoverageIgnoreStart
            // Because this is a few probability.
            throw new \BreakpointDebugging_ErrorException('New shared memory operation opening failed.', 101);
            // @codeCoverageIgnoreEnd
        }

        return array ($sharedMemoryKey, $sharedMemoryID);
    }

    /**
     * Get shared memory ID.
     *
     * @param resource $pFile Shared memory key file.
     *
     * @return mixed Shared memory ID or false.
     */
    static function getSharedMemoryID($pFile)
    {
        set_error_handler('\BreakpointDebugging::handleError', 0);
        $sharedMemoryKey = fread($pFile, 10);
        B::assert(!empty($sharedMemoryKey));
        // Open shared memory to read and write.
        $sharedMemoryID = @shmop_open($sharedMemoryKey, 'w', 0, 0);
        restore_error_handler();

        if (empty($sharedMemoryID)) {
            return false;
        }
        return $sharedMemoryID;
    }

}
