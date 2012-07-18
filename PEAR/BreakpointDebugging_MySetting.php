<?php

/**
 * This is file for various setting.
 *
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

// Reference path setting.
if (substr(PHP_OS, 0, 3) === 'WIN') { // In case of Windows.
    ini_set('include_path', '.;./PEAR;C:\xampp\php\PEAR'); // In case of local.
} else if (PHP_OS === 'Linux') { // In case of Linux.
    ini_set('include_path', '.:./PEAR:/opt/lampp/lib/php/PEAR'); // In case of local.
// ini_set('include_path', '.:/opt/lampp/lib/php/PEAR'); // In case of remote.
} else { // In case of other.
    assert(false);
}

require_once 'BreakpointDebugging.php'; // 'BreakpointDebugging.php' must require_once because it is base of all class, and it sets php.ini, and it sets autoload.
// ### Execution mode setting. ===>
/**
 * @see '### Debug mode constant number ###' of class BreakpointDebugging_InAllCase in BreakpointDebugging.php.
 *       B::LOCAL_DEBUG
 *       B::LOCAL_DEBUG_OF_RELEASE
 *       B::REMOTE_DEBUG
 *       B::RELEASE                               // We must execute "REMOTE_DEBUG" before this.
 *       B::LOCAL_DEBUG | B::UNIT_TEST            // Tests by "phpunit".
 *       B::LOCAL_DEBUG_OF_RELEASE | B::UNIT_TEST // Same as "B::LOCAL_DEBUG | B::UNIT_TEST".
 */
$_BreakpointDebugging_EXE_MODE = B::LOCAL_DEBUG;
// $_BreakpointDebugging_EXE_MODE = B::LOCAL_DEBUG_OF_RELEASE;
// $_BreakpointDebugging_EXE_MODE = B::REMOTE_DEBUG;
// $_BreakpointDebugging_EXE_MODE = B::RELEASE;
// $_BreakpointDebugging_EXE_MODE = B::LOCAL_DEBUG | B::UNIT_TEST;
// ### <=== Execution mode setting.
/**
 *
 */
function BreakpointDebugging_mySetting()
{
    global $_BreakpointDebugging_EXE_MODE;

    // ### Item setting. ===>
    $language = 'Japanese';
    $timezone = 'Asia/Tokyo';
    $SMTP = '<Your SMTP server>';
    $sendmailFrom = '<Your Windows mail address>';
    // PHP It limits directory which opens a file.
    if (substr(PHP_OS, 0, 3) === 'WIN') { // In case of Windows.
        $openBasedir = 'C:\xampp\;.\\';
    } else if (PHP_OS === 'Linux') { // In case of Linux.
        $openBasedir = '/opt/lampp/:./';
    } else { // In case of other.
        assert(false);
    }
    // Maximum log parameter nesting level. Default is 20. (1-100)
    // B::$maxLogParamNestingLevel = 20;
    // Maximum count of elements in log. ( Count of parameter or array elements ) Default is 50. (1-100)
    // B::$maxLogElementNumber = 50;
    // Maximum string type byte-count of log. Default is 3000. (1-)
    // B::$maxLogStringSize = 3000;
    // Inner form of the browser of the default: HTML text, character sets = UTF8.
    // header('Content-type: text/html; charset=utf-8');
    // Set "mbstring.detect_order = UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP" of "php.ini" file because this is purpose to define default value of character code detection.
    $result = mb_detect_order('UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
    assert($result);
    // This is work directory. "php_error.log" file is created in this directory.
    // Warning: When you use existing log, it is destroyed if it is not "UTF-8". It is necessary to be a single character sets.
    B::$workDir = './Work';
    if (!is_dir(B::$workDir)) {
        mkdir(B::$workDir, 0700);
    }
    B::$workDir = realpath(B::$workDir);
    assert(B::$workDir !== false);
    // ### <=== Item setting.
    //
    ////////////////////////////////////////////////////////////////////////////////
    // ### User place folder (Default is empty.) ###
    //
    //
    //
    //
    //
    //
    //
    ///* ### Example. ===>
    if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) { // In case of remote.
        if (substr(PHP_OS, 0, 3) === 'WIN') { // In case of Windows.
            // PHP It limits directory which opens a file.
            B::iniSet('open_basedir', 'C:\xampp\;.\\'); // '/???/:/???/'
        } else if (PHP_OS === 'Linux') { // In case of Linux.
            B::iniSet('open_basedir', '/opt/lampp/:./'); // '/???/:/???/'
        } else { // In case of other.
            assert(false);
        }
        // Windows e-mail sending server setting.
        B::iniSet('SMTP', 'smtp.example.com'); // 'smtp.???.com'
        // Windows mail address setting.
        B::iniSet('sendmail_from', '?@example.com'); // '???@???.com'
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
    // This is possible for any value because we doesn't use "allow_url_include".
    // This sets "user_agent" to "PHP".
    B::iniSet('user_agent', 'PHP');
    // Set for the debugging because "from" can be set only in "php.ini".
    // This judges an end of a sentence character by the data which was read in "fgets()" and "file()", and we can use "PHP_EOL" constant.
    B::iniSet('auto_detect_line_endings', '1');
    // This changes "php.ini" file setting into "ignore_user_abort = Off" because it is purpose to end execution of script when client is disconnected.
    B::iniSet('ignore_user_abort', '');
    // Windows e-mail sending server setting.
    B::iniSet('SMTP', $SMTP); // 'smtp.???.com'
    // Windows mail address setting.
    B::iniSet('sendmail_from', $sendmailFrom); // '???@???.com'
    //*/ ### <=== Example.
    if (!($_BreakpointDebugging_EXE_MODE & B::RELEASE)) { // In case of not release.
        include_once './PEAR_Setting/BreakpointDebugging_MySetting_Option.php';
    }
    ////////////////////////////////////////////////////////////////////////////////
    // ### This setting has been Fixed. ###
    if ($_BreakpointDebugging_EXE_MODE & B::RELEASE) { // In case of release.
        // Output it at log to except notice and deprecated.
        B::iniSet('error_reporting', (string) (PHP_INT_MAX & ~(E_NOTICE | E_DEPRECATED | E_STRICT)), false);
        // For security, it doesn't display all errors, warnings and notices.
        B::iniSet('display_errors', '', false);
        // This changes "php.ini" file setting into "display_startup_errors = Off" Because this makes not display an error on start-up for security.
        B::iniSet('display_startup_errors', '', false);
        // This changes "php.ini" file setting into "log_errors = On" to record log for security.
        B::iniSet('log_errors', '1', false);
        // This changes "php.ini" file setting into "html_errors=Off" for security because this does not make output link to page which explains function which HTML error occurred.
        B::iniSet('html_errors', '', false);
    }
////////////////////////////////////////////////////////////////////////////////
//    unset($openBasedir);
//    unset($sendmailFrom);
//    unset($SMTP);
//    unset($result);
//    unset($timezone);
//    unset($language);
}

BreakpointDebugging_mySetting();

?>
