<?php

/**
 * Class for display to other process windows.
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
use \BreakpointDebugging_Window as BW;

/**
 * Class for display to other process windows.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_Window
{
    /**
     * @var \BreakpointDebugging_Window This instance.
     */
    private static $self;

    /**
     * @var array Once JavaScript flag.
     */
    private static $_onceJavaScript = array ();

    /**
     * @var int Shared memory operation ID or file pointer.
     */
    private static $_resourceID;

    /**
     * @var bool Is "shmop" extention valid?
     */
    private static $_isShmopValid;

    function __destruct()
    {
        self::_deleteOtherProcessForDisplay();
    }

    /**
     * Lock on 2 processes.
     *
     * @param int $lockFlagLocationOfItself  The lock flag location of itself.
     * @param int $lockFlagLocationOfpartner The lock flag location of partner.
     * @param int $shmopId                   Shared memory ID.
     *
     * @return bool Failure returns false.
     */
    static function lockOn2Processes($lockFlagLocationOfItself, $lockFlagLocationOfpartner, $shmopId)
    {
        B::limitAccess(
            array ('BreakpointDebugging_DisplayToOtherProcess.php',
                'BreakpointDebugging/Window.php',
            )
        );

        $startTime = time();
        while (true) {
            if (shmop_write($shmopId, '1', $lockFlagLocationOfItself) === false) {
                return false;
            }
            $result = shmop_read($shmopId, $lockFlagLocationOfpartner, 1);
            if ($result === false) {
                return false;
            }
            if ($result === '1') {
                // @codeCoverageIgnoreStart
                // Because the following isn't executed in case of single process.
                if (shmop_write($shmopId, '0', $lockFlagLocationOfItself) === false) {
                    return false;
                }
                // In case of timeout by 5 minute.
                if (time() - $startTime > 300) {
                    throw new \BreakpointDebugging_ErrorException('This process has been timeouted.', 101);
                }
                // Wait 0.1 seconds.
                usleep(100000);
                continue;
                // @codeCoverageIgnoreEnd
            }
            break;
        }
        return true;
    }

    /**
     * Unlock on 2 processes.
     *
     * @param int $lockFlagLocationOfItself The lock flag location of itself.
     * @param int $shmopId                  Shared memory ID.
     *
     * @return bool Failure returns false.
     */
    static function unlockOn2Processes($lockFlagLocationOfItself, $shmopId)
    {
        B::limitAccess(
            array ('BreakpointDebugging_DisplayToOtherProcess.php',
                'BreakpointDebugging/Window.php',
            )
        );

        return shmop_write($shmopId, '0', $lockFlagLocationOfItself);
    }

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
     * Executes JavaScript.
     *
     * @param string $javaScript JavaScript to execute.
     *
     * @return void
     */
    static function executeJavaScript($javaScript)
    {
        B::limitAccess(
            array ('BreakpointDebugging_DisplayToOtherProcess.php',
                'BreakpointDebugging/Window.php',
            )
        );

        echo '<script>' . PHP_EOL
        . '<!--' . PHP_EOL
        . $javaScript . PHP_EOL
        . '//-->' . PHP_EOL
        . '</script>' . PHP_EOL;
        flush();
    }

    /**
     * Dispatches the shared resource of JavaScript to other process for display.
     *
     * @param type $javaScript
     *
     * @return void
     */
    private static function _dispatchJavaScript($javaScript)
    {
        $javaScriptDispatcher = '<script>' . PHP_EOL
            . '<!--' . PHP_EOL
            . $javaScript . PHP_EOL
            . '//-->' . PHP_EOL
            . '</script>' . PHP_EOL;
        if (self::$_isShmopValid) {
            $result = self::lockOn2Processes(0, 1, self::$_resourceID);
            B::assert($result !== false);
            // Gets the writing pointer.
            $javaScriptWritingPtr = shmop_read(self::$_resourceID, 3, 10);
            B::assert($javaScriptWritingPtr !== false);
            // Writes shared resource of JavaScript character string.
            $result = shmop_write(self::$_resourceID, $javaScriptDispatcher, $javaScriptWritingPtr);
            B::assert($result !== false);
            $javaScriptWritingPtr += strlen($javaScriptDispatcher);
            $javaScriptWritingPtr = sprintf('0x%08X', $javaScriptWritingPtr);
            // Registers the writing pointer.
            $result = shmop_write(self::$_resourceID, $javaScriptWritingPtr, 3);
            B::assert($result !== false);
            self::unlockOn2Processes(0, self::$_resourceID);
        } else {
            // Writes to shared file.
            $result = fwrite(self::$_resourceID, $javaScriptDispatcher);
            B::assert($result !== false);
        }
    }

    /**
     * Gets URI from relative file name.
     *
     * @param string $relativeFilePath Relative file path.
     *
     * @return string URI.
     */
    static function getURIFromRelativeFilePath($relativeFilePath)
    {
        $fullFilePath = stream_resolve_include_path($relativeFilePath);
        $relativeFilePath = substr($fullFilePath, strlen($_SERVER['DOCUMENT_ROOT']) + 1);
        $relativeFilePath = str_replace('\\', '/', $relativeFilePath);
        return "//localhost/$relativeFilePath";
    }

    /**
     * Creates or initializes shared resource for other process display because session unit test must not send HTML header.
     *
     * @return void
     */
    static function initializeSharedResource()
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        self::$_isShmopValid = extension_loaded('shmop');

        if (self::$_isShmopValid) { // If "shmop" extention is valid.
            $shmopFullFilePath = B::getStatic('$_workDir') . '/OtherProcessDisplayByShmop.txt';
            while (true) {
                if (is_file($shmopFullFilePath)) {
                    $shmopKey = file_get_contents($shmopFullFilePath);
                    B::assert($shmopKey !== false);
                    set_error_handler('\BreakpointDebugging::handleError', 0);
                    // Opens shared memory.
                    self::$_resourceID = @shmop_open($shmopKey, 'w', 0, 0);
                    restore_error_handler();
                    // If valid shared memory.
                    if (!empty(self::$_resourceID)) {
                        // Creates this class instance for shared memory destruction.
                        self::$self = new \BreakpointDebugging_Window();
                        return;
                    }
                }
                // Allocates shared memory area.
                list($shmopKey, self::$_resourceID) = self::buildSharedMemory(1024 * 1024);
                // Registers the shared memory key.
                $result = file_put_contents($shmopFullFilePath, $shmopKey);
                B::assert($result !== false);
                // Initializes shared memory.
                $result = shmop_write(self::$_resourceID, sprintf('0000x%08X0x%08X', 23, 23), 0);
                B::assert($result !== false);
                // Generates "Mozilla Firefox" command to open other process page.
                $url = B::copyResourceToCWD('BreakpointDebugging_DisplayToOtherProcess.php', '');
                if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
                    $command = '"C:/Program Files/Mozilla Firefox/firefox.exe" "https:' . $url . '"';
                } else {
                    $command = '"firefox" "https:' . $url . '"';
                }
                if (!(B::getStatic('$exeMode') & B::REMOTE)) { // If local server.
                    // Opens "BreakpointDebugging_DisplayToOtherProcess.php" page for display in other process.
                    `$command`;
                } else { // If remote server.
                    $isOpenOtherProcessWindow = shmop_read(self::$_resourceID, 3, 1);
                    if ($isOpenOtherProcessWindow === '0') {
                        self::executeJavaScript("alert('You must execute following command to open other process window.')");
                        self::executeJavaScript("alert('$command')");
                    }
                }
                // Closes the shared memory area.
                shmop_close(self::$_resourceID);
            }
        } else { // If "shmop" extension is invalid.
            if (!(B::getStatic('$exeMode') & B::REMOTE)) { // If local server.
                self::exitForError('You must enable "shmop" extention.');
            } else { // If remote server.
                $javaScriptFullFilePath = B::getStatic('$_workDir') . '/OtherProcessDisplayByJavaScript.txt';
                while (true) {
                    while (true) {
                        if (is_file($javaScriptFullFilePath)) {
                            $fileStatus = stat($javaScriptFullFilePath);
                            B::assert($fileStatus !== false);
                            // If previous execution had been abnormal end.
                            if ($fileStatus['size'] !== 0) {
                                break;
                            }
                            // Opens shared file with destruction and writing only.
                            self::$_resourceID = B::fopen(array ($javaScriptFullFilePath, 'wb'));
                            B::assert(self::$_resourceID !== false);
                            // Checks for abnormal end.
                            $result = fwrite(self::$_resourceID, '<!-- -->');
                            B::assert($result !== false);
                            // Creates this class instance to unlink shared file.
                            self::$self = new \BreakpointDebugging_Window();
                            return;
                        }
                        break;
                    }
                    // Creates shared file.
                    $result = file_put_contents($javaScriptFullFilePath, '');
                    B::assert($result !== false);
                }
            }
        }
    }

    /**
     * Deletes other process window for display, and clears shared resource.
     *
     * @return void
     */
    private static function _deleteOtherProcessForDisplay()
    {
        B::assert(isset(self::$_resourceID));

        if (self::$_isShmopValid) { // If "shmop" extention is valid.
            // Closes the shared memory area.
            shmop_close(self::$_resourceID);
        } else { // If "shmop" extension has not been loaded.
            // Closes the shared file.
            $result = fclose(self::$_resourceID);
            B::assert($result !== false);
            $javaScriptFullFilePath = B::getStatic('$_workDir') . '/OtherProcessDisplayByJavaScript.txt';
            // Unlinks shared file.
            B::unlink(array ($javaScriptFullFilePath));
        }
        // Unsets resource ID for assertion.
        self::$_resourceID = null;
    }

    /**
     * Opens a initialized virtual Window.
     * CAUTION: This window cannot request including link to itself.
     *          This window permits only a display.
     *
     * @param string $windowName      Window name which opens.
     * @param string $htmlFileContent HTML file content to initialize.
     *
     * @return void
     */
    static function virtualOpen($windowName, $htmlFileContent)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        B::assert(isset(self::$_resourceID));

        $htmlFileContent = str_replace(array ('\\', '\'', "\r", "\n"), array ('\\\\', '\\\'', '\r', '\n'), $htmlFileContent);
        self::_dispatchJavaScript("BreakpointDebugging_windowVirtualOpen('$windowName', '$htmlFileContent');" . PHP_EOL);
    }

    /**
     * Closes out browser window.
     *
     * @param string $windowName
     *
     * @return void
     */
    static function close($windowName)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        B::assert(isset(self::$_resourceID));

        self::_dispatchJavaScript("open('', '$windowName').close();" . PHP_EOL);
    }

    /**
     * Moves window to front.
     *
     * @param string $windowName Opened window name.
     *
     * @return void
     */
    static function front($windowName)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        B::assert(isset(self::$_resourceID));

        self::_dispatchJavaScript("BreakpointDebugging_windowFront('$windowName');" . PHP_EOL);
    }

    /**
     * Writes HTML character string inside a tag of opened window.
     *
     * @param string $windowName Opened window name.
     * @param string $tagName    The tag name.
     * @param int    $tagNumber  The tag number from 0.
     * @param string $html       HTML character string which writes inside a tag of opened window.
     *
     * @return void
     */
    static function htmlAddition($windowName, $tagName, $tagNumber, $html)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        B::assert(isset(self::$_resourceID));

        $html = str_replace(array ('\\', '\'', "\r", "\n"), array ('\\\\', '\\\'', '\r', '\n'), $html);
        self::_dispatchJavaScript("open('', '$windowName').document.getElementsByTagName('$tagName')[$tagNumber].innerHTML += '$html';" . PHP_EOL);
    }

    /**
     * Scrolls window by distance.
     *
     * @param string $windowName Opened window name.
     * @param int    $dy         Y vector distance.
     * @param int    $step       Y vector distance step.
     *
     * @return void
     */
    static function scrollBy($windowName, $dy, $step = 5)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        B::assert(isset(self::$_resourceID));

        for ($count = $dy / $step; $count > 0; $count--) {
            self::_dispatchJavaScript("open('', '$windowName', '').scrollBy(0, $step);" . PHP_EOL);
        }
    }

    /**
     * Clears window's header script.
     *
     * @param int $start ?????
     *
     * @return void
     */
    static function scriptClearance($start = 0)
    {
        // return; // For debug.

        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        B::assert(isset(self::$_resourceID));

        $javaScript = '';

        if (!array_key_exists(__FUNCTION__, self::$_onceJavaScript)) {
            self::$_onceJavaScript[__FUNCTION__] = true;
            $javaScript .= 'function BreakpointDebugging_windowScriptClearance()' . PHP_EOL
                . '{' . PHP_EOL
                . '    var $head = document.getElementsByTagName("head")[0];' . PHP_EOL
                . '    var $scripts = document.getElementsByTagName("script");' . PHP_EOL
                . "    for(var \$count = \$scripts.length - 1; \$count >= $start; \$count--){" . PHP_EOL
                . '        $head.removeChild($scripts[$count]);' . PHP_EOL
                . '    }' . PHP_EOL
                . '}' . PHP_EOL;
        }

        $javaScript .= 'BreakpointDebugging_windowScriptClearance();' . PHP_EOL;
        self::_dispatchJavaScript($javaScript);
    }

    /**
     * Displays an error into error window, and exits.
     *
     * @param string $message Error message by "PHP_EOL".
     *
     * @return void
     */
    static function exitForError($message)
    {
        if (isset(self::$_resourceID)) {
            self::virtualOpen(B::ERROR_WINDOW_NAME, B::getErrorHtmlFileTemplate());
            self::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, $message);
            self::front(B::ERROR_WINDOW_NAME);
        } else {
            echo '<pre>' . $message . '</pre>';
        }
        if (function_exists('xdebug_break')) {
            xdebug_break();
        }
        exit;
    }

}
