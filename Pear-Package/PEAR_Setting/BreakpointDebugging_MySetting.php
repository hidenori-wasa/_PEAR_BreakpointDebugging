<?php

/**
 * This is file for various setting.
 *
 * As for procedure, please, refer to the file level document block of BreakpointDebugging_InDebug.php.
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
use \BreakpointDebugging as B;

/**
 * Sets execution mode.
 *
 * @return void
 */
function BreakpointDebugging_setExecutionMode()
{
    /**
     * @var int Specifies debug mode.
     */
    global $_BreakpointDebugging_EXE_MODE;

    $REMOTE = 1;
    // $UNIT_TEST = 4; // For debug.
    // ### Execution mode setting. ===>
    // Please, choose a mode.
    // $_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags('DEBUG');
    // $_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags('RELEASE');
    $_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags('DEBUG_UNIT_TEST'); // Requires "BreakpointDebugging_PHPUnitStepExecution" package.
    // $_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags('RELEASE_UNIT_TEST'); // Requires "BreakpointDebugging_PHPUnitStepExecution" package.
    // ### <=== Execution mode setting.
    //
    // $_BreakpointDebugging_EXE_MODE |= $REMOTE; // Emulates remote by local host.
    //
    // Reference path setting.
    $includePaths = explode(PATH_SEPARATOR, ini_get('include_path'));
    if ($includePaths[0] !== '.') {
        echo file_get_contents('BreakpointDebugging/css/FontStyle.html', true);
        exit('<pre>"include_path" of "php.ini" must be "." first.</pre>');
    }
    array_unshift($includePaths, $includePaths[0]);
    $includePaths[1] = './PEAR';

    // For debug. ===>
    // if ($_BreakpointDebugging_EXE_MODE & $UNIT_TEST) {
    //     $includePaths = array ('.', './PEAR', './PEAROtherPackage'); // For independence execution check.
    // } else {
    //     $includePaths = array ('.', './PEAR'); // For independence execution check.
    // }
    array_unshift($includePaths, $includePaths[0]);
    $includePaths[1] = './PEAROtherPackage';
    // <=== For debug.

    ini_set('include_path', implode(PATH_SEPARATOR, $includePaths));
}

/**
 * Sets execution mode flags.
 *
 * @param string $executionMode Execution mode.
 *
 * @return void
 */
function BreakpointDebugging_setExecutionModeFlags($executionMode)
{
    /**
     * @see "### Debug mode constant number ###" of class "BreakpointDebugging_InAllCase" in "BreakpointDebugging.php".
     */
    // ### Debug mode number ###
    $REMOTE = 1;
    $RELEASE = 2;
    $UNIT_TEST = 4;

    // In case of direct command call or local "php" page call.
    if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1') { // In case of local.
        switch ($executionMode) {
            case 'RELEASE':
                return $RELEASE; // Local server debug by breakpoint and logging.
            case 'DEBUG':
                return 0; // Local server debug by breakpoint.
            case 'RELEASE_UNIT_TEST':
                return $RELEASE | $UNIT_TEST; // Unit test of release code on local server.
            case 'DEBUG_UNIT_TEST':
                return $UNIT_TEST; // Unit test of debug code on local server.
        }
    } else { // In case of remote.
        switch ($executionMode) {
            case 'RELEASE':
                return $REMOTE | $RELEASE; // Remote server release by logging. We must execute "$_BreakpointDebugging_EXE_MODE = $REMOTE" before this, and we must set on last for security.
            case 'DEBUG':
                return $REMOTE; // Remote server debug by browser display.
            case 'RELEASE_UNIT_TEST':
                return $REMOTE | $RELEASE | $UNIT_TEST; // Unit test of release code on remote server.
            case 'DEBUG_UNIT_TEST':
                return $REMOTE | $UNIT_TEST; // Unit test of debug code on remote server.
        }
    }
    echo file_get_contents('BreakpointDebugging/css/FontStyle.html', true);
    exit(
        '<pre>You must set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'...\');"' . PHP_EOL
        . "\t" . 'into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" because you mistook.' . PHP_EOL
        . ' </pre>'
    );
}

BreakpointDebugging_setExecutionMode();

