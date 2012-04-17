<?php

/**
 * This file is code except for release, therefore it does not read in case of release.
 *
 * This reduces load of PHP parser in release mode, then it does speed up.
 * As for procedure, please, refer to the file level document block of BreakpointDebugging.php.
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
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

/**
 * This is function to set breakpoint. You must define this function outside namespace, and you must not change function name.
 * If you don't have breakpoint, you can debug to set '$_BreakpointDebugging_EXE_MODE = B::REMOTE_DEBUG;'.
 *
 * @param string $message       Message
 * @param array  $callStackInfo Call stack info
 *
 * @return void
 */
function BreakpointDebugging_breakpoint($message = '', $callStackInfo = null)
{
    assert(func_num_args() <= 2);
    assert(is_string($message));
    assert(is_array($callStackInfo) || is_null($callStackInfo));

    if ($callStackInfo !== null) {
        $callStackInfo = each($callStackInfo);
        $callStackInfo = $callStackInfo['value'];
        if (array_key_exists('file', $callStackInfo)) {
            $errorFile = $callStackInfo['file'];
        }
        if (array_key_exists('line', $callStackInfo)) {
            $errorLine = $callStackInfo['line'];
        }
    }
    echo ''; // Please, set here breakpoint.
}

// ### Item setting. ===>
// "B::RELEASE" is needed to copy.
if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) { // In case of remote.
    // PHP It limits directory which opens a file.
    B::iniSet('open_basedir', 'C:\xampp\;.\\'); // '/???/:/???/'
    // Windows e-mail sending server setting.
    B::iniSet('SMTP', 'smtp.example.com'); // 'smtp.???.com'
    // Windows mail address setting.
    B::iniSet('sendmail_from', '?@example.com'); // '???@???.com'
    // ### [XDebug] setting in "php.ini" file. ###
    B::iniCheck('xdebug.remote_host', array('127.0.0.1', 'localhost'), 'Set "xdebug.remote_host = "&lt;Remote host name or ip&gt;"" of "php.ini" file because this is needed to do breakpoint debugging.');
} else { // In case of local.
    B::iniSet('open_basedir', 'C:\xampp\;.\\');
    B::iniSet('SMTP', 'smtp.example.com');
    B::iniSet('sendmail_from', '?@example.com');
    // ### [XDebug] setting in "php.ini" file. ###
    B::iniCheck('xdebug.remote_host', '127.0.0.1', 'Set "xdebug.remote_host = "127.0.0.1"" of "php.ini" file because this is needed to do breakpoint debugging.');
}
// ### [XDebug] setting in "php.ini" file. ###
B::iniCheck('xdebug.remote_handler', 'dbgp', 'Set "xdebug.remote_handler = "dbgp"" of "php.ini" file because this is needed to do breakpoint debugging.');
B::iniCheck('xdebug.remote_port', '9000', 'Set "xdebug.remote_port = 9000" of "php.ini" file because this is needed to do breakpoint debugging.');
B::iniCheck('xdebug.remote_enable', '1', 'Set "xdebug.remote_enable = 1" of "php.ini" file because this is needed to do breakpoint debugging.');
// ### <=== Item setting.
////////////////////////////////////////////////////////////////////////////////
// On local.
if ($_BreakpointDebugging_EXE_MODE & B::LOCAL_DEBUG) {
    // "mbstring.func_overload" do coding with 0 for plainness, but release environment is any possibly.
    B::iniCheck('mbstring.func_overload', '0', 'To make coding plain must be set "mbstring.func_overload = 0" of "php.ini" file.');
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ### [mbstring] setting in "php.ini" file. ###
// The default character sets of PHP
B::iniSet('default_charset', 'utf8');
// The default value of language setting (NLS)
B::iniSet('mbstring.language', LANGUAGE);
// Set "mbstring.internal_encoding = utf8" of "php.ini" file because this is purpose to define default value of inner character encoding.
B::iniSet('mbstring.internal_encoding', 'utf8');
// Set "mbstring.http_input = auto" of "php.ini" file because this is purpose to define default value of HTTP entry character encoding.
B::iniSet('mbstring.http_input', 'auto');
// Set "mbstring.http_output = utf8" of "php.ini" file because this is purpose to define default value of HTTP output character encoding.
B::iniSet('mbstring.http_output', 'utf8');
B::iniCheck('mbstring.encoding_translation', array('1'), 'Set "mbstring.encoding_translation = Off" of "php.ini" file because this is purpose not to change a input HTTP query into inner character encoding automatically.');
// Set "mbstring.substitute_character = none" of "php.ini" file because this is purpose to define character ( it does not display ) which substitutes an invalid character.
B::iniSet('mbstring.substitute_character', '');
// Set "mbstring.strict_detection = Off" of "php.ini" file because this is purpose to not do strict encoding detection.
B::iniSet('mbstring.strict_detection', '');
// this is possible for any value because "mbstring.script_encoding" is unrelated.
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
B::iniSet('date.timezone', TIMEZONE);
// This creates error log file "php_error.log" in "PHP_ERROR_LOG_FILE_PATH" folder.
B::iniSet('error_log', PHP_ERROR_LOG_FILE_PATH);
// This sets safe mode invalidly.
B::iniCheck('safe_mode', '', 'This feature has been deprecated in PHP5.3.0. Not to use this feature is strongly recommended generally. Therefore, set "safe_mode = Off" of "php.ini" file.');
// This changes "php.ini" file setting into "report_memleaks = On" because this setting detects a memory leak.
B::iniSet('report_memleaks', '1');
// Change "php.ini" file setting into "track_errors = Off" because this is not make to insert an error message in direct near "$php_errormsg" variable for security.
B::iniSet('track_errors', '');
// This limits a user input that it receive by the super global variable for security.
B::iniCheck('register_globals', '', 'Set "register_globals = Off" of "php.ini" file for security.');
// This doesn't escape user input for execution speed. This escapes with "addslashes()" and "mysqli_real_escape_string()".
B::iniCheck('magic_quotes_gpc', '', 'Set "magic_quotes_gpc = Off" of "php.ini" file for execution speed.');
// This makes not escape for execution speed at time of resource reading. Therefore, this changes "php.ini" file setting into "magic_quotes_runtime = Off".
B::iniSet('magic_quotes_runtime', '');

// This doesn't expose to be using php by server.
// B::iniCheck('expose_php', '', 'This should change "php.ini" file setting into "expose_php = Off" for security.');
// This changes "php.ini" file setting into "arg_separator.output = "&amp;" to be based on XHTML fully.
B::iniSet('arg_separator.output', '&amp;');
//B::iniCheck('short_open_tag', '', 'This should change "php.ini" file setting into "short_open_tag = Off" because it can distinguish between other languages by using "&lt;php?" opening tag.');
B::iniCheck('short_open_tag', '1', 'This should change "php.ini" file setting into "short_open_tag = On" because it needs for xampp using "&lt;?" opening tag.');
B::iniCheck('asp_tags', '', 'This should change "php.ini" file setting into "asp_tags = Off" because it can distinguish between other languages by using "&lt;php?" opening tag.');
// This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
B::iniSet('ignore_user_abort', '');
// This changes "php.ini" file setting into "memory_limit = 128M" because it works stably by memory limit setting which can be used with script.
B::iniSet('memory_limit', '128M');
// This changes "php.ini" file setting into "implicit_flush = Off" because it is purpose to prevent a remarkable degradation.
B::iniSet('implicit_flush', '');
B::iniCheck('scream.enabled', '', 'This should change "php.ini" file setting into "scream.enabled = false" because it does not make "@" error display control operator invalid.');
B::iniCheck('post_max_size', '128M', 'We recommends to set "post_max_size = 128M" of "php.ini" file because maximum size which is permitted to a POST data is different from the default.');
// The SMTP port setting of Windows
B::iniSet('smtp_port', '25');
B::iniCheck('mail.add_x_header', '', 'We recommend to set "mail.add_x_header = Off" of "php.ini" file because does not write that header continue "UID" behind the file name.');
B::iniCheck('upload_max_filesize', '128M', 'We recommend to set "upload_max_filesize = 128M" of "php.ini" file because it is "XAMPP" value.');

////////////////////////////////////////////////////////////////////////////////
// ### This uses "false" because this setting doesn't have relation with release. ###
// This makes all errors, warnings and note a stop at breakpoint or a display.
//$return = ini_set('error_reporting', (string)PHP_INT_MAX);
//assert($return !== false);
B::iniSet('error_reporting', (string) PHP_INT_MAX, false);
// This changes "php.ini" file setting into "display_errors = On" to display error, warning and note which isn't done handling by error handler.
//$return = ini_set('display_errors', '1');
//assert($return !== false);
B::iniSet('display_errors', '1', false);
// This changes "php.ini" file setting into "display_startup_errors = On" to display error in case of start-up.
//$return = ini_set('display_startup_errors', '1');
//assert($return !== false);
B::iniSet('display_startup_errors', '1', false);
// In case of debugging, this changes "php.ini" file setting into "log_errors = Off" because this doesn't record log.
//$return = ini_set('log_errors', '');
//assert($return !== false);
B::iniSet('log_errors', '', false);
// This outputs the message which it is possible to click to lead to the page which explains the function which generated a HTML error.
//$return = ini_set('html_errors', '1');
//assert($return !== false);
B::iniSet('html_errors', '1', false);
// // This doesn't make usual error report invalid.
// B::iniCheck( 'xmlrpc_errors', '', 'Please, set "xmlrpc_errors = Off" in "php.ini" file because this does not change usual error report invalidly.');

assert(1 <= B::$maxLogParamNestingLevel && B::$maxLogParamNestingLevel <= 100);
assert(1 <= B::$maxLogElementNumber && B::$maxLogElementNumber <= 100);
assert(1 <= B::$maxLogStringSize);
?>