<?php

/**
 * This is file for various setting.
 *
 * As for procedure, please, refer to the file level document block of BreakpointDebugging_InDebug.php.
 *
 * PHP version 5.3.2-5.4.x
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
use \BreakpointDebugging as B;

if (!defined('BREAKPOINTDEBUGGING_MODE')) {
    // ### Please, choose execution mode below. ###
    define('BREAKPOINTDEBUGGING_MODE', 'DEBUG_UNIT_TEST');
    // define('BREAKPOINTDEBUGGING_MODE', 'RELEASE_UNIT_TEST');
    // define('BREAKPOINTDEBUGGING_MODE', 'DEBUG');
    // define('BREAKPOINTDEBUGGING_MODE', 'RELEASE');
}
//
// ### Please, define variable if you emulate remote by local host. ###
// $_BreakpointDebugging_emulate_remote = true;
//
// Is it production server mode?
    const BREAKPOINTDEBUGGING_IS_PRODUCTION = false; // Do not change this line because "\BreakpointDebugging_ProductionSwitcher" class changes this line automatically.

if (preg_match('`^WIN`xXi', PHP_OS)) {
    define('BREAKPOINTDEBUGGING_IS_WINDOWS', true);
} else {
    define('BREAKPOINTDEBUGGING_IS_WINDOWS', false);
}
if (is_file('./WasaCakeTestStart.php')) {
    define('BREAKPOINTDEBUGGING_IS_CAKE', true);
} else {
    define('BREAKPOINTDEBUGGING_IS_CAKE', false);
}

/**
 * Sets execution mode.
 *
 * @return void
 */
