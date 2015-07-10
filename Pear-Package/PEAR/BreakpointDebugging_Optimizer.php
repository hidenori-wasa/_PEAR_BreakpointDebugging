<?php

/**
 * The abstract class for optimization.
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

use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;

/**
 * The abstract class for optimization.
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
class BreakpointDebugging_Optimizer
{
    /**
     * Information to optimize.
     *
     * @var array
     */
    protected static $infoToOptimize;

    /**
     * Strips a comment for restoration.
     *
     * @param string $phpFilePath       The file path of "PHP".
     * @param array  $lines             Character string lines of file.
     * @param bool   $isChanged         Is changed?
     * @param string $regExToReplace    Regular expression to replace.
     * @param string $regExToCommentOut Regular expression to comment out.
     *
     * @return void
     */
    protected static function stripCommentForRestoration($phpFilePath, &$lines, &$isChanged, $regExToReplace, $regExToCommentOut)
    {
        $lineNumber = 0;
        foreach ($lines as &$line) {
            $lineNumber++;
            if (preg_match('`^ [[:blank:]]* /\*\x20<BREAKPOINTDEBUGGING_COMMENT>\x20\*/`xX', $line) === 1) {
                // Checks a line tag.
                if (preg_match('`^ [[:blank:]]* /\*\x20<BREAKPOINTDEBUGGING_COMMENT>\x20\*/ .* //\x20<BREAKPOINTDEBUGGING_COMMENT>\x20 .* $`xX', $line) !== 1) {
                    $errorMessage = <<<EOD
<span style="color: yellow">
"/* &lt;BREAKPOINTDEBUGGING_COMMENT&gt; */" line of production code must have "// &lt;BREAKPOINTDEBUGGING_COMMENT&gt; " in same line.
FILE: $phpFilePath
LINE: $lineNumber</span>
EOD;
                    BW::throwErrorException($errorMessage);
                }
                // Sets "$result" always.
                $result = $line;
                // Strips a comment for restoration.
                // Strips "/* <BREAKPOINTDEBUGGING_COMMENT> */ - // <BREAKPOINTDEBUGGING_COMMENT> " for restoration.
                if (preg_match('`^ [[:blank:]]* /\*\x20<BREAKPOINTDEBUGGING_COMMENT>\x20\*/' . $regExToReplace . '`xX', $line) === 1) {
                    $result = preg_replace(
                        '`^ ( [[:blank:]]* ) /\*\x20<BREAKPOINTDEBUGGING_COMMENT>\x20\*/ .* //\x20<BREAKPOINTDEBUGGING_COMMENT>\x20 ( .* ) $`xX', //
                        '$1$2', //
                        $line, //
                        1 //
                    );
                }
            } else {
                // Strips "// <BREAKPOINTDEBUGGING_COMMENT> " for restoration.
                $result = preg_replace(
                    '`^ ( [[:blank:]]* ) //\x20<BREAKPOINTDEBUGGING_COMMENT>\x20 (' . $regExToCommentOut . '.* ) $`xX', //
                    '$1$2', //
                    $line, //
                    1 //
                );
            }
            B::assert($result !== null);
            if ($result !== $line) {
                $line = $result;
                $isChanged = true;
            }
        }
    }

    /**
     * Sets information to optimize.
     *
     * @param string $fullFilePath Filename to optimize.
     * @param int    $line         The line to optimize.
     * @param string $changeKind   The change kind to optimize.
     */
    static function setInfoToOptimize($fullFilePath, $line, $changeKind)
    {
        self::$infoToOptimize[$fullFilePath][] = array (
            'LINE_NUMBER' => $line,
            'CHANGE_KIND' => $changeKind
        );
    }

    /**
     * Makes a button.
     *
     * @param type $buttonText The button text.
     *
     * @return void
     */
    protected static function makeButton($buttonText)
    {
        $backTrace = debug_backtrace();
        $thisFileName = basename($backTrace[1]['file']);
        $fontStyle = 'style="font-size: 25px; font-weight: bold;"';
        $queryString = B::httpBuildQuery(array ($buttonText => true));
        $html = <<<EOD
<form method="post" action="$thisFileName?$queryString">
    <input type="submit" value="$buttonText" $fontStyle/>
</form>
EOD;
        BW::htmlAddition(substr($thisFileName, 0, -4), 'body', 0, $html);
    }

    /**
     * Checks execution directory.
     *
     * @param string $html HTML for display.
     *
     * @return bool Returns "false" if failure.
     */
    protected static function checkExecutionDir($html)
    {
        // If "CakePHP".
        if (BREAKPOINTDEBUGGING_IS_CAKE) {
            $expectedFilePath = 'app/webroot/' . basename($_SERVER['PHP_SELF']);
            $backTrace = debug_backtrace();
            if (count($backTrace) === 2 // Checks in case of unit-test.
                && preg_match('`/' . $expectedFilePath . '$`xX', $_SERVER['PHP_SELF']) !== 1 // Checks this file path.
            ) {
                $thisFileName = basename($backTrace[1]['file']);
                BW::throwErrorException('"' . $thisFileName . '" must be executed in "' . $expectedFilePath . '".');
            }
        }
    }

    /**
     * Comments out a line to optimize in production server.
     *
     * @param string $line Character string line of file.
     * @param string $regExToCommentOut Regular expression to comment out.
     *
     * @return string Replaced character string line.
     */
    protected static function commentOut($line, $regExToCommentOut)
    {
        $result = preg_replace(
            $regExToCommentOut, //
            '$1// <BREAKPOINTDEBUGGING_COMMENT> $2', //
            $line, //
            1 //
        );
        if ($result === null) { // If error.
            BW::throwErrorException('"preg_replace()" failed.');
        }
        return $result;
    }

    /**
     * Gets array from file.
     *
     * @param string $phpFilePath "*.php" file path to get.
     * @param string $errorHtml   Error HTML to display if error.
     *
     * @return array Gotten lines.
     *
     * @throws \BreakpointDebugging_ErrorException
     */
    protected static function getArrayFromFile($phpFilePath, $errorHtml)
    {
        $pFile = B::fopen(array ($phpFilePath, 'rb'), 0644);
        if ($pFile === false) {
            BW::throwErrorException('"' . $phpFilePath . '" file cannot open.');
        }
        $lines = array ();
        while (($result = fgets($pFile)) !== false) {
            $lines[] = $result;
        }
        // Closes the "*.php" file stream.
        $result = fclose($pFile);
        if ($result !== true) {
            BW::throwErrorException('"' . $phpFilePath . '" file cannot close.');
        }

        return $lines;
    }

    /**
     * Writes or skips a file.
     *
     * @param array  $lines         The lines to write.
     * @param string $fullFilePath  Full file path to process.
     * @param string $thisClassName This class name.
     * @param bool   $isChanged     Is changed?
     *
     * @return void
     */
    protected static function writeOrSkip($lines, $fullFilePath, $thisClassName, $isChanged)
    {
        if ($isChanged) {
            // Displays the progress.
            $newFilePath = BREAKPOINTDEBUGGING_WORK_DIR_NAME . basename($fullFilePath) . '.copy';
            BW::htmlAddition($thisClassName, 'body', 0, 'Renaming "' . $fullFilePath . '" to "' . $newFilePath . '".<br />');
            while (true) {
                // Renames the "*.php" file to "*.php.copy".
                if (rename($fullFilePath, $newFilePath) === false) {
                    // Displays the progress.
                    BW::htmlAddition($thisClassName, 'body', 0, '<span style="color: orange">ERROR: Do not share "' . $fullFilePath . '" file.</span><br />');
                    sleep(5);
                    continue;
                }
                break;
            }
            // Displays the progress.
            BW::htmlAddition($thisClassName, 'body', 0, '<span style="color: red">Writing "' . $fullFilePath . '".</span><br />');
            // Writes the array to "*.php" file.
            B::filePutContents($fullFilePath, $lines, 0644, LOCK_EX);
            // Displays the progress.
            BW::htmlAddition($thisClassName, 'body', 0, 'Deleting "' . $newFilePath . '".<br />');
            // Deletes the "*.php.copy" file.
            B::unlink(array ($newFilePath));
        } else {
            // Displays the progress.
            BW::htmlAddition($thisClassName, 'body', 0, '<span style="color: gray">Skips "' . $fullFilePath . '".</span><br />');
        }
    }

}
