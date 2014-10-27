<?php

/**
 * Tool to switch production mode and development mode.
 *
 * Please, run the following procedure.
 * Procedure1: Display "./BreakpointDebugging_ProductionSwitcher.php" page with browser.
 * Procedure2: Click showing button ("Switch to production" button or "Switch to development" button).
 *
 * "Switch to development" button:
 *      1. Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" to "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'" file.
 *              This mode stops at error location, then we can do step execution even though we execute unit test.
 *              Also, this mode displays error information.
 *              Moreover, we can change execution page and execution mode by project configuration change of IDE only.
 *      2. Deletes "// <BREAKPOINTDEBUGGING_COMMENT> " of "\BreakpointDebugging::assert()", "\BreakpointDebugging::iniSet()" and "\BreakpointDebugging::iniCheck()" line.
 * "Switch to production" button:
 *      1. Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" to "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'" file.
 *              This mode does not display "XDebug" and error logging information for security.
 *              Also, this mode cannot change mode by URL rewriting for security.
 *              Moreover, increases execution speed.
 *      2. Inserts "// <BREAKPOINTDEBUGGING_COMMENT> " into "\BreakpointDebugging::assert()" line.
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
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use BreakpointDebugging_Window as BW;

/**
 * Tool to switch production mode and development mode.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_ProductionSwitcher
{
    /**
     * @var array Black list paths to manage.
     */
    private static $_blackListPaths = array (
        'BreakpointDebugging/Component/',
        'BreakpointDebugging/PHPUnit/',
        'BreakpointDebugging_DisplayToOtherProcess.php',
        'BreakpointDebugging_ErrorLogFilesManager.php',
        'BreakpointDebugging_InDebug.php',
        'BreakpointDebugging_MySetting.php',
        'BreakpointDebugging_MySetting_InDebug.php',
        'BreakpointDebugging_NativeFunctions_InDebug.php',
        'BreakpointDebugging_PHPUnit.php',
        'BreakpointDebugging_PHPUnit_DisplayCodeCoverageReport.php',
        'BreakpointDebugging_ProductionSwitcher.php',
        'BreakpointDebugging/Window.php',
        './nbproject/',
        './tests/',
    );

    /**
     * @var string  Mode error message.
     */
    private static $_modeErrorMessage;

    /**
     * Scans directory to search "*.php" files.
     *
     * @param string $fullDirPath  Full directory path for search.
     * @param array  $phpFilePaths "*.php" paths for switching.
     */
    private static function _scandir($fullDirPath, &$phpFilePaths)
    {
        // Extracts directory elements.
        $dirElements = scandir($fullDirPath);
        foreach ($dirElements as $dirElement) {
            if ($dirElement === '.' || $dirElement === '..') {
                continue;
            }
            $fullDirElement = str_replace('\\', '/', $fullDirPath) . '/' . $dirElement;
            foreach (self::$_blackListPaths as $fullBlackListPath => $value) {
                B::assert($value === true);
                // If black list.
                if ($fullDirElement === $fullBlackListPath) {
                    // Skips black list.
                    continue 2;
                }
            }
            // If directory.
            if (is_dir($fullDirElement)) {
                // Recursive call.
                self::_scandir($fullDirElement, $phpFilePaths);
            }
            // If this is "*.php" file except unit test file.
            if (is_file($fullDirElement) //
                && preg_match('`.*(?<!Test)\.php$`xX', $fullDirElement) //
            ) {
                // Registers full "*.php" file path.
                $phpFilePaths[$fullDirElement] = true;
            }
        }
    }

    /**
     * Searches "*.php" paths.
     *
     * @return array "*.php" paths for switching.
     * @throws \BreakpointDebugging_ErrorException
     */
    private static function _searchPHPFiles()
    {
        // White list to manage.
        $whiteListPaths = array (
            'BreakpointDebugging/',
            'BreakpointDebugging_InDebug.php',
            'BreakpointDebugging_LockByShmopResponse.php',
        );
        // Transforms white list to full path.
        $cwd = str_replace('\\', '/', getcwd());
        foreach ($whiteListPaths as $key => &$whiteListPath) {
            $fullPath = str_replace('\\', '/', stream_resolve_include_path($whiteListPath));
            if (strpos($fullPath, $cwd) === 0) {
                // Skip white list inside of project work directory.
                unset($whiteListPaths[$key]);
                continue;
            }
            $whiteListPath = $fullPath;
        }
        array_unshift($whiteListPaths, $cwd);
        // Transforms black list to full path.
        array_unshift(self::$_blackListPaths, B::getStatic('$_workDir'));
        $blackListPaths = array ();
        foreach (self::$_blackListPaths as $key => $blackListPath) {
            // Translates to full path.
            $result = stream_resolve_include_path($blackListPath);
            // If the file or directory exists.
            if ($result !== false) {
                $blackListPaths[str_replace('\\', '/', $result)] = true;
            }

            // If this is not this package's "*.php" black list file.
            if (strpos($blackListPath, 'BreakpointDebugging_') !== 0) {
                continue;
            }

            // Excepts "./" from include path.
            $includePath = get_include_path();
            $currentPath = '.' . PATH_SEPARATOR;
            $currentPath2 = './' . PATH_SEPARATOR;
            if (strpos($includePath, $currentPath) === 0) {
                $tmpIncludePath = substr($includePath, strlen($currentPath));
            } else if (strpos($includePath, $currentPath2) === 0) {
                $tmpIncludePath = substr($includePath, strlen($currentPath2));
            } else {
                throw new \BreakpointDebugging_ErrorException('Include path must begin ".' . PATH_SEPARATOR . '" or "./' . PATH_SEPARATOR . '".');
            }
            set_include_path($tmpIncludePath);
            // Excepts "./" from include path, then translates to full path.
            $result = stream_resolve_include_path($blackListPath);
            set_include_path($includePath);
            // If the file or directory exists.
            if ($result !== false) {
                // Adds the copied full "*.php" file path.
                $blackListPaths[str_replace('\\', '/', $result)] = true;
            }
        }
        self::$_blackListPaths = $blackListPaths;
        // Searches '*.php' files to manage.
        $phpFilePaths = array ();
        foreach ($whiteListPaths as $fullWhiteListPath) {
            if (is_dir($fullWhiteListPath)) { // If directory path.
                self::_scandir($fullWhiteListPath, $phpFilePaths);
            } else if (is_file($fullWhiteListPath)) { // If file path.
                // Asserts that this file is not unit test file.
                B::assert(preg_match('`.*(?<!Test)\.php$`xX', $fullWhiteListPath));
                // Registers full "*.php" file path.
                $phpFilePaths[$fullWhiteListPath] = true;
            }
        }
        return $phpFilePaths;
    }

    /**
     * Switches mode.
     *
     * @return void
     */
    static function switchMode()
    {
        if (!B::checkDevelopmentSecurity()) {
            exit;
        }

        // Cancels the script running time limitation.
        set_time_limit(0);

        // Adds path.
        self::$_blackListPaths[] = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting_InDebug.php';

        $getHtmlFileContent = function ($title) {
            $htmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>$title</title>
    </head>
    <body style="background-color: black; color: white; font-size: 25px">
    </body>
</html>
EOD;
            return $htmlFileContent;
        };
        // Locks "*.php" files.
        $pLock = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
        $pLock->lock();

        try {
            if (isset($_GET['production']) // 'Switch to production' button was pushed.
                || isset($_GET['development']) // Or, 'Switch to development' button was pushed.
            ) {
                BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcher'));
                // Opens my setting file.
                $mySettingFilePath = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
                $fullMySettingFilePath = str_replace('\\', '/', stream_resolve_include_path($mySettingFilePath));
                $phpFilePaths = self::_searchPHPFiles();
            } else { // In case of first time when this page was called.
                BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcher'));
                BW::htmlAddition(__CLASS__, 'body', 0, '<h1>ProductionSwitcher</h1><hr />');
                $makeButton = function ($modeKey, $getHtmlFileContent) {
                    $thisFileName = basename(__FILE__);
                    $fontStyle = 'style="font-size: 25px; font-weight: bold;"';
                    $queryString = B::httpBuildQuery(array ($modeKey => true));
                    $html = <<<EOD
<form method="post" action="$thisFileName?$queryString">
    <input type="submit" value="Switch to $modeKey" $fontStyle/>
</form>
EOD;
                    BW::htmlAddition(__CLASS__, 'body', 0, $html);
                };
                // Makes buttons.
                if (BREAKPOINTDEBUGGING_IS_PRODUCTION) {
                    $makeButton('development', $getHtmlFileContent);
                    $html = <<<EOD
<ol>
    <li>Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" to "{$settingDirName}BreakpointDebugging_MySetting.php" file.</li>
    <li>Deletes "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " of "\BreakpointDebugging::assert()", "\BreakpointDebugging::iniSet()" and "\BreakpointDebugging::iniCheck()" line.</li>
</ol>
<hr />
EOD;
                    BW::htmlAddition(__CLASS__, 'body', 0, $html);
                } else {
                    $makeButton('production', $getHtmlFileContent);
                    $settingDirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
                    $html = <<<EOD
<ol>
    <li>Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" to "{$settingDirName}BreakpointDebugging_MySetting.php" file.</li>
    <li>Inserts "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " into "\BreakpointDebugging::assert()" line.</li>
</ol>
<hr />
EOD;
                    BW::htmlAddition(__CLASS__, 'body', 0, $html);
                }
                goto END_LABEL;
            }

            // 'Switch to production' button was pushed.
            // Or, 'Switch to development' button was pushed.
            $getFileToArray = function ($phpFilePath, $getHtmlFileContent) {
                $pFile = B::fopen(array ($phpFilePath, 'r+b'));
                if ($pFile === false) {
                    BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcherError'));
                    BW::htmlAddition(__CLASS__, 'body', 0, '"' . $phpFilePath . '" file cannot open.');
                    throw new \BreakpointDebugging_ErrorException('');
                }
                $lines = array ();
                while (($result = fgets($pFile)) !== false) {
                    $lines[] = $result;
                }
                return array ($pFile, $lines);
            };

            $writeAndClose = function ($pFile, $lines, $fullFilePath, $thisClassName, $isChanged) {
                // Closes the "*.php" file stream.
                $result = fclose($pFile);
                B::assert($result === true);
                if ($isChanged) {
                    // Displays the progress.
                    $newFullFilePath = B::getStatic('$_workDir') . '/' . basename($fullFilePath) . '.copy';
                    BW::htmlAddition($thisClassName, 'body', 0, 'Renaming "' . $fullFilePath . '" to "' . $newFullFilePath . '".<br />');
                    // Renames the "*.php" file to "*.php.copy".
                    rename($fullFilePath, $newFullFilePath);
                    // Displays the progress.
                    BW::htmlAddition($thisClassName, 'body', 0, '<span style="color: red">Writing "' . $fullFilePath . '".</span><br />');
                    // Writes the array to "*.php" file.
                    file_put_contents($fullFilePath, $lines);
                    // Displays the progress.
                    BW::htmlAddition($thisClassName, 'body', 0, 'Deleting "' . $newFullFilePath . '".<br />');
                    // Deletes the "*.php.copy" file.
                    B::unlink(array ($newFullFilePath));
                } else {
                    // Displays the progress.
                    BW::htmlAddition($thisClassName, 'body', 0, '<span style="color: gray">Skips "' . $fullFilePath . '".</span><br />');
                }
            };

            while (true) {
                // Copies the "*.php" file lines to an array.
                list($pFile, $lines) = $getFileToArray($fullMySettingFilePath, $getHtmlFileContent);

                if (isset($_GET['production'])) { // 'Switch to production' button was pushed.
                    foreach ($lines as &$line) {
                        // Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" to "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'" file.
                        $result = preg_replace(
                            '`^ ( [[:blank:]]* const [[:blank:]]* BREAKPOINTDEBUGGING_IS_PRODUCTION [[:blank:]]* = [[:blank:]]* ) false ( [[:blank:]]* ; .* )`xXs', //
                            '$1true$2', //
                            $line, //
                            1 //
                        );
                        B::assert($result !== null);
                        if ($result !== $line) {
                            $line = $result;
                            $writeAndClose($pFile, $lines, $fullMySettingFilePath, __CLASS__, true);
                            break 2;
                        }
                    }
                    // In case of error.
                    BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcherError'));
                    BW::htmlAddition(__CLASS__, 'body', 0, 'You must define "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" in "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".');
                } else { // 'Switch to development' button was pushed.
                    foreach ($lines as &$line) {
                        // Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" to "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'" file.
                        $result = preg_replace(
                            '`^ ( [[:blank:]]* const [[:blank:]]* BREAKPOINTDEBUGGING_IS_PRODUCTION [[:blank:]]* = [[:blank:]]* ) true ( [[:blank:]]* ; .* )`xXs', //
                            '$1false$2', //
                            $line, //
                            1 //
                        );
                        B::assert($result !== null);
                        if ($result !== $line) {
                            $line = $result;
                            $writeAndClose($pFile, $lines, $fullMySettingFilePath, __CLASS__, true);
                            break 2;
                        }
                    }
                    // In case of error.
                    BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcherError'));
                    BW::htmlAddition(__CLASS__, 'body', 0, 'You must define "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" in "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".');
                }
                throw new \BreakpointDebugging_ErrorException('');
            }
            foreach ($phpFilePaths as $phpFilePath => $value) {
                B::assert($value === true);
                // Copies the "*.php" file lines to an array.
                list($pFile, $lines) = $getFileToArray($phpFilePath, $getHtmlFileContent);

                $isChanged = false;
                if (isset($_GET['production'])) { // 'Switch to production' button was pushed.
                    foreach ($lines as &$line) {
                        // Inserts "// <BREAKPOINTDEBUGGING_COMMENT> " into "\BreakpointDebugging::assert()" line.
                        $result = preg_replace(
                            '`^ ( [[:blank:]]* ) ( \\\\? [[:blank:]]* [_[:alpha:]]+ [_[:alnum:]]* [[:blank:]]* :: [[:blank:]]* assert [[:blank:]]* \( .* )`xXs', //
                            '$1// <BREAKPOINTDEBUGGING_COMMENT> $2', //
                            $line, //
                            1 //
                        );
                        B::assert($result !== null);
                        if ($result !== $line) {
                            $line = $result;
                            $isChanged = true;
                        }
                    }
                } else { // 'Switch to development' button was pushed.
                    foreach ($lines as &$line) {
                        // Deletes "// <BREAKPOINTDEBUGGING_COMMENT> " of "\BreakpointDebugging::assert()", "\BreakpointDebugging::iniSet()" and "\BreakpointDebugging::iniCheck()" line.
                        $result = preg_replace(
                            '`^ ( [[:blank:]]* ) //\x20<BREAKPOINTDEBUGGING_COMMENT>\x20( .* )`xXs', //
                            '$1$2', //
                            $line, //
                            1 //
                        );
                        B::assert($result !== null);
                        if ($result !== $line) {
                            $line = $result;
                            $isChanged = true;
                        }
                    }
                }

                // 'Switch to production' button was pushed.
                // Or, 'Switch to development' button was pushed.
                $writeAndClose($pFile, $lines, $phpFilePath, __CLASS__, $isChanged);
            }
            // In case of success.
            BW::htmlAddition(__CLASS__, 'body', 0, '<p style="color: aqua">Switch has been done.</p>');
            BW::htmlAddition(__CLASS__, 'body', 0, '<p style="color: aqua">You must comment out "$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" inside "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" file before your IP is changed.</p>');
            BW::scrollBy(__CLASS__, PHP_INT_MAX, PHP_INT_MAX);
        } catch (\Exception $e) {
            // Unlocks error log files.
            $pLock->unlock();
            throw $e;
        }

        END_LABEL:
        // Unlocks error log files.
        $pLock->unlock();
    }

}

\BreakpointDebugging_ProductionSwitcher::switchMode();