require_once 'BreakpointDebugging.php'; // 'BreakpointDebugging.php' must require_once because it is base of all class, and it sets php.ini, and it sets autoload.
/**
 * Sets global variables and "php.ini" variables.
 *
 * @return void
 */
function BreakpointDebugging_mySetting()
{
    $REMOTE = 1;

    // ### Item setting. ===>
    $developerIP = &B::refStatic('$_developerIP');
    // Please, enter developer IP address.
    // However, comment out this when running code is local or running code does not use.
    // $developerIP = '219.116.0.54';
    $language = 'Japanese';
    $timezone = 'Asia/Tokyo';
    $SMTP = '<Your SMTP server>';
    $sendmailFrom = '<Your Windows mail address>';
    // Please, set your username.
    $userName = &B::refStatic('$_userName');
    $userName = 'root'; // Example: 'hidenori'
    // PHP It limits directory which opens a file.
    if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // In case of Windows.
        $openBasedir = 'C:\xampp\;.\\;' . sys_get_temp_dir();
    } else { // In case of Unix.
        if (B::getStatic('$exeMode') & $REMOTE) { // In case of remote.
            $openBasedir = '/home/users/2/lolipop.jp-92350a29e84a878a/web/:./:' . sys_get_temp_dir();
        } else { // In case of local.
            $openBasedir = '/opt/lampp/:./:' . sys_get_temp_dir();
        }
    }
    // Maximum log file sum mega byte size. Recommendation size is 1 MB.
    // Log file rotation is from "php_error_1.log" file to "php_error_8.log" file.
    $maxLogMBSize = 1;
    // This code has been fixed.
    $maxLogFileByteSize = &B::refStatic('$_maxLogFileByteSize');
    $maxLogFileByteSize = $maxLogMBSize << 17;
    // Maximum log parameter nesting level. Default is 20. (1-100)
    $maxLogParamNestingLevel = &B::refStatic('$_maxLogParamNestingLevel');
    $maxLogParamNestingLevel = 20;
    // Maximum count of elements in log. (Maximum number of parameter, array elements and call-stack) Default is 50. (1-100)
    $maxLogElementNumber = &B::refStatic('$_maxLogElementNumber');
    $maxLogElementNumber = 50;
    // Maximum string type byte-count of log. Default is 3000. (1-)
    $maxLogStringSize = &B::refStatic('$_maxLogStringSize');
    $maxLogStringSize = 3000;
    // Inner form of the browser of the default: HTML text, character sets = UTF8.
    // header('Content-type: text/html; charset=utf-8');
    // Set "mbstring.detect_order = UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP" of "php.ini" file because this is purpose to define default value of character code detection.
    $result = mb_detect_order('UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
    B::assert($result, 101);
    // This is work directory. "php_error_*.log" file is created in this directory.
    // Warning: When you use existing log, it is destroyed if it is not "UTF-8". It is necessary to be a single character sets.
    $workDir = &B::refStatic('$_workDir');
    $workDir = './Work';
    if (is_dir($workDir)) {
        B::chmod($workDir, 0700);
    } else {
        B::mkdir(array ($workDir, 0700));
    }
    $workDir = realpath($workDir);
    B::assert($workDir !== false, 102);
    // ### <=== Item setting.
    //
    ////////////////////////////////////////////////////////////////////////////////
    // ### User place folder (Default is empty.) ###
    // /* ### "Unix" Example. ===>
    // PHP It limits directory which opens a file.
    B::iniSet('open_basedir', $openBasedir);
    if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
        // Windows e-mail sending server setting.
        B::iniSet('SMTP', $SMTP); // 'smtp.???.com'
        // Windows mail address setting.
        B::iniSet('sendmail_from', $sendmailFrom); // '???@???.com'
    }
    // The default character sets of PHP.
    B::iniSet('default_charset', 'utf8');
    // The default value of language setting (NLS).
    B::iniSet('mbstring.language', $language);
    // Set "mbstring.internal_encoding = utf8" of "php.ini" file because this is purpose to define default value of inner character encoding.
    B::iniSet('mbstring.internal_encoding', 'utf8');
    // Set "mbstring.http_input = auto" of "php.ini" file because this is purpose to define default value of HTTP entry character encoding.
    B::iniSet('mbstring.http_input', 'auto');
    // Set "mbstring.http_output = utf8" of "php.ini" file because this is purpose to define default value of HTTP output character encoding.
    B::iniSet('mbstring.http_output', 'utf8');
    // Set "mbstring.strict_detection = Off" of "php.ini" file because this is purpose to not do strict encoding detection.
    B::iniSet('mbstring.strict_detection', '');
    // this is possible for any value because "mbstring.script_encoding" is unrelated.
    // This is possible for any value because we doesn't use "allow_url_include".
    // This sets "user_agent" to "PHP".
    B::iniSet('user_agent', 'PHP');
    // Set for the debugging because "from" can be set only in "php.ini".
    // This judges an end of a sentence character by the data which was read in "fgets()" and "file()", and we can use "PHP_EOL" constant.
    B::iniSet('auto_detect_line_endings', '1');
    // This changes "php.ini" file setting into "arg_separator.output = "&amp;" to be based on XHTML fully.
    B::iniSet('arg_separator.output', '&amp;');
    // This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
    B::iniSet('ignore_user_abort', '');
    // ### <=== "Unix" Example. */

    /* ### "Windows" Example. ===>
      // PHP It limits directory which opens a file.
      B::iniSet('open_basedir', $openBasedir);
      if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
      // Windows e-mail sending server setting.
      B::iniSet('SMTP', $SMTP); // 'smtp.???.com'
      // Windows mail address setting.
      B::iniSet('sendmail_from', $sendmailFrom); // '???@???.com'
      }
      // The default character sets of PHP.
      B::iniSet('default_charset', 'utf8');
      // The default value of language setting (NLS).
      B::iniSet('mbstring.language', $language);
      // Set "mbstring.internal_encoding = utf8" of "php.ini" file because this is purpose to define default value of inner character encoding.
      B::iniSet('mbstring.internal_encoding', 'utf8');
      // Set "mbstring.http_input = auto" of "php.ini" file because this is purpose to define default value of HTTP entry character encoding.
      B::iniSet('mbstring.http_input', 'auto');
      // Set "mbstring.http_output = utf8" of "php.ini" file because this is purpose to define default value of HTTP output character encoding.
      B::iniSet('mbstring.http_output', 'utf8');
      // Set "mbstring.strict_detection = Off" of "php.ini" file because this is purpose to not do strict encoding detection.
      B::iniSet('mbstring.strict_detection', '');
      // This sets "user_agent" to "PHP".
      B::iniSet('user_agent', 'PHP');
      // Set for the debugging because "from" can be set only in "php.ini".
      // This judges an end of a sentence character by the data which was read in "fgets()" and "file()", and we can use "PHP_EOL" constant.
      B::iniSet('auto_detect_line_endings', '1');
      // This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
      B::iniSet('ignore_user_abort', '');
      ### <=== "Windows" Example. */
    if (!(B::getStatic('$exeMode') & B::RELEASE)) { // In case of debug.
        include_once './' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . '/BreakpointDebugging_MySetting_InDebug.php';
    } else { // In case of release.
        ////////////////////////////////////////////////////////////////////////////////
        // ### This setting has been Fixed. ###
        if (!(B::getStatic('$exeMode') & B::UNIT_TEST)) {
            // Output it at log to except notice and deprecated.
            ini_set('error_reporting', (string) (PHP_INT_MAX & ~(E_NOTICE | E_DEPRECATED | E_STRICT)));
            // This changes "php.ini" file setting into "log_errors = On" to record log for security.
            ini_set('log_errors', '1');
            if (!(B::getStatic('$exeMode') & B::REMOTE)) { // In case of local host.
                return;
            }
            // When "Xdebug" exists.
            if (extension_loaded('xdebug')) {
                B::iniCheck('xdebug.remote_enable', '0', 'Set "xdebug.remote_enable = 0" of "php.ini" file because is for security.');
                // Does not display XDebug information.
                ini_set('xdebug.default_enable', '0');
            }
            // For security, it doesn't display all errors, warnings and notices.
            ini_set('display_errors', '');
            // This changes "php.ini" file setting into "display_startup_errors = Off" Because this makes not display an error on start-up for security.
            ini_set('display_startup_errors', '');
            // This changes "php.ini" file setting into "html_errors=Off" for security because this does not make output link to page which explains function which HTML error occurred.
            ini_set('html_errors', '');
        }
    }
}

BreakpointDebugging_mySetting();

?>
