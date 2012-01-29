<?php

/**
 * This is file for various setting.
 * 
 * As for procedure, please, refer to the file level document block of BreakpointDebugging.php.
 * 
 * PHP version 5.3
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

// Reference path setting.
ini_set('include_path', '.;./PEAR;C:/Program Files/Jcx.Software/VS.Php/2008/'); // In case of local.
// ini_set('include_path', '.:./PEAR'); // In case of remote.

require_once 'BreakpointDebugging.php'; // 'BreakpointDebugging.php' must require_once because it is base of all class, and it sets php.ini, and it sets autoload.

// ### Execution mode setting. ===>
/**
 * @see '### Debug mode constant number ###' of class BreakpointDebugging_InAllCase in BreakpointDebugging.php.
 *       LOCAL_DEBUG
 *       LOCAL_DEBUG_OF_RELEASE
 *       REMOTE_DEBUG
 *       RELEASE
 */
$_BreakpointDebugging_EXE_MODE = B::LOCAL_DEBUG;
// ### <=== Execution mode setting.

// ### Item setting. ===>
// B::$maxLogParamNestingLevel = 20; // Max log parameter nesting level. Default is 20. (1-100)
assert(1 <= B::$maxLogParamNestingLevel && B::$maxLogParamNestingLevel <= 100);
const LANGUAGE = 'Japanese';
const TIMEZONE = 'Asia/Tokyo';
// Warning: When you use existing log, it is destroyed if it is not "UTF-8". It is necessary to be a single character sets.
const PHP_ERROR_LOG_FILE_PATH = './php_error.log';
// The HTTPS web application root directory.
const LOCAL_HTTPS_APP_ROOT = '';
const REMOTE_HTTPS_APP_ROOT = ''; // 'https://???/???/'

// Inner form of the browser of the default: HTML text, character sets = UTF8
header('Content-type: text/html; charset=utf-8');
// Set "mbstring.detect_order = UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP" of "php.ini" file because this is purpose to define default value of character code detection.
$result = mb_detect_order( 'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
assert($result);
// ### <=== Item setting.

////////////////////////////////////////////////////////////////////////////////
// This setting has been Fixed.
if ($_BreakpointDebugging_EXE_MODE & B::RELEASE) { // In case of release.
    // Output it at log to except notice and deprecated.
    ini_set('error_reporting', (string)(PHP_INT_MAX & ~(E_NOTICE | E_DEPRECATED | E_STRICT)));
    // For security, it doesn't display all errors, warnings and notices.
    ini_set('display_errors', '');
    // This changes "php.ini" file setting into "display_startup_errors = Off" Because this makes not display an error on start-up for security.
    ini_set('display_startup_errors', '');
    // This changes "php.ini" file setting into "log_errors = On" to record log for security.
    ini_set('log_errors', '1');
    // This changes "php.ini" file setting into "html_errors=Off" for security because this does not make output link to page which explains function which HTML error occurred.
    ini_set('html_errors', '');
}

////////////////////////////////////////////////////////////////////////////////
// User place folder (Default is empty.)







/*### Example ###
if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) { // In case of remote.
    // PHP It limits directory which opens a file.
    B::iniSet('open_basedir', 'C:\xampp\;.\\'); // '/???/:/???/'
    // Windows e-mail sending server setting.
    B::iniSet('SMTP', 'smtp.example.com'); // 'smtp.???.com'
    // Windows mail address setting.
    B::iniSet('sendmail_from', '?@example.com'); // '???@???.com'
}
// This makes all errors, warnings and note a stop at breakpoint or a display.
B::iniSet('error_reporting', (string)PHP_INT_MAX);
// In case of debugging, this changes "php.ini" file setting into "log_errors = Off" because this doesn't record log.
B::iniSet('log_errors', '');
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
// Set "mbstring.detect_order = auto" of "php.ini" file because this is purpose to define default value of character code detection.
B::iniSet('mbstring.detect_order', 'auto');
// Set "mbstring.strict_detection = Off" of "php.ini" file because this is purpose to not do strict encoding detection.
B::iniSet('mbstring.strict_detection', '');
// This sets "user_agent" to "PHP".
B::iniSet('user_agent', 'PHP');
// This judges an end of a sentence character by the data which was read in "fgets()" and "file()", and we can use "PHP_EOL" constant.
B::iniSet('auto_detect_line_endings', '1');
// Timezone setting.
B::iniSet('date.timezone', TIMEZONE);
// This creates error log file "php_error.log" in "PHP_ERROR_LOG_FILE_PATH" folder.
B::iniSet('error_log', PHP_ERROR_LOG_FILE_PATH);
// Change "php.ini" file setting into "track_errors = Off" because this is not make to insert an error message in direct near "$php_errormsg" variable for security.
B::iniSet('track_errors', '');
// This changes "php.ini" file setting into "arg_separator.output = "&amp;" to be based on XHTML fully.
B::iniSet('arg_separator.output', '&amp;');
// This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
B::iniSet('ignore_user_abort', '');
*/

////////////////////////////////////////////////////////////////////////////////
if (!($_BreakpointDebugging_EXE_MODE & B::RELEASE)) { // In case of not release.
    include_once './BreakpointDebugging_MySetting_Option.php';
}

?>
