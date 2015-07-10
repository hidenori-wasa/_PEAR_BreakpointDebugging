<?php

/**
 * "\BreakpointDebugging::iniSet()" or "\BreakpointDebugging::iniCheck()" optimizer on production server.
 *
 * LICENSE:
 * Copyright (c) 2015-, Hidenori Wasa
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
 * "\BreakpointDebugging::iniSet()" or "\BreakpointDebugging::iniCheck()" optimizer on production server.
 *
 * PHP version 5.3.2-5.4.x
 *
 * Please, run the following procedure.
 * Procedure1: Change the code to production mode by "./BreakpointDebugging_ProductionSwitcher.php".
 * Procedure2: Display "http://<production server name>/<project name>/BreakpointDebugging_IniSetOptimizer.php" page with browser.
 * Procedure3: Click showing button.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_IniSetOptimizer extends \BreakpointDebugging_Optimizer
{
    /**
     * "\BreakpointDebugging::iniSet()" regular expression for replacement.
     *
     * @var string
     */
    private static $_iniSetRegEx = '`^ ( [[:blank:]]* ) ( \\\\ [[:blank:]]* BreakpointDebugging [[:blank:]]* :: [[:blank:]]* iniSet [[:blank:]]* ( \( .* \) [[:blank:]]* ; [[:blank:]]* (// .*)? ) [\r\n]* ) $`xXU';

    /**
     * "\BreakpointDebugging::iniSet()" and "\BreakpointDebugging::iniCheck()" regular expression to comment out.
     *
     * @var string
     */
    private static $_commentOutRegEx = '`^ ( [[:blank:]]* ) ( \\\\ [[:blank:]]* BreakpointDebugging [[:blank:]]* :: [[:blank:]]* ( iniSet | iniCheck ) [[:blank:]]* \( .* \) [[:blank:]]* ; [[:blank:]]* (// .*)? [\r\n]* ) $`xXU';

    /**
     * Replaces "\BreakpointDebugging::iniSet()" class method to native "ini_set()" function.
     *
     * @param string $line Character string line of file.
     *
     * @return string Replaced character string line.
     */
    private static function _replaceIniSetToNative($line)
    {
        $result = preg_replace(
            self::$_iniSetRegEx, //
            "$1/* <BREAKPOINTDEBUGGING_COMMENT> */ ini_set $3 // <BREAKPOINTDEBUGGING_COMMENT> $2", //
            $line, //
            1 //
        );
        B::assert($result !== null && $result !== $line, 1);

        return $result;
    }

    /**
     * Optimizes "*_MySetting.php" files.
     *
     * @return void
     */
    static function optimize()
    {
        // Checks security.
        if (!B::checkDevelopmentSecurity('REMOTE')) {
            BW::throwErrorException('This page must be executed in remote.');
        }

        $getHtmlFileContent = function ($title) {
            $htmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>$title</title>
    </head>
    <body style="background-color: #333; color: white; font-size: 25px">
    </body>
</html>
EOD;
            return $htmlFileContent;
        };

        if (!BREAKPOINTDEBUGGING_IS_PRODUCTION) {
            BW::throwErrorException('<strong>You must change the code to production mode by "./BreakpointDebugging_ProductionSwitcher.php".</strong>');
        }

        parent::checkExecutionDir($getHtmlFileContent('IniSetOptimizerError'));

        // Cancels the script running time limitation.
        set_time_limit(0);

        BW::virtualOpen(__CLASS__, $getHtmlFileContent('IniSetOptimizer'));
        if (!isset($_GET['Optimize_on_production'])) { // In case of first time when this page was called.
            // Gets "*_MySetting.php" file paths.
            $filesystemIterator = new FilesystemIterator(BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS);
            foreach ($filesystemIterator as $fileinfo) {
                $relativeFilePath = $fileinfo->getPathname();
                if (preg_match('`.* _MySetting.php $`xX', $relativeFilePath) === 1) {
                    $mySettingFilePaths[] = $relativeFilePath;
                }
            }
            foreach ($mySettingFilePaths as $relativeFilePath) {
                // Copies the "*.php" file lines to an array.
                $lines = parent::getArrayFromFile($relativeFilePath, $getHtmlFileContent('IniSetOptimizerError'));
                $isChanged = false;
                // Strips a comment for restoration about "\BreakpointDebugging::iniSet()" and "\BreakpointDebugging::iniCheck()".
                parent::stripCommentForRestoration($relativeFilePath, $lines, $isChanged, '[[:blank:]]* ini_set [[:blank:]]* \(', '\\\\ [[:blank:]]* BreakpointDebugging [[:blank:]]* :: [[:blank:]]* ( iniSet | iniCheck ) [[:blank:]]* \(');
                // Writes result.
                parent::writeOrSkip($lines, $relativeFilePath, __CLASS__, $isChanged);
            }
            // In case of success.
            BW::htmlAddition(__CLASS__, 'body', 0, '<p style="color: aqua">"&lt;BREAKPOINTDEBUGGING_COMMENT&gt;" of "*_MySetting.php" files was stripped about "\BreakpointDebugging::iniSet()" and "\BreakpointDebugging::iniCheck()".</p><hr />');

            $html = '<h1>IniSetOptimizer</h1>';
            $html .= '<h3>NOTICE: Inside of the following directory is processed about "*_MySetting.php" files.</h3>';
            $html .= '<ul><span style="color:yellow">';
            $settingDirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
            $html .= "<li>$settingDirName</li>";
            $html .= '</span></ul><hr />';
            BW::htmlAddition(__CLASS__, 'body', 0, $html);
            // Makes buttons.
            parent::makeButton('Optimize on production');
            $commentOutRegEx = self::$_commentOutRegEx;
            $html = <<<EOD
<ol>
    <li>
        "\BreakpointDebugging::iniSet()" or "\BreakpointDebugging::iniCheck()" optimizer on production server.<br />
        Optimizes "{$settingDirName}*_MySetting.php" files.<br />
    </li>
    <li>Inserts "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " into a line to comment out "\BreakpointDebugging::iniSet()" or "\BreakpointDebugging::iniCheck()".</li>
    <li>Replaces "\BreakpointDebugging::iniSet(..." line to "/* &lt;BREAKPOINTDEBUGGING_COMMENT&gt; */ ini_set(... // &lt;BREAKPOINTDEBUGGING_COMMENT&gt; &lt;native code&gt;".</li>
</ol>
<h4><span style="color: yellow">CAUTION: "/* &lt;BREAKPOINTDEBUGGING_COMMENT&gt; */" line of production code must have "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt;" in same line.</span></h4>
<hr />
<h3><span style="color:aqua">You must write same pattern code like following if you want optimization of parsed code cache.</span></h3>
<ul>
    <li>$commentOutRegEx</li>
</ul>
<hr />
EOD;
            BW::htmlAddition(__CLASS__, 'body', 0, $html);

            // Resets "php.ini" file setting by rerequest.
            return;
        }

        // 'Optimize on production' button was pushed.
        BW::htmlAddition(__CLASS__, 'body', 0, '<p style="color: aqua">"*_MySetting.php" files was checked about "\BreakpointDebugging::iniSet()" and "\BreakpointDebugging::iniCheck()".</p><hr />');

        foreach (parent::$infoToOptimize as $fullFilePath => $lineInfos) {
            // Copies the "*.php" file lines to an array.
            $lines = self::getArrayFromFile($fullFilePath, $getHtmlFileContent('IniSetOptimizerError'));
            $isChanged = false;
            // Processes the registered information.
            foreach ($lineInfos as $lineInfo) {
                $lineNumber = $lineInfo['LINE_NUMBER'];
                $changeKind = $lineInfo['CHANGE_KIND'];
                $line = &$lines[$lineNumber - 1];
                switch ($changeKind) {
                    case 'COMMENT_OUT':
                        // Inserts "// <BREAKPOINTDEBUGGING_COMMENT> " into a line to comment out.
                        $result = parent::commentOut($line, self::$_commentOutRegEx);
                        B::assert($result !== $line);
                        $line = $result;
                        $isChanged = true;
                        break;
                    case 'REPLACE_TO_NATIVE':
                        // Replaces "\BreakpointDebugging::iniSet(..." line to "/* <BREAKPOINTDEBUGGING_COMMENT> */ ini_set(... // <BREAKPOINTDEBUGGING_COMMENT> <native code>".
                        $result = self::_replaceIniSetToNative($line);
                        $line = $result;
                        $isChanged = true;
                        break;
                    default:
                        B::assert(false);
                }
            }
            // 'Optimize on production' button was pushed.
            parent::writeOrSkip($lines, $fullFilePath, __CLASS__, $isChanged);
        }
        // In case of success.
        // 'Optimize on production' button was pushed.
        BW::htmlAddition(__CLASS__, 'body', 0, '<p style="color: aqua">Optimization was done.</p>');
        BW::scrollBy(__CLASS__, PHP_INT_MAX, PHP_INT_MAX);
    }

}

\BreakpointDebugging_IniSetOptimizer::optimize();
