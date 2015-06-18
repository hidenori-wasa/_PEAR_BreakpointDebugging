<?php

/**
 * Class for display to other process windows.
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
use \BreakpointDebugging_Shmop as BS;
use \BreakpointDebugging_Window as BW;

/**
 * Class for display to other process windows.
 *
 * PHP version 5.3.2-5.4.x
 *
 * This class should be used for development because customer needs command input.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_Window
{
    /**
     * Shared memory byte size.
     *
     * @const int
     */
    const SHARED_MEMORY_SIZE = 1048576;

    /**
     * This instance.
     *
     * @var \BreakpointDebugging_Window
     */
    private static $_self;

    /**
     * Shared file path.
     *
     * @var string
     */
    private static $_sharedFilePath;

    /**
     * Once JavaScript flag.
     *
     * @var array
     */
    private static $_onceJavaScript = array ();

    /**
     * Shared memory operation ID or file pointer.
     *
     * @var int
     */
    private static $_resourceID;

    /**
     * Is "shmop" extention valid?
     *
     * @var bool
     */
    private static $_isShmopValid;

    /**
     * This method is called at execution end.
     */
    function __destruct()
    {
        self::_clearSharedResource();
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
     * @param string $javaScript JavaScript character string.
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
            // Locks the shared memory.
            $result = self::lockOn2Processes(0, 1, self::$_resourceID);
            B::assert($result !== false);
            try {
                // Gets the writing pointer.
                $javaScriptWritingPtr = shmop_read(self::$_resourceID, 3, 10);
                B::assert($javaScriptWritingPtr !== false);
                // If area to write overruns shared memory area.
                if ($javaScriptWritingPtr + strlen($javaScriptDispatcher) >= self::SHARED_MEMORY_SIZE) {
                    $lastStrLen = self::SHARED_MEMORY_SIZE - $javaScriptWritingPtr;
                    $javaScriptDispatcherBefore = substr($javaScriptDispatcher, 0, $lastStrLen);
                    $javaScriptDispatcher = substr($javaScriptDispatcher, $lastStrLen);
                    // Writes shared resource of JavaScript character string until end of shared memory area.
                    $result = shmop_write(self::$_resourceID, $javaScriptDispatcherBefore, $javaScriptWritingPtr);
                    B::assert($result !== false);
                    // Initializes the shared memory writing pointer.
                    $javaScriptWritingPtr = 23;
                }
                // Writes shared resource of JavaScript character string.
                $result = shmop_write(self::$_resourceID, $javaScriptDispatcher, $javaScriptWritingPtr);
                B::assert($result !== false);
                $javaScriptWritingPtr += strlen($javaScriptDispatcher);
                $javaScriptWritingPtr = sprintf('0x%08X', $javaScriptWritingPtr);
                // Registers the writing pointer.
                $result = shmop_write(self::$_resourceID, $javaScriptWritingPtr, 3);
                B::assert($result !== false);
            } catch (\Exception $e) {
                // Unlocks the shared memory.
                $result = self::unlockOn2Processes(0, self::$_resourceID);
                B::assert($result !== false);
                throw $e;
            }
            // Unlocks the shared memory.
            $result = self::unlockOn2Processes(0, self::$_resourceID);
            B::assert($result !== false);
        } else {
            // Writes to shared file. Append open file is atomic writing.
            $result = fwrite(self::$_resourceID, $javaScriptDispatcher);
            B::assert($result !== false);
            $result = fflush(self::$_resourceID);
            B::assert($result !== false);
        }
    }

    /**
     * Generates "Mozilla Firefox" start command.
     *
     * @param string $uri URI for display.
     *
     * @return void
     */
    static function generateMozillaFirefoxStartCommand($uri)
    {
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            return '"C:/Program Files/Mozilla Firefox/firefox.exe" "' . $uri . '"';
        } else {
            return '"firefox" "' . $uri . '"';
        }
    }

    /**
     * Creates or initializes shared resource for other process display because session unit test must not send HTML header.
     *
     * @return void
     */
    private static function _initializeSharedResource()
    {
        $openFirefoxWindow = function ($uri) {
            $command = BW::generateMozillaFirefoxStartCommand($uri);
            if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // If Windows.
                // Opens "BreakpointDebugging_DisplayToOtherProcess.php" page for display in other process.
                `$command`;
                return;
            } else { // If UNIX.
                $errorMessage = <<<EOD
<pre>
Please, stop this project.
Next, execute following command to open other process window.

$command

Next, restart this project.
</pre>
EOD;
                exit($errorMessage);
            }
        };


        self::$_isShmopValid = extension_loaded('shmop');
        // Gets full shared file path.
        self::$_sharedFilePath = B::getStatic('$_workDir') . '/SharedFileForOtherProcessDisplay.txt';
        $sharedFilePath = self::$_sharedFilePath;
        // Copies the "BreakpointDebugging_DisplayToOtherProcess.php" file into current work directory, and gets its URI.
        $uri = 'https:' . B::copyResourceToCWD('BreakpointDebugging_DisplayToOtherProcess.php', '');
        // Creates this class instance for shared memory close or shared file deletion.
        self::$_self = new BW();
        // If "shmop" extention is valid.
        if (self::$_isShmopValid) {
            while (true) {
                // If shared file exists.
                if (is_file($sharedFilePath)) {
                    // Gets shared memory operation key.
                    $shmopKey = file_get_contents($sharedFilePath);
                    if ($shmopKey === false) {
                        sleep(1);
                        continue;
                    }
                    set_error_handler('\BreakpointDebugging::handleError', 0);
                    // Opens shared memory.
                    self::$_resourceID = @shmop_open($shmopKey, 'w', 0, 0);
                    restore_error_handler();
                    // If valid shared memory.
                    if (!empty(self::$_resourceID)) {
                        return;
                    }
                    echo '<strong style="color:red">Server was down.</strong>';
                    // Unlinks shared file.
                    if (unlink($sharedFilePath) === false) {
                        sleep(1);
                        continue;
                    }
                }
                // Allocates shared memory area.
                list($shmopKey, self::$_resourceID) = BS::buildSharedMemory(self::SHARED_MEMORY_SIZE);
                // Registers the shared memory key.
                if (B::filePutContents($sharedFilePath, $shmopKey) === false) {
                    sleep(1);
                    continue;
                }
                // Initializes shared memory.
                if (shmop_write(self::$_resourceID, sprintf('0000x%08X0x%08X', 23, 23), 0) === false) {
                    sleep(1);
                    continue;
                }
                break;
            }
            // If "CakePHP".
            if (BREAKPOINTDEBUGGING_IS_CAKE) {
                $uri = str_replace('\\', '/', $uri);
                $uri = str_replace('/app/webroot/', '/', $uri);
            }
            // Opens "Mozilla Firefox" window.
            $openFirefoxWindow($uri);
        } else { // If "shmop" extension is invalid.
            if ($_SERVER['SERVER_ADDR'] === '127.0.0.1') { // If local server.
                exit(B::getErrorHTML('You must enable "shmop" extention inside "php.ini" file.'));
            } else { // If remote server.
                while (true) {
                    // If shared file exists.
                    if (is_file($sharedFilePath)) {
                        $fileStatus = stat($sharedFilePath);
                        B::assert($fileStatus !== false);
                        // If other process for display had been ending.
                        if ($fileStatus['size'] === 1) {
                            break;
                        }
                        // Opens the shared file with append writing.
                        $pFile = B::fopen(array ($sharedFilePath, 'ab'));
                        B::assert($pFile !== false);
                        self::$_resourceID = $pFile;
                        // Health check.
                        $result = fwrite($pFile, ' ');
                        B::assert($result !== false);
                        $result = fflush($pFile);
                        B::assert($result !== false);
                        $fileStatus = fstat($pFile);
                        $expectedFileSize = $fileStatus['size'] + 1;
                        for ($count = 0; $count < 5; $count++) {
                            // Wait 1 seconds.
                            usleep(1000000);
                            $fileStatus = fstat($pFile);
                            $currentFileSize = $fileStatus['size'];
                            if ($currentFileSize === $expectedFileSize) {
                                return;
                            }
                        }
                        echo '<strong style="color:red">Server was down.</strong>';
                        // Does not unlink the shared file because possibility that remote server is UNIX is high.
                    }
                    break;
                }
                // Opens "Mozilla Firefox" window.
                $openFirefoxWindow($uri);
                // Creates shared file, and checks for other process start.
                $result = B::filePutContents($sharedFilePath, '');
                B::assert($result !== false);
            }
        }
    }

    /**
     * Clears shared resource.
     *
     * @return void
     */
    private static function _clearSharedResource()
    {
        // If resource had not been created.
        if (!isset(self::$_resourceID)) {
            return;
        }
        // If "shmop" extention is valid.
        if (self::$_isShmopValid) {
            // Closes the shared memory area.
            shmop_close(self::$_resourceID);
        } else { // If "shmop" extention is invalid.
            // Closes the shared file.
            $result = fclose(self::$_resourceID);
            B::assert($result !== false);
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

        if (!isset(self::$_resourceID)) {
            self::_initializeSharedResource();
        }

        $htmlFileContent = str_replace(array ('\\', '\'', "\r", "\n"), array ('\\\\', '\\\'', '\r', '\n'), $htmlFileContent);
        self::_dispatchJavaScript("BreakpointDebugging_windowVirtualOpen('$windowName', '$htmlFileContent');" . PHP_EOL);
    }

    /**
     * Closes out browser window.
     *
     * @param string $windowName Window name.
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
     * @param int $start The script tag number for start location to clear.
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
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        self::virtualOpen(B::ERROR_WINDOW_NAME, B::getErrorHtmlFileTemplate());
        self::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, $message);
        self::front(B::ERROR_WINDOW_NAME);
        if (function_exists('xdebug_break')) {
            xdebug_break();
        }
        exit;
    }

    /**
     *  Displays an error exception into error window. Next, throws exception.
     *
     * @param string $message Error message by "PHP_EOL".
     *
     * @return void
     *
     * @throws \BreakpointDebugging_ErrorException
     */
    static function throwErrorException($message)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // If command line.
            return;
        }

        self::virtualOpen(B::ERROR_WINDOW_NAME, B::getErrorHtmlFileTemplate());
        self::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, $message);
        self::front(B::ERROR_WINDOW_NAME);

        throw new \BreakpointDebugging_ErrorException('');
    }

}
