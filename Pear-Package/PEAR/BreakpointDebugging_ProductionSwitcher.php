<?php

/**
 * Tool to switch production mode and development mode.
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
require_once './BreakpointDebugging_Inclusion.php';
require_once './BreakpointDebugging_Optimizer.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;

/**
 * Tool to switch production mode and development mode.
 *
 * PHP version 5.3.2-5.4.x
 *
 * Please, run the following procedure.
 * Procedure1: Display "http://localhost/<project name>/BreakpointDebugging_ProductionSwitcher.php" page with browser.
 * Procedure2: Click showing button ("Switch to production" button or "Switch to development" button).
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_ProductionSwitcher extends \BreakpointDebugging_Optimizer
{
    private static $_commentOutAssertionRegEx = '`^ ( [[:blank:]]* ) ( \\\\ [[:blank:]]* BreakpointDebugging [[:blank:]]* :: [[:blank:]]* assert [[:blank:]]* \( .* \) [[:blank:]]* ; [[:blank:]]* (// .*)? [\r\n]* ) $`xXU';
    private static $_changeModeConstToLiteralRegEx1 = '`^ ( [[:blank:]]* ) (( if [[:blank:]]* \( [[:blank:]]* !? [[:blank:]]* ) ';
    private static $_changeModeConstToLiteralRegEx2 = '((?![_[:alnum:]]) .* \) [[:blank:]]* { [[:blank:]]* (// .*)? ) [\r\n]* ) $`xXU';
    private static $_isDebugRegEx = '\\\\ [[:blank:]]* BreakpointDebugging [[:blank:]]* :: [[:blank:]]* isDebug [[:blank:]]* \( [[:blank:]]* \) ';
    private static $_breakpointdebuggingIsProductionRegEx = 'BREAKPOINTDEBUGGING_IS_PRODUCTION ';

    /**
     * Black list paths to manage.
     *
     * @var array
     */
    private static $_blackListPaths = array (
        './nbproject/',
        './tests/',
        'BreakpointDebugging/Component/',
        'BreakpointDebugging/PHPUnit/',
        'BreakpointDebugging/Sample/',
        'BreakpointDebugging_DisplayToOtherProcess.php',
        'BreakpointDebugging_ErrorLogFilesManager.php',
        'BreakpointDebugging_InDebug.php',
        'BreakpointDebugging_IniSetOptimizer.php',
        'BreakpointDebugging_NativeFunctions_InDebug.php',
        'BreakpointDebugging_Optimizer.php',
        'BreakpointDebugging_PHPUnit.php',
        'BreakpointDebugging_PHPUnit_DisplayCodeCoverageReport.php',
        'BreakpointDebugging_ProductionSwitcher.php',
        'BreakpointDebugging/Window.php',
    );

    /**
     * Scans directory to search "*.php" files.
     *
     * @param string $dirPath      Relative directory path for search.
     * @param string $fullDirPath  Full directory path for search.
     * @param array  $phpFilePaths "*.php" paths for switching.
     *
     * @return void
     */
    private static function _scandir($dirPath, $fullDirPath, &$phpFilePaths)
    {
        // Extracts directory elements.
        $dirElements = scandir($fullDirPath);
        foreach ($dirElements as $dirElement) {
            $fullDirElement = str_replace('\\', '/', $fullDirPath) . '/' . $dirElement;
            if ($dirElement === '.' //
                || $dirElement === '..' //
                || is_link($fullDirElement) // Skips symbolic link.
            ) {
                continue;
            }
            foreach (self::$_blackListPaths as $fullBlackListPath => $dummy) {
                // If black list.
                if ($fullDirElement === $fullBlackListPath) {
                    // Skips black list.
                    continue 2;
                }
            }
            $dirElement = str_replace('\\', '/', $dirPath) . $dirElement;
            // If directory.
            if (is_dir($fullDirElement)) {
                // Recursive call.
                self::_scandir($dirElement . '/', $fullDirElement, $phpFilePaths);
            } else if (is_file($fullDirElement) // If this is "*.php" file except unit test file's "*Test.php".
                && preg_match('`.*(?<!Test)\.php$`xX', $fullDirElement) //
            ) {
                // Registers "*.php" file path.
                $phpFilePaths[$dirElement] = true;
            }
        }
    }

    /**
     * Gets white list paths.
     *
     * @return type
     *
     * @throws \BreakpointDebugging_ErrorException
     */
    private static function _getWhiteListPaths()
    {
        // White list to manage.
        $whiteListPaths = array (
            'BreakpointDebugging/',
            'BreakpointDebugging_Inclusion.php',
            'BreakpointDebugging_LockByShmopResponse.php',
        );

        // Transforms white list to full path.
        $cwd = str_replace('\\', '/', getcwd());
        foreach ($whiteListPaths as $key => &$whiteListPath) {
            $fullPath = stream_resolve_include_path($whiteListPath);
            if ($fullPath === false) {
                BW::throwErrorException('"' . $whiteListPath . '" is invalid file path.');
            }
            $fullPath = str_replace('\\', '/', $fullPath);
            if (strpos($fullPath, $cwd) === 0) {
                // Skip white list inside of project work directory.
                unset($whiteListPaths[$key]);
                continue;
            }
        }
        array_unshift($whiteListPaths, './');

        if (BREAKPOINTDEBUGGING_IS_CAKE) {
            $cakeWhiteListPaths = array (
                '../Controller/',
                '../Lib/',
                '../Model/',
                '../Plugin/',
                '../View/',
            );
            $whiteListPaths = array_merge($whiteListPaths, $cakeWhiteListPaths);
        }

        return $whiteListPaths;
    }

    /**
     * Searches "*.php" paths.
     *
     * @return array "*.php" paths for switching.
     *
     * @throws \BreakpointDebugging_ErrorException
     */
    private static function _searchPHPFiles()
    {
        // Transforms black list to full path.
        array_unshift(self::$_blackListPaths, BREAKPOINTDEBUGGING_WORK_DIR_NAME);
        $blackListPaths = array ();
        foreach (self::$_blackListPaths as $blackListPath) {
            // If this is not this package's black list file and directory.
            if (preg_match('`^ BreakpointDebugging ( _ | / )`xX', $blackListPath) !== 1) {
                // Translates to full path.
                $result = stream_resolve_include_path($blackListPath);
                // If the file or directory exists.
                if ($result !== false) {
                    $blackListPaths[str_replace('\\', '/', $result)] = true;
                }
                continue;
            }

            // Translates to full path.
            $result = stream_resolve_include_path($blackListPath);
            // If the file or directory exists.
            if ($result !== false) {
                // Adds the copied full "*.php" file path.
                $blackListPaths[str_replace('\\', '/', $result)] = true;
            }
        }

        $whiteListPaths = self::_getWhiteListPaths();
        self::$_blackListPaths = $blackListPaths;
        // Searches '*.php' files to manage.
        $phpFilePaths = array ();
        foreach ($whiteListPaths as $whiteListPath) {
            $fullWhiteListPath = realpath($whiteListPath);
            B::assert($fullWhiteListPath !== false);
            if (is_dir($fullWhiteListPath)) { // If directory path.
                self::_scandir($whiteListPath, $fullWhiteListPath, $phpFilePaths);
            } else if (is_file($fullWhiteListPath)) { // If file path.
                // Asserts that this file is not unit test file.
                B::assert(preg_match('`.*(?<!Test)\.php$`xX', $whiteListPath));
                // Registers "*.php" file path.
                $phpFilePaths[$whiteListPath] = true;
            }
        }
        return $phpFilePaths;
    }

    /**
     * Check comment lines of plural line to skip.
     *
     * Plural line is "Heredoc", "Nowdoc", "/*" and "/**".
     *
     * @param string $phpFilePath   "*.php" file path to get.
     * @param int    $maxLineNumber Maximum line number.
     *
     * @return array The array to skip a line.
     */
    private static function _checkCommentLinesOfPluralLineToSkip($phpFilePath, $maxLineNumber)
    {
        $checkLinesToSkip = function ($startLine, $endLine, &$linesToSkip, &$state) {
            for ($count = $startLine; $count <= $endLine; $count++) {
                B::assert(array_key_exists($count, $linesToSkip));
                $linesToSkip[$count] = true;
            }
            $state = 'NONE';
        };

        $tokens = token_get_all(file_get_contents($phpFilePath));
        $state = 'NONE';
        $linesToSkip = array_fill(1, $maxLineNumber, false);
        $lineCount = 1;
        $semicolonCount = 0;
        foreach ($tokens as $token) {
            if (!is_array($token)) {
                if ($token === ';') {
                    $semicolonCount++;
                    if ($semicolonCount === 2) { // If plural syntax line.
                        $checkLinesToSkip($lineCount, $lineCount, $linesToSkip, $dummy);
                    }
                }
                continue;
            }
            if ($token[2] !== $lineCount) {
                $lineCount = $token[2];
                $semicolonCount = 0;
            }

            $tokenKind = $token[0];
            switch ($state) {
                case 'NONE':
                    if ($tokenKind === T_START_HEREDOC) { // If "Heredoc" or "Nowdoc".
                        $startLine = $token[2];
                        $state = 'DOC_END_SEARCH';
                    } else if (($tokenKind === T_DOC_COMMENT || $tokenKind === T_COMMENT) // If "/**", "/*" or "//".
                        && strpos($token[1], '/*') === 0 // If "/**" or "/*".
                    ) {
                        $startLine = $token[2];
                        $state = 'COMMENT_END_SEARCH';
                    }
                    break;
                case 'DOC_END_SEARCH':
                    if ($tokenKind === T_END_HEREDOC) { // If end of "Heredoc" or "Nowdoc".
                        $checkLinesToSkip($startLine, $token[2], $linesToSkip, $state);
                    }
                    break;
                case 'COMMENT_END_SEARCH':
                    $checkLinesToSkip($startLine, $token[2], $linesToSkip, $state);
                    break;
                default:
                    assert(false);
            }
        }

        switch ($state) {
            case 'DOC_END_SEARCH':
                BW::throwErrorException('"Heredoc" or "Nowdoc" must be ended.');
            case 'COMMENT_END_SEARCH':
                $checkLinesToSkip($startLine, $maxLineNumber, $linesToSkip, $state);
            case 'NONE':
                break;
            default:
                assert(false);
        }

        return $linesToSkip;
    }

    /**
     * Changes mode constant to literal for optimization of parsed code cache in production server.
     *
     * @param string $line         Character string line of file.
     * @param string $regExTarget  Regular expression target to change.
     * @param string $strToReplace Character string to replace.
     *
     * @return string Replaced character string line.
     */
    private static function _changeModeConstToLiteral($line, $regExTarget, $strToReplace)
    {
        return preg_replace(
            self::$_changeModeConstToLiteralRegEx1 . $regExTarget . self::$_changeModeConstToLiteralRegEx2, //
            "$1/* <BREAKPOINTDEBUGGING_COMMENT> */ $3 $strToReplace $4 // <BREAKPOINTDEBUGGING_COMMENT> $2", //
            $line, //
            1 //
        );
    }

    /**
     * Switches mode.
     *
     * @return void
     */
    static function switchMode()
    {
        // Exits this process if remote server because this process makes overload.
        // Also, reading or writing "PHP" codes may cause sharing violation if remote.
        if (!B::checkDevelopmentSecurity('LOCAL')) {
            return false;
        }

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

        parent::checkExecutionDir($getHtmlFileContent('ProductionSwitcherError'));

        // Cancels the script running time limitation.
        set_time_limit(0);

        // Adds path.
        self::$_blackListPaths[] = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting_InDebug.php';
        BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcher'));
        if (isset($_GET['Switch_to_production']) // 'Switch to production' button was pushed.
            || isset($_GET['Switch_to_development']) // Or, 'Switch to development' button was pushed.
        ) {
            // Opens my setting file.
            $mySettingFilePath = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
            $phpFilePaths = self::_searchPHPFiles();
            $phpFilePaths = array (
                './_ProvingGround.php' => true,
                './BreakpointDebugging_PEAR_Setting/BreakpointDebugging_MySetting.php' => true,
            ); // For debug.
        } else { // In case of first time when this page was called.
            $html = '<h1>ProductionSwitcher</h1>';
            $whiteListPaths = self::_getWhiteListPaths();
            $html .= '<h3>NOTICE: Inside of the following directory is processed about "*.php" files recursively.</h3>';
            $html .= '<ul><span style="color:yellow">';
            foreach ($whiteListPaths as $whiteListPath) {
                $html .= "<li>$whiteListPath</li>";
            }
            $html .= '</span></ul><hr />';
            BW::htmlAddition(__CLASS__, 'body', 0, $html);
            $settingDirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
            // Makes buttons.
            if (BREAKPOINTDEBUGGING_IS_PRODUCTION) {
                parent::makeButton('Switch to development');
                $html = <<<EOD
<ol>
    <li>
        Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" to "{$settingDirName}BreakpointDebugging_MySetting.php" file.<br />
        <span style="color:aqua">
            This mode stops at error location during unit test.<br />
            Next, we can know error location by browser display.<br />
            Next, we can jump to its error location with IDE call stack window.<br />
            Next, we can see variables value of its error location with IDE variable window.<br />
            Or, we can do step execution from error location of unit test file by breakpoint setting and restart.<br />
            So, this is breakpoint debugging.<br />
            Also, this mode displays "XDebug" and error logging information.<br />
        </span>
    </li>
    <li>Strips "/* &lt;BREAKPOINTDEBUGGING_COMMENT&gt; */ - // &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " for restoration.</li>
    <li>Strips "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " for restoration.</li>
</ol>
<hr />
EOD;
            } else {
                parent::makeButton('Switch to production');
                $commentOutAssertionRegEx = self::$_commentOutAssertionRegEx;
                $isDebugRegEx1 = self::$_changeModeConstToLiteralRegEx1 . self::$_isDebugRegEx . self::$_changeModeConstToLiteralRegEx2;
                $breakpointdebuggingIsProductionRegEx = self::$_changeModeConstToLiteralRegEx1 . self::$_breakpointdebuggingIsProductionRegEx . self::$_changeModeConstToLiteralRegEx2;
                $html = <<<EOD
<ol>
    <li>
        Sets "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" to "{$settingDirName}BreakpointDebugging_MySetting.php" file.<br />
        <span style="color:aqua">
            This mode does not display "XDebug" and error logging information for security.<br />
            Also, this mode cannot change execution mode by URL rewriting for security.<br />
            Moreover, increases execution speed.<br />
        </span>
    </li>
    <li>Inserts "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " into "\BreakpointDebugging::assert()" line.</li>
    <li>Inserts "/* &lt;BREAKPOINTDEBUGGING_COMMENT&gt; */ &lt;Insert code&gt; // &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " into "\BreakpointDebugging::isDebug()" and "BREAKPOINTDEBUGGING_IS_PRODUCTION" line.</li>
</ol>
<h4><span style="color: yellow">CAUTION: "/* &lt;BREAKPOINTDEBUGGING_COMMENT&gt; */" line of production code must have "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt;" in same line.</span></h4>
<h4><span style="color: yellow">Therefore, production code must not be formatted.</span></h4>
<hr />
<h3><span style="color:aqua">You must write same pattern code like following if you want change to literal for optimization of parsed code cache.</span></h3>
<ul>
    <li>$commentOutAssertionRegEx</li>
    <li>$isDebugRegEx1</li>
    <li>$breakpointdebuggingIsProductionRegEx</li>
</ul>
<hr />
EOD;
            }
            BW::htmlAddition(__CLASS__, 'body', 0, $html);

            return true;
        }

        // A button was pushed.
        foreach ($phpFilePaths as $phpFilePath => $dummy) {
            // Copies the "*.php" file lines to an array.
            $lines = parent::getArrayFromFile($phpFilePath, $getHtmlFileContent('ProductionSwitcherError'));

            $isChanged = false;
            if (isset($_GET['Switch_to_production'])) { // 'Switch to production' button was pushed.
                // Check comment lines of plural line to skip.
                $linesToSkip = self::_checkCommentLinesOfPluralLineToSkip($phpFilePath, count($lines));
                $lineCount = 0;
                foreach ($lines as &$line) {
                    $lineCount++;
                    if ($linesToSkip[$lineCount]) {
                        continue;
                    }
                    // Inserts "// <BREAKPOINTDEBUGGING_COMMENT> " into "\BreakpointDebugging::assert()" line.
                    $result = parent::commentOut($line, self::$_commentOutAssertionRegEx);
                    if ($result !== $line) {
                        $line = $result;
                        $isChanged = true;
                        continue;
                    }
                    // Inserts "/* <BREAKPOINTDEBUGGING_COMMENT> */ "<new code>" // <BREAKPOINTDEBUGGING_COMMENT> " into "\BreakpointDebugging::isDebug()" line.
                    $result = self::_changeModeConstToLiteral($line, self::$_isDebugRegEx, 'false');
                    B::assert($result !== null);
                    if ($result !== $line) {
                        $line = $result;
                        $isChanged = true;
                        continue;
                    }
                    // Inserts "/* <BREAKPOINTDEBUGGING_COMMENT> */ "<new code>" // <BREAKPOINTDEBUGGING_COMMENT> " into "BREAKPOINTDEBUGGING_IS_PRODUCTION" line.
                    $result = self::_changeModeConstToLiteral($line, self::$_breakpointdebuggingIsProductionRegEx, 'true');
                    B::assert($result !== null);
                    if ($result !== $line) {
                        $line = $result;
                        $isChanged = true;
                        continue;
                    }
                }
            } else { // 'Switch to development' button was pushed.
                parent::stripCommentForRestoration($phpFilePath, $lines, $isChanged, '', '');
            }

            // A button was pushed.
            parent::writeOrSkip($lines, $phpFilePath, __CLASS__, $isChanged);
        }

        // Sets value to "const BREAKPOINTDEBUGGING_IS_PRODUCTION" for mode change.
        while (true) {
            // Copies the "*.php" file lines to an array.
            $lines = parent::getArrayFromFile($mySettingFilePath, $getHtmlFileContent('ProductionSwitcherError'));

            if (isset($_GET['Switch_to_production'])) { // 'Switch to production' button was pushed.
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
                        parent::writeOrSkip($lines, $mySettingFilePath, __CLASS__, true);
                        break 2;
                    }
                }
                // Uses "\BreakpointDebugging_Window" class method for display on production mode if error.
                BW::throwErrorException('You must define "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" in "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".');
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
                        parent::writeOrSkip($lines, $mySettingFilePath, __CLASS__, true);
                        break 2;
                    }
                }
                // Uses "\BreakpointDebugging_Window" class method for display on production mode if error.
                BW::throwErrorException('You must define "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" in "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".');
            }
        }

        // In case of success.
        if (isset($_GET['Switch_to_production'])) { // 'Switch to production' button was pushed.
            $html = <<<EOD
<p style="color: yellow">
    Please, follow out procedure as below.<br />
    Procedure1: Copy project files from local server to remote server.<br />
    Procedure2: Execute "https://&lt;server name&gt;/&lt;project name&gt;/BreakpointDebugging_IniSetOptimizer.php" page in remote server.<br />
</p>
EOD;
            BW::htmlAddition(__CLASS__, 'body', 0, $html);
        }
        BW::htmlAddition(__CLASS__, 'body', 0, '<p style="color: aqua">Switch has been done.</p>');
        BW::scrollBy(__CLASS__, PHP_INT_MAX, PHP_INT_MAX);

        return true;
    }

}

\BreakpointDebugging_ProductionSwitcher::switchMode();
