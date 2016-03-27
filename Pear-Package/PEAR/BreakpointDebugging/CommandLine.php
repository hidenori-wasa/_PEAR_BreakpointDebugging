<?php

/**
 * Command line interface utility class.
 *
 * LICENSE:
 * Copyright (c) 2015-, Hidenori Wasa
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
class BreakpointDebugging_CommandLine
{

    /**
     * Opens command line process pipe.
     *
     * @param string $fullFilePath Full file path to open a pipe as page.
     * @param array  $queryString  A query character string.
     *
     * @return resource Opened process pipe.
     * @throws \BreakpointDebugging_ErrorException
     */
    static function popen($fullFilePath, $queryString)
    {
        // Creates and runs a test process.
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // For Windows.
            // include_once $fullFilePath; // For debug.
            $pPipe = popen('php.exe -f ' . $fullFilePath . ' -- ' . $queryString, 'r');
            if ($pPipe === false) {
                throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
            }
        } else { // For Unix.
            // include_once $fullFilePath; // For debug.
            // "&" is the background execution of command.
            $pPipe = popen('php -f ' . $fullFilePath . ' -- ' . $queryString . ' &', 'r');
            if ($pPipe === false) {
                throw new \BreakpointDebugging_ErrorException('Failed to "popen()".');
            }
            // Executes command to asynchronization.
            if (!stream_set_blocking($pPipe, 0)) {
                throw new \BreakpointDebugging_ErrorException('Failed to "stream_set_blocking($pPipe, 0)".');
            }
        }
        return $pPipe;
    }

    /**
     * Waits for multiple processes, then returns its results.
     *
     * @param array $pPipes The pipes of multiple processes which opened by "popen()".
     *
     * @return array Results of multiple processes.
     * @throws \BreakpointDebugging_ErrorException
     */
    static function waitForMultipleProcesses($pPipes)
    {
        $results = array ();
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            foreach ($pPipes as $pPipe) {
                $results[] = stream_get_contents($pPipe);
            }
        } else {
            foreach ($pPipes as $pPipe) {
                // Waits until command execution end.
                if (!stream_set_blocking($pPipe, 1)) {
                    throw new \BreakpointDebugging_ErrorException('Failed to "stream_set_blocking($pPipe, 1)".');
                }
                // Gets command's stdout.
                $results[] = stream_get_contents($pPipe);
            }
        }
        return $results;
    }

}