function BreakpointDebugging_setExecutionMode()
{
    global $_BreakpointDebugging_EXE_MODE, $_BreakpointDebugging_get, $_BreakpointDebugging_argSeparatorOutput, $_BreakpointDebugging_emulate_remote;

    $_BreakpointDebugging_argSeparatorOutput = '&amp;';

    if (isset($_SERVER['SERVER_ADDR'])) { // In case of common gateway.
        $_BreakpointDebugging_get = $_GET;
    } else { // In case of command line.
        $argc = $_SERVER['argc'];
        $_BreakpointDebugging_get = array ();
        if ($argc > 0) {
            $queryStrings = explode($_BreakpointDebugging_argSeparatorOutput, $_SERVER['argv'][$argc - 1]);
            foreach ($queryStrings as $queryString) {
                list($queryKey, $queryValue) = explode('=', $queryString);
                $_BreakpointDebugging_get[$queryKey] = urldecode($queryValue);
            }
        }
    }

    if (BREAKPOINTDEBUGGING_IS_PRODUCTION === true) { // In case of production server release.
        $_BreakpointDebugging_EXE_MODE = 3; // For HTTP request query string attack counter-plan.
    } else { // In case of development.
        // Checks PHP version.
        if (version_compare(PHP_VERSION, '5.3.2', '<') || version_compare(PHP_VERSION, '5.5', '>=')) {
            exit('<pre>PHP version must be "5.3.2-" or "5.4.x".</pre>');
        }
        if (!BREAKPOINTDEBUGGING_IS_WINDOWS) { // In case of Unix.
            $serverUser = posix_getpwuid(posix_geteuid());
            $osUserName = get_current_user();
            if ($serverUser['name'] !== $osUserName) { // If server user name is not OS user name.
                exit('<pre>' . htmlspecialchars("You must set 'User $osUserName' and 'Group <your group name>' of 'httpd.conf' file.", ENT_QUOTES) . '</pre>');
            }
        }
        $_BreakpointDebugging_EXE_MODE = BreakpointDebugging_getExecutionModeFlags();
        if (isset($_BreakpointDebugging_emulate_remote)) {
            $REMOTE = 1;
            $_BreakpointDebugging_EXE_MODE |= $REMOTE;
        }
    }
    unset($_BreakpointDebugging_emulate_remote);
    // Reference path setting.
    $includePaths = explode(PATH_SEPARATOR, ini_get('include_path'));
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
 * Gets execution mode flags.
 *
 * @return int The flags of execution mode.
 */
function BreakpointDebugging_getExecutionModeFlags()
{
    /**
     * See "### Debug mode constant number ###" of class "BreakpointDebugging_InAllCase" in "BreakpointDebugging.php".
     */
    // ### Debug mode number ###
    $REMOTE = 1;
    $RELEASE = 2;
    $UNIT_TEST = 4;

    // In case of direct command call or local "php" page call.
    if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1') { // In case of local.
        switch (BREAKPOINTDEBUGGING_MODE) {
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
        switch (BREAKPOINTDEBUGGING_MODE) {
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

    exit('<pre><b>Mistaken "BREAKPOINTDEBUGGING_MODE" value.</b></pre>');
}

BreakpointDebugging_setExecutionMode();

require_once 'BreakpointDebugging.php'; // 'BreakpointDebugging.php' must require_once because it is base of all class, and it sets php.ini, and it sets autoload.

\BreakpointDebugging::assert(is_bool(BREAKPOINTDEBUGGING_IS_PRODUCTION));

/**
 * Sets global variables and "php.ini" variables.
 *
 * @return void
 */
function BreakpointDebugging_mySetting()
{
    $exeMode = B::getStatic('$exeMode');
    $REMOTE = 1;

    // ### Please, set item. ===>
    $developerIP = &B::refStatic('$_developerIP');
    // Enter developer IP address for security.
    $developerIP = '127.0.0.1';
    $language = 'Japanese';
    $timezone = 'Asia/Tokyo';
    $SMTP = '<Your SMTP server>';
    $sendmailFrom = '<Your Windows mail address>';
    // PHP It limits directory which opens a file.
    if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // In case of Windows.
        $openBasedir = 'C:\xampp\;.\\;' . sys_get_temp_dir();
    } else { // In case of Unix.
        if ($exeMode & $REMOTE) { // In case of remote.
            $openBasedir = '/usr/local/php5.3/php/:/home/users/2/lolipop.jp-92350a29e84a878a/web/:./:' . sys_get_temp_dir();
            // $openBasedir = '/opt/lampp/:./:' . sys_get_temp_dir(); // Emulates remote by local host.
        } else { // In case of local.
            // $openBasedir = '/opt/lampp/:./:' . sys_get_temp_dir();
            $openBasedir = '/usr/share/php/:./:' . sys_get_temp_dir();
        }
    }
    // Maximum log file sum mega byte size. Recommendation size is 1 MB.
    // Log file rotation is from "php_error_1.log" file to "php_error_8.log" file.
    // $maxLogMBSize = 1;
    // This code has been fixed.
    // $maxLogFileByteSize = &B::refStatic('$_maxLogFileByteSize');
    // $maxLogFileByteSize = $maxLogMBSize << 17;
    // Maximum log parameter nesting level. Default is 20. (1-100)
    // $maxLogParamNestingLevel = &B::refStatic('$_maxLogParamNestingLevel');
    // $maxLogParamNestingLevel = 20;
    // Maximum count of elements in log. (Maximum number of parameter, array elements and call-stack) Default is count($_SERVER). (1-100)
    // $maxLogElementNumber = &B::refStatic('$_maxLogElementNumber');
    // $maxLogElementNumber = count($_SERVER);
    // Maximum string type byte-count of log. Default is 3000. (1-)
    // $maxLogStringSize = &B::refStatic('$_maxLogStringSize');
    // $maxLogStringSize = 3000;
    // Inner form of the browser of the default: HTML text, character sets = UTF8.
    // header('Content-type: text/html; charset=utf-8');
    // Set "mbstring.detect_order = UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP" of "php.ini" file because this is purpose to define default value of character code detection.
    $result = mb_detect_order('UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
    // \BreakpointDebugging::assert($result, 101);
    $workDir = &B::refStatic('$_workDir');
    // We can change work directory name.
    $workDir = './BreakpointDebugging_Work/';
    if (!BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of development.
        if (is_dir($workDir)) {
            B::chmod($workDir, 0700);
        } else {
            B::mkdir(array ($workDir));
        }
        // Copies the "BreakpointDebugging_*.php" file into current work directory.
        B::copyResourceToCWD('BreakpointDebugging_ErrorLogFilesManager.php', '');
        B::copyResourceToCWD('BreakpointDebugging_PHPUnit_DisplayCodeCoverageReport.php', '');
        B::copyResourceToCWD('BreakpointDebugging_ProductionSwitcher.php', '');
    }
    $workDir = realpath($workDir);
    // \BreakpointDebugging::assert($workDir !== false, 102);
    // ### <=== Please, set item.
    //
    ////////////////////////////////////////////////////////////////////////////////
//    // ### User place folder (Default is empty.) ###
//
//    /* ### "Unix" Example. ===>
//      // PHP It limits directory which opens a file.
//      B::iniSet('open_basedir', $openBasedir);
//      if ($exeMode & B::REMOTE) { // In case of remote.
//      // Windows e-mail sending server setting.
//      B::iniSet('SMTP', $SMTP); // 'smtp.???.com'
//      // Windows mail address setting.
//      B::iniSet('sendmail_from', $sendmailFrom); // '???@???.com'
//      }
//      // The default character sets of PHP.
//      B::iniSet('default_charset', 'utf8');
//      // The default value of language setting (NLS).
//      B::iniSet('mbstring.language', $language);
//      // Set "mbstring.internal_encoding = utf8" of "php.ini" file because this is purpose to define default value of inner character encoding.
//      B::iniSet('mbstring.internal_encoding', 'utf8');
//      // Set "mbstring.http_input = auto" of "php.ini" file because this is purpose to define default value of HTTP entry character encoding.
//      B::iniSet('mbstring.http_input', 'auto');
//      // Set "mbstring.http_output = utf8" of "php.ini" file because this is purpose to define default value of HTTP output character encoding.
//      B::iniSet('mbstring.http_output', 'utf8');
//      // Set "mbstring.strict_detection = Off" of "php.ini" file because this is purpose to not do strict encoding detection.
//      B::iniSet('mbstring.strict_detection', '');
//      // This is possible for any value because "mbstring.script_encoding" is unrelated.
//      //
//      // This is possible for any value because we doesn't use "allow_url_include".
//      //
//      // This sets "user_agent" to "PHP".
//      B::iniSet('user_agent', 'PHP');
//      // Set for the debugging because "from" can be set only in "php.ini".
//      // This judges an end of a sentence character by the data which was read in "fgets()" and "file()", and we can use "PHP_EOL" constant.
//      B::iniSet('auto_detect_line_endings', '1');
//      // This changes "php.ini" file setting into "arg_separator.output = "&amp;" to be based on XHTML fully.
//      global $_BreakpointDebugging_argSeparatorOutput;
//      B::iniSet('arg_separator.output', $_BreakpointDebugging_argSeparatorOutput);
//      unset($_BreakpointDebugging_argSeparatorOutput);
//      // This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
//      B::iniSet('ignore_user_abort', '');
//      ### <=== "Unix" Example. */
//
//    // /* ### "Windows" Example. ===>
//    // Timezone setting.
//    B::iniSet('date.timezone', $timezone);
//    // Change "php.ini" file setting into "track_errors = Off" because this is not make to insert an error message in direct near "$php_errormsg" variable for security.
//    B::iniSet('track_errors', '');
//    // This changes "php.ini" file setting into "arg_separator.output = "&amp;" to be based on XHTML fully.
//    B::iniSet('arg_separator.output', '&amp;');
//    // Directory limitation which opens a file.
//    B::iniSet('open_basedir', $openBasedir);
//    if ($exeMode & B::REMOTE) { // In case of remote.
//        // Windows e-mail sending server setting.
//        B::iniSet('SMTP', $SMTP); // 'smtp.???.com'
//        // Windows mail address setting.
//        B::iniSet('sendmail_from', $sendmailFrom); // '???@???.com'
//    }
//    // The default character sets of PHP.
//    B::iniSet('default_charset', 'utf8');
//    // The default value of language setting (NLS).
//    B::iniSet('mbstring.language', $language);
//    // Set "mbstring.internal_encoding = utf8" of "php.ini" file because this is purpose to define default value of inner character encoding.
//    B::iniSet('mbstring.internal_encoding', 'utf8');
//    // Set "mbstring.http_input = auto" of "php.ini" file because this is purpose to define default value of HTTP entry character encoding.
//    B::iniSet('mbstring.http_input', 'auto');
//    // Set "mbstring.http_output = utf8" of "php.ini" file because this is purpose to define default value of HTTP output character encoding.
//    B::iniSet('mbstring.http_output', 'utf8');
//    // Set "mbstring.strict_detection = Off" of "php.ini" file because this is purpose to not do strict encoding detection.
//    B::iniSet('mbstring.strict_detection', '');
//    // This sets "user_agent" to "PHP".
//    B::iniSet('user_agent', 'PHP');
//    // Set for the debugging because "from" can be set only in "php.ini".
//    // This judges an end of a sentence character by the data which was read in "fgets()" and "file()", and we can use "PHP_EOL" constant.
//    B::iniSet('auto_detect_line_endings', '1');
//    // This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
//    B::iniSet('ignore_user_abort', '');
//    // ### <=== "Windows" Example. */
//
    // PHP It limits directory which opens a file.
    B::iniSet('open_basedir', $openBasedir);
    // Caution: "if" statement is needed to copy in case of remote release if copies a code inside "if".
    if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
        // Windows e-mail sending server setting.
        B::iniSet('SMTP', $SMTP); // 'smtp.???.com'
        // Windows mail address setting.
        B::iniSet('sendmail_from', $sendmailFrom); // '???@???.com'
    } else { // In case of local.
        // "mbstring.func_overload" do coding with 0 for plainness, but release environment is any possibly.
        B::iniCheck('mbstring.func_overload', '0', 'To make coding plain must be set "mbstring.func_overload = 0" of "php.ini" file.');
        B::iniSet('SMTP', $SMTP);
        B::iniSet('sendmail_from', $sendmailFrom);
    }
    ////////////////////////////////////////////////////////////////////////////////
    // ### [mbstring] setting in "php.ini" file. ###
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
    B::iniCheck('mbstring.encoding_translation', array ('1'), 'Set "mbstring.encoding_translation = Off" of "php.ini" file because this is purpose not to change a input HTTP query into inner character encoding automatically.');
    // Set "mbstring.substitute_character = none" of "php.ini" file because this is purpose to define character ( it does not display ) which substitutes an invalid character.
    B::iniSet('mbstring.substitute_character', '');
    // Set "mbstring.strict_detection = Off" of "php.ini" file because this is purpose to not do strict encoding detection.
    B::iniSet('mbstring.strict_detection', '');
    // This is possible for any value because "mbstring.script_encoding" is unrelated.
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ### The "Fopen wrappers" setting of "php.ini" file ###
    B::iniCheck('allow_url_fopen', '1', 'Set "allow_url_fopen = On" of "php.ini" file because this is purpose that a file path is made to be able to specify URL by "fopen()" type function.');
    // This is possible for any value because we doesn't use "allow_url_include".
    // This sets "user_agent" to "PHP".
    B::iniSet('user_agent', 'PHP');
    // Set for the debugging because "from" can be set only in "php.ini".
    // This judges an end of a sentence character by the data which was read in "fgets()" and "file()", and we can use "PHP_EOL" constant.
    B::iniSet('auto_detect_line_endings', '1');
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ### "php.ini" the file setting ( This sets a security mainly ). ###
    // Timezone setting.
    B::iniSet('date.timezone', $timezone);
    // This changes "php.ini" file setting into "report_memleaks = On" because this setting detects a memory leak.
    B::iniSet('report_memleaks', '1');
    // Change "php.ini" file setting into "track_errors = Off" because this is not make to insert an error message in direct near "$php_errormsg" variable for security.
    B::iniSet('track_errors', '');
    if (version_compare(PHP_VERSION, '5.4', '<')) {
        // This limits a user input that it receive by the super global variable for security.
        B::iniCheck('register_globals', '', 'Set "register_globals = Off" of "php.ini" file for security.');
        // This doesn't escape user input for execution speed. This escapes with "addslashes()" and "mysqli_real_escape_string()".
        B::iniCheck('magic_quotes_gpc', '', 'Set "magic_quotes_gpc = Off" of "php.ini" file for execution speed.');
        // This makes not escape for execution speed at time of resource reading. Therefore, this changes "php.ini" file setting into "magic_quotes_runtime = Off".
        B::iniSet('magic_quotes_runtime', '');
        // This sets safe mode invalidly.
        B::iniCheck('safe_mode', '', 'This feature has been deprecated in PHP5.3.0. Not to use this feature is strongly recommended generally. Therefore, set "safe_mode = Off" of "php.ini" file.');
    }
    // This doesn't expose to be using php by server.
    // B::iniCheck('expose_php', '', 'This should change "php.ini" file setting into "expose_php = Off" for security.');
    // This changes "php.ini" file setting into "arg_separator.output = "&amp;" to be based on XHTML fully.
    B::iniSet('arg_separator.output', '&amp;');
    B::iniCheck('short_open_tag', '', 'You must change "php.ini" file setting into "short_open_tag = Off" because "BreakpointDebugging_ProductionSwitcher" does not detect "&lt;?" opening tag.');
    B::iniCheck('asp_tags', '', 'This should change "php.ini" file setting into "asp_tags = Off" because it can distinguish between other languages by using "&lt;php?" opening tag.');
    // This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
    B::iniSet('ignore_user_abort', '');
    // This changes "php.ini" file setting into "memory_limit = 128M" because it works stably by memory limit setting which can be used with script.
    B::iniSet('memory_limit', '128M');
    // This changes "php.ini" file setting into "implicit_flush = Off" because it is purpose to prevent a remarkable degradation.
    B::iniSet('implicit_flush', '');
    B::iniCheck('scream.enabled', '', 'This should change "php.ini" file setting into "scream.enabled = false" because it does not make "@" error display control operator invalid.');
    if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // In case of Windows.
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            // Shows crt warnings in case of Windows debug mode.
            ini_set('windows_show_crt_warning', '1');
        }
    }
    // The SMTP port setting of Windows.
    B::iniSet('smtp_port', '25');
    // B::iniCheck('mail.add_x_header', '', 'We recommend to set "mail.add_x_header = Off" of "php.ini" file because does not write that header continue "UID" behind the file name.');
    B::iniCheck('output_buffering', '', 'Sets \'output_buffering = Off\' of "php.ini" file for output window.');
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ### Super global variable filter setting. ###
    B::iniCheck('filter.default', 'unsafe_raw', 'Set \'filter.default = unsafe_raw\' of "php.ini" or ".htaccess" file because of unit test\'s static backup.');
    B::iniCheck('filter.default_flags', '', 'Set \'filter.default_flags = ""\' of "php.ini" or ".htaccess" file because of unit test\'s static backup.');
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // When "Zend OPcache" exists.
    if (extension_loaded('Zend OPcache')) {
        B::iniCheck('opcache.enable_cli', '0', 'Set "opcache.enable_cli = 0" of "php.ini" file because we cannot call CLI from CGI with "popen()".');
        B::iniCheck('opcache.validate_timestamps', '1', 'Set "opcache.validate_timestamps = 1" of "php.ini" file because we must cache modified "*.php" files.');
        //if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of production server release.
        if (!\BreakpointDebugging::isDebug() && ($exeMode & B::REMOTE)) { // In case of release remote mode.
            B::iniCheck('opcache.file_update_protection', array ('0'), 'Do not set "opcache.file_update_protection = 0" of "php.ini" file because production server want to modify a file during execution.' . PHP_EOL . "\t" . 'Recommendation is "opcache.file_update_protection = 2".');
            B::iniCheck('opcache.revalidate_freq', array ('0'), 'Do not set "opcache.revalidate_freq = 0" of "php.ini" file because production server does not want to access a file as much as possible.' . PHP_EOL . "\t" . 'Recommendation is "opcache.revalidate_freq = 60".');
            //} else { // In case of development.
        } else { // Except release remote mode.
            B::iniCheck('opcache.file_update_protection', '0', 'Set "opcache.file_update_protection = 0" of "php.ini" file because we must cache modified "*.php" files.');
            B::iniCheck('opcache.revalidate_freq', '0', 'Set "opcache.revalidate_freq = 0" of "php.ini" file because we must cache modified "*.php" files.');
        }
    }

    //if (!($exeMode & B::RELEASE)) { // In case of debug.
    if (\BreakpointDebugging::isDebug()) { // In case of debug.
        include_once './' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . '/BreakpointDebugging_MySetting_InDebug.php';
    } else { // In case of release.
        ////////////////////////////////////////////////////////////////////////////////
        // ### This setting has been Fixed. ###
        if (!($exeMode & B::UNIT_TEST)) {
            // Output it at log to except notice and deprecated.
            B::iniSet('error_reporting', (string) (PHP_INT_MAX & ~(E_NOTICE | E_DEPRECATED | E_STRICT)));
            // This changes "php.ini" file setting into "log_errors = On" to record log for security.
            B::iniSet('log_errors', '1');
            if (!($exeMode & B::REMOTE)) { // In case of local host.
                return;
            }
            // When "Xdebug" exists.
            if (extension_loaded('xdebug')) {
                B::iniCheck('xdebug.remote_enable', '0', 'Set "xdebug.remote_enable = 0" of "php.ini" file because is for security.');
                // Does not display XDebug information.
                B::iniSet('xdebug.default_enable', '0');
            }
            // For security, it doesn't display all errors, warnings and notices.
            B::iniSet('display_errors', '');
            // This changes "php.ini" file setting into "display_startup_errors = Off" Because this makes not display an error on start-up for security.
            B::iniSet('display_startup_errors', '');
            // This changes "php.ini" file setting into "html_errors=Off" for security because this does not make output link to page which explains function which HTML error occurred.
            B::iniSet('html_errors', '');
            return;
        }
    }
}

BreakpointDebugging_mySetting();
