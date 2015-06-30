<?php

/**
 * Error log files manager.
 *
 * LICENSE:
 * Copyright (c) 2012-, Hidenori Wasa
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
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;

/**
 * Error log files manager.
 *
 * Please, run the following procedure.
 * Procedure1: Switch to production mode with "./BreakpointDebugging_ProductionSwitcher.php" page
 *      if "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" in "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'".
 * Procedure2: Register your IP address to "$developerIP"
 *      in "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'" file.
 * Procedure3: Call this page from browser.
 * Procedure4: Download by clicking all "Download error log file" button.
 * Procedure5: Click "Delete all error log files" button.
 * Procedure6: Debug php code by downloaded error log files.
 * Procedure7: Go to "Procedure5" if "Download error log file" button exists.
 * Procedure8: Upload repaired php code.
 * Procedure9: Click "Reset error log files" button.
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
        self::$_lockByFileExisting->unlock();

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
        if (!B::checkDevelopmentSecurity('RELEASE')) {
            exit;
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
    <body style="background-color: black; color: white; font-size: 25px">
        <pre></pre>
    </body>
</html>
EOD;

        // Locks error log files.
        self::$_lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
        self::$_lockByFileExisting->lock();

        try {
            $errorLogDirectory = BREAKPOINTDEBUGGING_WORK_DIR_NAME . \BreakpointDebugging_Error::getErrorLogDir();
            if (!is_dir($errorLogDirectory)) {
                BW::virtualOpen(__CLASS__, $errorHtmlFileContent);
                BW::htmlAddition(__CLASS__, 'pre', 0, 'Error log directory does not exist.');
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
                BW::virtualOpen(__CLASS__, $errorHtmlFileContent);
                BW::htmlAddition(__CLASS__, 'pre', 0, 'You must comment out "$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" inside "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" file before your IP is changed.');
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
                BW::virtualOpen(__CLASS__, $errorHtmlFileContent);
                BW::htmlAddition(__CLASS__, 'pre', 0, 'You must comment out "$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" inside "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" file before your IP is changed.');
            } else { // In case of first time when this page was called.
                echo '<body style="background-color: black; color: white">';
                $thisFileName = basename(__FILE__);
                $fontStyle = 'style="font-size: 25px; font-weight: bold;"';
                // Makes error log download-buttons.
                foreach ($errorLogDirElements as $errorLogDirElement) {
                    if (!preg_match('`\.log$`xX', $errorLogDirElement)) {
                        continue;
                    }

                    $queryString = B::httpBuildQuery(array ('download' => $errorLogDirElement));
                    echo <<<EOD
<br/>
<form method="post" action="$thisFileName?$queryString">
    <input type="submit" value="Download error log file ({$errorLogDirElement})" $fontStyle/>
</form>
EOD;
                }

                $queryString = B::httpBuildQuery(array ('deleteErrorLogs' => true));
                echo <<<EOD
<br/><br/>
<form method="post" action="$thisFileName?$queryString">
    <input type="submit" value="Delete all error log files (You must download all error log files before you push this button.)" $fontStyle/>
</form>
EOD;

                $queryString = B::httpBuildQuery(array ('reset' => true));
                echo <<<EOD
<br/><br/>
<form method="post" action="$thisFileName?$queryString">
    <input type="submit" value="Reset error log files (You must debug and upload all error code before you push this button.)" $fontStyle/>
</form>
EOD;
                echo '</body>';
            }
        } catch (\Exception $e) {
            // Unlocks error log files.
            self::$_lockByFileExisting->unlock();
            throw $e;
        }

        END_LABEL:
        // Unlocks error log files.
        self::$_lockByFileExisting->unlock();
    }

}

\BreakpointDebugging_ErrorLogFilesManager::manageErrorLogFiles();
