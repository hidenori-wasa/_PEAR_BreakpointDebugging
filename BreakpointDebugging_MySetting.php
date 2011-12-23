<?php

/**
 * This is file for various setting.
 * 
 * As for procedure, please, refer to the file level document block of BreakpointDebugging.php.
 * 
 * PHP version 5.3
 * 
 * LICENSE:
 * Copyright (c) 2011, Hidenori Wasa
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
 * @author   Hidenori Wasa <hidenori_wasa@yahoo.co.jp>
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
 * 
 * @return void
 */
function BreakpointDebugging_breakpoint()
{
    echo ''; // Please, set here breakpoint.
    // If you don't have breakpoint, you can debug to set '$_BreakpointDebugging_EXE_MODE = B::REMOTE_DEBUG;'.
}

// Reference path setting.
ini_set('include_path', '.;./PEAR;C:/Program Files/Jcx.Software/VS.Php/2008/'); // In case of local.
// ini_set('include_path', '.:./PEAR'); // In case of remote.

require_once 'BreakpointDebugging.php'; // 'BreakpointDebugging.php' must require_once because it is base of all class, and it sets php.ini, and it sets autoload.

// ### Execution mode setting. ===>
/**
 * @see 'Debug mode constant number' of class BreakpointDebugging_For_Debug_And_Release in BreakpointDebugging.php.
 */
$_BreakpointDebugging_EXE_MODE = B::LOCAL_DEBUG;
// ### <=== Execution mode setting.

// ### Item setting. ===>
const LANGUAGE = 'Japanese';
const TIMEZONE = 'Asia/Tokyo';
const PHP_ERROR_LOG_FILE_PATH = './php_error.log';
// The HTTPS web application root directory.
const LOCAL_HTTPS_APP_ROOT = '';
const REMOTE_HTTPS_APP_ROOT = ''; // 'https://???/???/'

// Inner form of the browser of the default: HTML text, character sets = UTF8
header('Content-type: text/html; charset=utf-8');

if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) { // In case of remote.
    // PHP It limits directory which opens a file.
    B::iniSet('open_basedir', 'C:\xampp\;.\\'); // '/???/:/???/'
    // Windows e-mail sending server setting.
    B::iniSet('SMTP', 'smtp.example.com'); // 'smtp.???.com'
    // Windows mail address setting.
    B::iniSet('sendmail_from', '?@example.com'); // '???@???.com'
} else { // In case of local.
    B::iniSet('open_basedir', 'C:\xampp\;.\\');
    B::iniSet('SMTP', 'smtp.example.com');
    B::iniSet('sendmail_from', '?@example.com');
}
// ### <=== Item setting.

////////////////////////////////////////////////////////////////////////////////
if ($_BreakpointDebugging_EXE_MODE & B::RELEASE) { // In case of release.
    // Output it at log to except notice and deprecated.
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    // For security, it doesn't display all errors, warnings and notices.
    B::iniSet('display_errors', '');
    // This changes "php.ini" file setting into "display_startup_errors = Off" Because this makes not display an error on start-up for security.
    B::iniSet('display_startup_errors', '');
    // This changes "php.ini" file setting into "log_errors = On" to record log for security.
    B::iniSet('log_errors', '1');
    // This changes "php.ini" file setting into "html_errors=Off" for security because this does not make output link to page which explains function which HTML error occurred.
    B::iniSet('html_errors', '');
} else { // In case of not release.
    // On local.
    if ($_BreakpointDebugging_EXE_MODE & B::LOCAL_DEBUG) {
        // "mbstring.func_overload" do coding with 0 for plainness, but release environment is any possibly.
        B::iniCheck('mbstring.func_overload', '0', 'To make coding plain must be set "mbstring.func_overload = 0" of "php.ini" file.');
    }
    // This makes all errors, warnings and note a stop at breakpoint or a display.
    error_reporting(-1);
    // This changes "php.ini" file setting into "display_errors = On" to display error, warning and note which isn't done handling by error handler.
    B::iniSet('display_errors', '1');
    // This changes "php.ini" file setting into "display_startup_errors = On" to display error in case of start-up.
    B::iniSet('display_startup_errors', '1');
    // In case of debugging, this changes "php.ini" file setting into "log_errors = Off" because this doesn't record log.
    B::iniSet('log_errors', '');
    // This outputs the message which it is possible to click to lead to the page which explains the function which generated a HTML error.
    B::iniSet('html_errors', '1');
    // // This doesn't make usual error report invalid.
    // B::iniCheck( 'xmlrpc_errors', '', 'Please, set "xmlrpc_errors = Off" in "php.ini" file because this does not change usual error report invalidly.');
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ### [mbstring] setting in "php.ini" file. ###
// The character code specification of regular expression of PHP
mb_regex_encoding('utf8');
// The default character sets of PHP
B::iniSet('default_charset', 'utf8');
// The default value of language setting (NLS)
B::iniSet('mbstring.language', LANGUAGE);
// // The character code in case of internal process of PHP script
// mb_internal_encoding('utf8');
// Set "mbstring.internal_encoding = utf8" of "php.ini" file because this is purpose to define default value of inner character encoding.
B::iniSet('mbstring.internal_encoding', 'utf8');
// Set "mbstring.http_input = auto" of "php.ini" file because this is purpose to define default value of HTTP entry character encoding.
B::iniSet('mbstring.http_input', 'auto');
// Set "mbstring.http_output = utf8" of "php.ini" file because this is purpose to define default value of HTTP output character encoding.
B::iniSet('mbstring.http_output', 'utf8');
B::iniCheck('mbstring.encoding_translation', '', 'Set "mbstring.encoding_translation = Off" of "php.ini" file because this is purpose not to change a input HTTP query into inner character encoding automatically.');
// Set "mbstring.detect_order = auto" of "php.ini" file because this is purpose to define default value of character code detection.
B::iniSet('mbstring.detect_order', 'auto');
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
B::iniCheck('short_open_tag', '', 'This should change "php.ini" file setting into "short_open_tag = Off" because it can distinguish between other languages by using "<php?" opening tag.');
// This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
B::iniSet('ignore_user_abort', '');
// This changes "php.ini" file setting into "memory_limit = 128M" because it works stably by memory limit setting which can be used with script.
B::iniSet('memory_limit', '128M');
// This changes "php.ini" file setting into "implicit_flush = Off" because it is purpose to prevent a remarkable degradation.
B::iniSet('implicit_flush', '');
B::iniCheck('scream.enabled', '', 'This should change "php.ini" file setting into "scream.enabled = false" because it does not make "@" error display control operator invalid.');
B::iniCheck('post_max_size', '8M', 'We recommends to set "post_max_size = 8M" of "php.ini" file because maximum size which is permitted to a POST data is different from the default.');
// The SMTP port setting of Windows
B::iniSet('smtp_port', '25');
B::iniCheck('mail.add_x_header', '', 'We recommend to set "mail.add_x_header = Off" of "php.ini" file because does not write that header continue "UID" behind the file name.');

?>
