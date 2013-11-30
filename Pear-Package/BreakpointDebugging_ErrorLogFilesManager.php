<?php

/**
 * Error log files manager.
 *
 * Please, run the following procedure.
 * Procedure1: Set
 *      "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags('RELEASE');"
 *      in "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'" file.
 * Procedure2: Register your IP address to "$developerIP"
 *      in "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'" file.
 * Procedure3: Upload this page to the project current directory.
 * Procedure4: Call this page from browser.
 * Procedure5: Download by clicking all "Download error log file" button.
 * Procedure6: Click "Delete all error log files" button.
 * Procedure7: Debug php code by downloaded error log files.
 * Procedure8: Go to "Procedure5" if "Download error log file" button exists.
 * Procedure9: Upload repaired php code.
 * Procedure10: Click "Reset error log files" button.
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012-2013, Hidenori Wasa
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
//////////////////////////////////////////////////////////////////
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;

/**
 * Error log files manager.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_ErrorLogFilesManager
{
    private static $_lockByFileExisting;

    /**
     * Downloads a file.
     *
     * @param string $filepath A file path.
     *
     * @return void
     */
    private static function _download($filepath)
    {
        // Sends HTML header.
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"'); // Established file name. "filename" is sending-data file name.
        header('Content-Type: application/octet-stream'); // Content type. "application/octet-stream" is dialog which saves response by naming.
        header('Content-Transfer-Encoding: binary'); // Content-transfer-encoding.
        header('Content-Length: ' . filesize($filepath)); // Message body length.
        header('Pragma: private'); // For HTTP/1.0. "private" is cached on client, but it is not cached on proxy.
        header('Cache-Control: private'); // For HTTP/1.1 instead of "Pragma".
        // Copies error log file to temporary file.
        $pFile = B::fopen(array ($filepath, 'rb'));
        $pTmp = tmpfile();
        $offset = 0;
        while (!feof($pFile)) {
            $offset += stream_copy_to_stream($pFile, $pTmp, 4096, $offset);
            fflush($pTmp);
        }
        fclose($pFile);

        // Unlocks error log files.
        self::$_lockByFileExisting[0]->unlock();

        // Downloads by sector unit to avoid memory lack.
        rewind($pTmp);
        while (!feof($pTmp)) {
            echo fread($pTmp, 4096);
            flush();
        }
        fclose($pTmp);
        exit;
    }

    /**
     * Manages error log files.
     *
     * @return void
     */
    static function manageErrorLogFiles()
    {
        if (!B::checkDevelopmentSecurity(B::RELEASE)) {
            return;
        }

        // Cancels the script running time limitation.
        set_time_limit(0);

        $errorHtmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>ErrorLogFilesManager</title>
    </head>
    <body style="background-color: black; color: white; font-size: 1.5em">
        <pre></pre>
    </body>
</html>
EOD;

        // Locks error log files.
        self::$_lockByFileExisting[0] = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
        self::$_lockByFileExisting[0]->lock();

        try {
            $errorLogDirectory = B::getStatic('$_workDir') . \BreakpointDebugging_Error::getErrorLogDir();
            if (!is_dir($errorLogDirectory)) {
                B::windowVirtualOpen(__CLASS__, $errorHtmlFileContent);
                B::windowHtmlAddition(__CLASS__, 'pre', 0, 'Error log directory does not exist.');
                goto END_LABEL;
            }
            $errorLogDirElements = scandir($errorLogDirectory);
            // When you pushed "Download error log files" button.
            if (isset($_GET['download'])) {
                // Searches the error log file which should download.
                foreach ($errorLogDirElements as $errorLogDirElement) {
                    $errorLogDirElementPath = $errorLogDirectory . $errorLogDirElement;
                    if (!is_file($errorLogDirElementPath)) {
                        continue;
                    }
                    if ($_GET['download'] !== $errorLogDirElement) {
                        continue;
                    }
                    // Downloads the error log file.
                    self::_download($errorLogDirElementPath);
                }
            } else if (isset($_GET['deleteErrorLogs'])) { // When you pushed "Delete all error log files" button.
                // Searches the files which should delete.
                foreach ($errorLogDirElements as $errorLogDirElement) {
                    if (!preg_match('`\.log$`xX', $errorLogDirElement)) {
                        continue;
                    }
                    $errorLogDirElementPath = $errorLogDirectory . $errorLogDirElement;
                    if (!is_file($errorLogDirElementPath)) {
                        continue;
                    }
                    // Deletes the error log file, variable configuring file or the error location file.
                    B::unlink(array ($errorLogDirElementPath));
                }
                B::windowVirtualOpen(__CLASS__, $errorHtmlFileContent);
                B::windowHtmlAddition(__CLASS__, 'pre', 0, 'You must comment out "$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" inside "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" file before your IP is changed.');
            } else if (isset($_GET['reset'])) { // When you pushed "Reset error log files" button.
                // Searches the files which should delete.
                foreach ($errorLogDirElements as $errorLogDirElement) {
                    $errorLogDirElementPath = $errorLogDirectory . $errorLogDirElement;
                    if (!is_file($errorLogDirElementPath)) {
                        continue;
                    }
                    // Deletes the error log file, variable configuring file or the error location file.
                    B::unlink(array ($errorLogDirElementPath));
                }
                B::windowVirtualOpen(__CLASS__, $errorHtmlFileContent);
                B::windowHtmlAddition(__CLASS__, 'pre', 0, 'You must comment out "$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" inside "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" file before your IP is changed.');
            } else { // In case of first time when this page was called.
                echo '<body style="background-color:black;color:white">';
                $thisFileName = basename(__FILE__);
                $fontStyle = 'style="font-size: 24px; font-weight: bold;"';
                // Makes error log download-buttons.
                foreach ($errorLogDirElements as $errorLogDirElement) {
                    if (!preg_match('`\.log$`xX', $errorLogDirElement)) {
                        continue;
                    }
                    echo <<<EOD
<br/>
<form method="post" action="$thisFileName?download=$errorLogDirElement&{$_SERVER['QUERY_STRING']}">
    <input type="submit" value="Download error log file ({$errorLogDirElement})" $fontStyle/>
</form>
EOD;
                }
                echo <<<EOD
<br/><br/>
<form method="post" action="$thisFileName?deleteErrorLogs&{$_SERVER['QUERY_STRING']}">
    <input type="submit" value="Delete all error log files (You must download all error log files before you push this button.)" $fontStyle/>
</form>
EOD;
                echo <<<EOD
<br/><br/>
<form method="post" action="$thisFileName?reset&{$_SERVER['QUERY_STRING']}">
    <input type="submit" value="Reset error log files (You must debug and upload all error code before you push this button.)" $fontStyle/>
</form>
EOD;
                echo '</body>';
            }
        } catch (\Exception $e) {
            // Unlocks error log files.
            self::$_lockByFileExisting[0]->unlock();
            throw $e;
        }

        END_LABEL:
        // Unlocks error log files.
        self::$_lockByFileExisting[0]->unlock();
    }

}

\BreakpointDebugging_ErrorLogFilesManager::manageErrorLogFiles();

?>
