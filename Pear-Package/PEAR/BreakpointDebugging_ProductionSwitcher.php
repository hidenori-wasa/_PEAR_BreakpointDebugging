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
use BreakpointDebugging_Window as BW;

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
        'BreakpointDebugging/Component/',
        'BreakpointDebugging/PHPUnit/',
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
        './nbproject/',
        './tests/',
    );

    /**
     * Scans directory to search "*.php" files.
     *
     * @param string $fullDirPath  Full directory path for search.
     * @param array  $phpFilePaths "*.php" paths for switching.
     *
     * @return void
     */
    private static function _scandir($fullDirPath, &$phpFilePaths)
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
            'BreakpointDebugging_InDebug.php',
            'BreakpointDebugging_LockByShmopResponse.php',
        );
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
        // Transforms white list to full path.
        $cwd = str_replace('\\', '/', getcwd());
        foreach ($whiteListPaths as $key => &$whiteListPath) {
            $fullPath = stream_resolve_include_path($whiteListPath);
            if ($fullPath === false) {
                throw new \BreakpointDebugging_ErrorException('"' . $whiteListPath . '" is invalid file path.');
            }
            $fullPath = str_replace('\\', '/', $fullPath);
            if (strpos($fullPath, $cwd) === 0) {
                // Skip white list inside of project work directory.
                unset($whiteListPaths[$key]);
                continue;
            }
            $whiteListPath = $fullPath;
        }
        array_unshift($whiteListPaths, $cwd);

        return $whiteListPaths;
    }

    /**
     * Searches "*.php" paths.
     *
     * @return array "*.php" paths for switching.
     * @throws \BreakpointDebugging_ErrorException
     *
     * @codeCoverageIgnore
     */
    private static function _searchPHPFiles()
    {
        $whiteListPaths = self::_getWhiteListPaths();
        // Transforms black list to full path.
        array_unshift(self::$_blackListPaths, rtrim(B::getStatic('$_workDir'), '\/') . '/');
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
        // Locks "*.php" files.
        $pLock = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
        $pLock->lock();

        try {
            BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcher'));
            if (isset($_GET['Switch_to_production']) // 'Switch to production' button was pushed.
                || isset($_GET['Switch_to_development']) // Or, 'Switch to development' button was pushed.
            ) {
                // Opens my setting file.
                $mySettingFilePath = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
                $fullMySettingFilePath = str_replace('\\', '/', stream_resolve_include_path($mySettingFilePath));
                $phpFilePaths = self::_searchPHPFiles();
                $phpFilePaths = array (
                    'C:/xampp/htdocs/CakePHPSamples/app/webroot/BreakpointDebugging_PEAR_Setting/BreakpointDebugging_MySetting.php' => true,
                    'C:/xampp/htdocs/CakePHPSamples/app/webroot/_ProvingGround.php' => true,
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
<hr />
<h3><span style="color:aqua">You must write same pattern code like following if you want change to literal for optimization of parsed code cache.</span></h3>
<ul>
    <li>$commentOutAssertionRegEx</li>
    <li>$isDebugRegEx1</li>
    <li>$breakpointdebuggingIsProductionRegEx</li>
</ul>
<h3><span style="color:orange">Caution: Syntaxes like following have bug in production mode.</span></h3>
<dl>
    <dt>Multiple syntax line is a bug line in case of "\\BreakpointDebugging::assert()".<span style="color:orange">This is not detected as a bug line by IDE.</span></dt>
    <dd>
        <span style="color:fuchsia">\\BreakpointDebugging::assert(\$a); echo('abc');</span><br />
        <span style="color:aqua">Searching by "^[\\t\\x20]*\\\\[\\t\\x20]*BreakpointDebugging[\\t\\x20]*::[\\t\\x20]*assert[\\t\\x20]*\\(" is valid means to this bug.</span>
    </dd>
    <dt>Multiple comment line start is a bug line in case of "\\BreakpointDebugging::assert()". <span style="color:aqua">This is detected by IDE.</span></dt>
    <dd>\\BreakpointDebugging::assert(\$a); /* );</dd>
    <dt><span style="color:orange">"Heredoc" and "Nowdoc" is not detected as a bug line by IDE.</span></dt>
    <dd>\$val = &lt;&lt;&lt;'LABEL'<br />
        <span style="color:fuchsia">
            \\BreakpointDebugging::assert( This is a bug line. );<br />
            if(\\BreakpointDebugging::isDebug() This is a bug line. ){<br />
            if(!\\BreakpointDebugging::isDebug() This is a bug line. ){<br />
            if(BREAKPOINTDEBUGGING_IS_PRODUCTION This is a bug line. ){<br />
            if(!BREAKPOINTDEBUGGING_IS_PRODUCTION This is a bug line. ){<br />
        </span>
        LABEL;<br />
        <span style="color:aqua">Searching by "^[\\t\\x20]*\\\\[\\t\\x20]*BreakpointDebugging[\\t\\x20]*::[\\t\\x20]*assert[\\t\\x20]*\\(" is valid means to this bug.</span><br />
        <span style="color:aqua">Searching by "^[\\t\\x20]*if[\\t\\x20]*\\([\\t\\x20]*!?(\\\\[\\t\\x20]*BreakpointDebugging[\\t\\x20]*::[\\t\\x20]*isDebug[\\t\\x20]*\\(|BREAKPOINTDEBUGGING_IS_PRODUCTION)" is valid means to this bug.</span>
    </dd>
</dl>
<hr />
EOD;
                }
                BW::htmlAddition(__CLASS__, 'body', 0, $html);
                goto END_LABEL;
            }

            // 'Switch to production' button was pushed.
            // Or, 'Switch to development' button was pushed.
            while (true) {
                // Copies the "*.php" file lines to an array.
                $lines = parent::getArrayFromFile($fullMySettingFilePath, $getHtmlFileContent('ProductionSwitcherError'));

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
                            parent::writeOrSkip($lines, $fullMySettingFilePath, __CLASS__, true);
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
                            parent::writeOrSkip($lines, $fullMySettingFilePath, __CLASS__, true);
                            break 2;
                        }
                    }
                    // In case of error.
                    BW::virtualOpen(__CLASS__, $getHtmlFileContent('ProductionSwitcherError'));
                    BW::htmlAddition(__CLASS__, 'body', 0, 'You must define "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" in "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".');
                }
                throw new \BreakpointDebugging_ErrorException('');
            }
            foreach ($phpFilePaths as $phpFilePath => $dummy) {
                // Copies the "*.php" file lines to an array.
                $lines = parent::getArrayFromFile($phpFilePath, $getHtmlFileContent('ProductionSwitcherError'));

                $isChanged = false;
                if (isset($_GET['Switch_to_production'])) { // 'Switch to production' button was pushed.
                    foreach ($lines as &$line) {
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

                // 'Switch to production' button was pushed.
                // Or, 'Switch to development' button was pushed.
                parent::writeOrSkip($lines, $phpFilePath, __CLASS__, $isChanged);
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
