<?php

/**
 * Shared memory operation utility class.
 *
 * LICENSE:
 * Copyright (c) 2014-, Hidenori Wasa
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
 * Class which locks php-code by shared memory operation.
 *
 * This class requires "shmop" extension.
 *
 * PHP version 5.3.2-5.4.x
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
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
                'BreakpointDebugging_LockByShmopResponse.php',
                'BreakpointDebugging/Window.php',
                'index.php', // For debug.
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
        if (strlen($sharedMemoryKey) !== 10) {
            return false;
        }
        // Open shared memory to read and write.
        $sharedMemoryID = @shmop_open($sharedMemoryKey, 'w', 0, 0);
        restore_error_handler();

        if (empty($sharedMemoryID)) {
            return false;
        }
        return $sharedMemoryID;
    }

}
