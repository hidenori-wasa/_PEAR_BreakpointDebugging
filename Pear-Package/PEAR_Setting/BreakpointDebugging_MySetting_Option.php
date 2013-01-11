<?php

/**
 * This is file for various setting except for release.
 *
 * As for procedure, please, refer to the file level document block of BreakpointDebugging_Option.php.
 * "*_Option.php" file does not use on release. Therefore, response time is zero on release.
 * These file names put "_" to become error when we do autoload.
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
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

B::limitInvokerFilePaths('./PEAR_Setting/BreakpointDebugging_MySetting.php');

// ### Item-setting for debugging. ===>
// $xdebugManualUrl = 'http://www.php.net/manual/ja/';
$xdebugVarDisplayMaxChildren = '50';
$xdebugVarDisplayMaxData = '3000';
$xdebugVarDisplayMaxDepth = '3';
// B::setBrowserPass('C:\Program Files\Internet Explorer\iexplore.exe');
// ### <=== Item-setting for debugging.
//
// PHP It limits directory which opens a file.
B::iniSet('open_basedir', $openBasedir);
// Caution: "if" statement is needed to copy in case of "B::RELEASE" if copies a code inside "if".
if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) { // In case of remote.
    // Windows e-mail sending server setting.
    B::iniSet('SMTP', $SMTP); // 'smtp.???.com'
    // Windows mail address setting.
    B::iniSet('sendmail_from', $sendmailFrom); // '???@???.com'
} else { // In case of local.
    // "mbstring.func_overload" do coding with 0 for plainness, but release environment is any possibly.
    B::iniCheck('mbstring.func_overload', '0', 'To make coding plain must be set "mbstring.func_overload = 0" of "php.ini" file.');
    B::iniSet('SMTP', $SMTP);
    B::iniSet('sendmail_from', $sendmailFrom);
    // ### [XDebug] setting in "php.ini" file. ###
    B::iniCheck('xdebug.remote_host', '127.0.0.1', 'Set \'xdebug.remote_host = "127.0.0.1"\' of "php.ini" file because remote IDE host of server is "127.0.0.1".');
}

if (B::getXebugExists()) {
    // xdebug.dump.*    * = COOKIE, FILES, GET, POST, REQUEST, SERVER, SESSION.
    //      Shows the specified superglobal value. Example is shown below.
    //      B::iniSet('xdebug.dump.SERVER', 'REMOTE_ADDR,REQUEST_METHOD');
    // if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) { // In case of remote.
    //    // ### [XDebug] setting in "php.ini" file. ###
    //    B::iniCheck('xdebug.remote_host', array ('127.0.0.1', 'localhost'), 'Sets the \'xdebug.remote_host = "&lt;Remote IDE host of server&gt;"\' of "php.ini file", in other words remote IDE host of server is "&lt;Your host name or IP&gt;".');
    // }
    // ### [XDebug] setting in "php.ini" file. ###
    // First is DBGP_IDEKEY, and next is USER, and last is USERNAME.
    // B::iniSet('xdebug.idekey', ?????);
    // // Manual base url for links from function traces or error messages.
    // B::iniSet('xdebug.manual_url', $xdebugManualUrl, false);
    // Limits the number of object properties or array elements for display of var_dump(), local variables or Function Traces.
    B::iniSet('xdebug.var_display_max_children', $xdebugVarDisplayMaxChildren, false);
    // Limits character string type byte-count for display of var_dump(), local variables or Function Traces.
    B::iniSet('xdebug.var_display_max_data', $xdebugVarDisplayMaxData, false);
    // Controls how many nested levels of array elements and object properties.
    // Display by var_dump(), local variables or Function Traces.
    B::iniSet('xdebug.var_display_max_depth', $xdebugVarDisplayMaxDepth, false);
    // Shows function call parameters name and value.
    // B::iniSet('xdebug.collect_params', '4', false);
    B::iniSet('xdebug.collect_params', '2', false);
    // Does not gather local variables information for "xdebug_get_declared_vars()".
    B::iniSet('xdebug.collect_vars', '0', false);
    // Shows stack-traces.
    B::iniSet('xdebug.default_enable', '1', false);
    // Shows values of superglobals defined by "xdebug.dump.*".
    B::iniSet('xdebug.dump_globals', '1', false);
    // Dumps superglobals on first error situation.
    B::iniSet('xdebug.dump_once', '1', false);
    // Does not dump undefined values from superglobals.
    B::iniSet('xdebug.dump_undefined', '0', false);
    // Necessary for remote breakpoint debugging execution.
    B::iniSet('xdebug.extended_info', '1', false);
    // Max nesting level of function call.
    B::iniSet('xdebug.max_nesting_level', '100', false);
    // Overloads var_dump() with its own improved version for displaying variables.
    B::iniSet('xdebug.overload_var_dump', '1', false);
    // Connects automatically. Therefore, does not use because other human can debug.
    B::iniSet('xdebug.remote_autostart', '0', false);
    // Ignores "xdebug.remote_host", then connects by sending client IP. Therefore, does not use because anybody can debug.
    B::iniSet('xdebug.remote_connect_back', '0', false);
    // Deadline of remote debug by session cookie.
    B::iniSet('xdebug.remote_cookie_expire_time', '3600', false);
    B::iniCheck('xdebug.remote_enable', '1', 'Set "xdebug.remote_enable = 1" of "php.ini" file because this is needed to do breakpoint debugging.');
    B::iniCheck('xdebug.remote_handler', 'dbgp', 'Set \'xdebug.remote_handler = "dbgp"\' of "php.ini" file because this is needed to do remote debugging.');
    // Connects when remote debug begins.
    B::iniSet('xdebug.remote_mode', 'req', false);
    B::iniCheck('xdebug.remote_port', '9000', 'Set "xdebug.remote_port = 9000" of "php.ini" file. This is "NetBeans IDE" port number of own terminal. Also, we use default value because it is the default of "NetBeans IDE".');
    // Enables '@' operator.
    B::iniSet('xdebug.scream', '0', false);
    // Shows local variables.
    B::iniSet('xdebug.show_local_vars', '1', false);
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
B::iniSet('date.timezone', $timezone);
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
B::iniCheck('short_open_tag', '1', 'This should change "php.ini" file setting into "short_open_tag = On" because it needs for xampp using "&lt;?" opening tag.');
B::iniCheck('asp_tags', '', 'This should change "php.ini" file setting into "asp_tags = Off" because it can distinguish between other languages by using "&lt;php?" opening tag.');
// This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
B::iniSet('ignore_user_abort', '');
// This changes "php.ini" file setting into "memory_limit = 128M" because it works stably by memory limit setting which can be used with script.
B::iniSet('memory_limit', '128M');
// This changes "php.ini" file setting into "implicit_flush = Off" because it is purpose to prevent a remarkable degradation.
B::iniSet('implicit_flush', '');
B::iniCheck('scream.enabled', '', 'This should change "php.ini" file setting into "scream.enabled = false" because it does not make "@" error display control operator invalid.');
if (B::getOs() === 'WIN') { // In case of Windows.
    B::iniCheck('post_max_size', '128M', 'We recommend to set "post_max_size = 128M" of "php.ini" file because maximum size which is permitted to a POST data is different from the default.');
    B::iniCheck('upload_max_filesize', '128M', 'We recommend to set "upload_max_filesize = 128M" of "php.ini" file because it is "XAMPP" value.');
} else { // In case of Unix.
    B::iniCheck('post_max_size', '8M', 'We recommend to set "post_max_size = 8M" of "php.ini" file because maximum size which is permitted to a POST data is different from the default.');
    B::iniCheck('upload_max_filesize', '2M', 'We recommend to set "upload_max_filesize = 2M" of "php.ini" file because it is "XAMPP" value.');
}
// The SMTP port setting of Windows.
B::iniSet('smtp_port', '25');
// B::iniCheck('mail.add_x_header', '', 'We recommend to set "mail.add_x_header = Off" of "php.ini" file because does not write that header continue "UID" behind the file name.');
////////////////////////////////////////////////////////////////////////////////
// ### This uses "false" because this setting doesn't have relation with release. ###
// This makes all errors, warnings and note a stop at breakpoint or a display.
B::iniSet('error_reporting', (string) PHP_INT_MAX, false);
// This changes "php.ini" file setting into "display_errors = On" to display error, warning and note which isn't done handling by error handler.
B::iniSet('display_errors', '1', false);
// This changes "php.ini" file setting into "display_startup_errors = On" to display error in case of start-up.
B::iniSet('display_startup_errors', '1', false);
// In case of debugging, this changes "php.ini" file setting into "log_errors = Off" because this doesn't record log.
B::iniSet('log_errors', '', false);
// This outputs the message which it is possible to click to lead to the page which explains the function which generated a HTML error.
B::iniSet('html_errors', '1', false);
// // This doesn't make usual error report invalid.
// B::iniCheck( 'xmlrpc_errors', '', 'Please, set "xmlrpc_errors = Off" in "php.ini" file because this does not change usual error report invalidly.');
B::assert(1 <= B::getMaxLogParamNestingLevel() && B::getMaxLogParamNestingLevel() <= 100, 1);
B::assert(1 <= B::getMaxLogElementNumber() && B::getMaxLogElementNumber() <= 100, 2);
B::assert(1 <= B::getMaxLogElementNumber(), 3);

?>
