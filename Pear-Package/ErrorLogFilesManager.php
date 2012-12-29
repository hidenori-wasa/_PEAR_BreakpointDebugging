<?php

/**
 * Error log files manager.
 *
 * Procedure1: Please, set "$_BreakpointDebugging_EXE_MODE = $RELEASE;" in "BreakpointDebugging_MySetting.php" file.
 * Procedure2: Please, register your IP address to "$myIPAddress".
 * Procedure3: Please, upload this page to the project current directory.
 * Procedure4: Please, call this page from browser.
 * Procedure5: Please, download by clicking all "Download error log file" button.
 * Procedure6: Please, click "Delete all error log files" button.
 * Procedure7: Please, debug php code by downloaded error log files.
 * Procedure8: Please, go to "Procedure5" if "Download error log file" button exists.
 * Procedure9: Please, upload repaired php code.
 * Procedure10: Please, click "Reset error log files" button.
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
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
$myIPAddress = '127.0.0.1'; // Please, enter your IP address.
//////////////////////////////////////////////////////////////////
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

// Checks the execution mode.
if ($_BreakpointDebugging_EXE_MODE !== B::RELEASE) { // In case of not release.
    exit('<pre>You must set "$_BreakpointDebugging_EXE_MODE = $setExecutionMode(\'RELEASE\');" into "BreakpointDebugging_MySetting.php" file.</pre>');
}

// Checks client IP address.
if ($_SERVER['REMOTE_ADDR'] !== $myIPAddress) {
    exit('<pre>You must register your IP address into "$myIPAddress" of this page, then upload it.</pre>');
}
// Checks the request protocol.
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    exit('<pre>You must use "https" protocol.</pre>');
}
// Locks error log files.
$lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
$lockByFileExisting->lock();
$errorLogDirectory = B::$workDir . '/ErrorLog/';
if (!is_dir($errorLogDirectory)) {
    echo 'Error log directory does not exist.';
    goto END_LABEL;
}
$errorLogDirElements = scandir($errorLogDirectory);
// When you pushed "Download error log files" button.
if (isset($_GET['download'])) {
    /**
     * Downloads a file.
     *
     * @param string $filepath A file path.
     *
     * @return void
     */
    function download($filepath)
    {
        // Sends HTML header.
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"'); // Established file name. "filename" is sending-data file name.
        header('Content-Type: application/octet-stream'); // Content type. "application/octet-stream" is dialog which saves response by naming.
        header('Content-Transfer-Encoding: binary'); // Content-transfer-encoding.
        header('Content-Length: ' . filesize($filepath)); // Message body length.
        header('Pragma: private'); // For HTTP/1.0. "private" is cached on client, but it is not cached on proxy.
        header('Cache-Control: private'); // For HTTP/1.1 instead of "Pragma".
        // Downloads by sector unit to avoid memory lack.
        $pFile = fopen($filepath, 'rb');
        while (!feof($pFile)) {
            echo fread($pFile, 4096);
        }
        fclose($pFile);
    }

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
        download($errorLogDirElementPath);
        $doneDownload = true;
        break;
    }
    B::assert($doneDownload, 1);
} else if (isset($_GET['deleteErrorLogs'])) { // When you pushed "Delete all error log files" button.
    // Searches the files which should delete.
    foreach ($errorLogDirElements as $errorLogDirElement) {
        if (!preg_match('`^php_error_[1-8]\.log$`xXu', $errorLogDirElement)) {
            continue;
        }
        $errorLogDirElementPath = $errorLogDirectory . $errorLogDirElement;
        if (!is_file($errorLogDirElementPath)) {
            continue;
        }
        // Deletes the error log file, variable configuring file or the error location file.
        unlink($errorLogDirElementPath);
    }
    echo '<H1>You must delete this page for security.</H1>';
} else if (isset($_GET['reset'])) { // When you pushed "Reset error log files" button.
    // Searches the files which should delete.
    foreach ($errorLogDirElements as $errorLogDirElement) {
        $errorLogDirElementPath = $errorLogDirectory . $errorLogDirElement;
        if (!is_file($errorLogDirElementPath)) {
            continue;
        }
        // Deletes the error log file, variable configuring file or the error location file.
        unlink($errorLogDirElementPath);
    }
    echo '<H1>You must delete this page for security.</H1>';
} else { // In case of first time when this page was called.
    $thisFileName = basename(__FILE__);
    // Makes error log download-buttons.
    foreach ($errorLogDirElements as $errorLogDirElement) {
        if (!preg_match('`^php_error_[1-8]\.log$`xXu', $errorLogDirElement)) {
            continue;
        }
        echo <<<EOD
<form method="post" action="{$thisFileName}?download={$errorLogDirElement}">
    <input type="submit" value="Download error log file ({$errorLogDirElement})"/>
</form>
EOD;
    }
    echo <<<EOD
<form method="post" action="{$thisFileName}?deleteErrorLogs">
    <input type="submit" value="Delete all error log files"> You must download all error log files before you push this button.</input>
</form>
EOD;
    echo <<<EOD
<form method="post" action="{$thisFileName}?reset">
    <input type="submit" value="Reset error log files"> You must debug and upload all error code before you push this button.</input>
</form>
EOD;
}

END_LABEL:
// Unlocks error log files.
$lockByFileExisting->unlock();

?>
