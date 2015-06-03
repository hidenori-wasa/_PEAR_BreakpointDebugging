<?php

/**
 * This is file for various setting except for release.
 *
 * As for procedure, please, refer to the file level document block of BreakpointDebugging_InDebug.php.
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero on release.
 * These file names put "_" to become error when we do autoload.
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
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

B::limitAccess(BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php');

// ### Item-setting for debugging. ===>
// $xdebugManualUrl = 'http://www.php.net/manual/ja/';
$xdebugVarDisplayMaxChildren = '100';
$xdebugVarDisplayMaxData = '3000';
$xdebugVarDisplayMaxDepth = '6';
// B::setBrowserPass('C:\Program Files\Internet Explorer\iexplore.exe');
// ### <=== Item-setting for debugging.

if (B::getXebugExists()) {
    // xdebug.dump.*    * = COOKIE, FILES, GET, POST, REQUEST, SERVER, SESSION.
    //      Shows the specified superglobal value. Example is shown below.
    //      B::iniSet('xdebug.dump.SERVER', 'REMOTE_ADDR,REQUEST_METHOD');
    if (B::getStatic('$exeMode') & B::REMOTE) { // In case of remote.
        // ### [XDebug] setting in "php.ini" file or ".htaccess" file. ###
        // Comment out following line if you want to emulate remote by local host.
        // B::iniCheck('xdebug.remote_host', array ('127.0.0.1', 'localhost'), 'Sets the \'xdebug.remote_host = "&lt;Remote IDE host of server&gt;"\' of "php.ini file", in other words remote IDE host of server is "&lt;Your host name or IP&gt;".');
    } else { // In case of local.
        // ### [XDebug] setting in "php.ini" file. ###
        B::iniCheck('xdebug.remote_host', '127.0.0.1', 'Set \'xdebug.remote_host = "127.0.0.1"\' of "php.ini" file because remote IDE host of server is "127.0.0.1".');
    }
    //
    // ### [XDebug] setting in "php.ini" file. ###
    // $_get = B::getStatic('$_get');
    // $xdebugSessionName = $_get['XDEBUG_SESSION_START'];
    // B::iniCheck('xdebug.idekey', $xdebugSessionName, 'Set \'xdebug.idekey = "$xdebugSessionName"\' of "php.ini" file because this value must be the debug session name.');
    // Manual base url for links from function traces or error messages.
    // B::iniSet('xdebug.manual_url', $xdebugManualUrl);
    // Limits the number of object properties or array elements for display of var_dump(), local variables or Function Traces.
    B::iniSet('xdebug.var_display_max_children', $xdebugVarDisplayMaxChildren);
    // Limits character string type byte-count for display of var_dump(), local variables or Function Traces.
    B::iniSet('xdebug.var_display_max_data', $xdebugVarDisplayMaxData);
    // Controls how many nested levels of array elements and object properties.
    // Display by var_dump(), local variables or Function Traces.
    B::iniSet('xdebug.var_display_max_depth', $xdebugVarDisplayMaxDepth);
    // Shows function call parameters name and value.
    B::iniSet('xdebug.collect_params', '4');
    // B::iniSet('xdebug.collect_params', '2');
    // Does not gather local variables information for "xdebug_get_declared_vars()".
    B::iniSet('xdebug.collect_vars', '0');
    // Shows stack-traces.
    B::iniSet('xdebug.default_enable', '1');
    // Shows values of superglobals defined by "xdebug.dump.*".
    B::iniSet('xdebug.dump_globals', '1');
    // Dumps superglobals on first error situation.
    B::iniSet('xdebug.dump_once', '1');
    // Does not dump undefined values from superglobals.
    B::iniSet('xdebug.dump_undefined', '0');
    B::iniCheck('xdebug.extended_info', '1', 'Necessary for remote breakpoint-debugging execution.');
    // Max nesting level of function call.
    B::iniSet('xdebug.max_nesting_level', '100');
    B::iniCheck('xdebug.overload_var_dump', '1', 'Overloads var_dump() with its own improved version for displaying variables.');
    // Connects automatically. Therefore, does not use because other human can debug.
    B::iniSet('xdebug.remote_autostart', '0');
    B::iniCheck('xdebug.remote_connect_back', '0', 'Ignores "xdebug.remote_host", then connects by sending client IP. Therefore, does not use because anybody can debug.');
    // Deadline of remote debug by session cookie.
    B::iniSet('xdebug.remote_cookie_expire_time', '3600');
    B::iniCheck('xdebug.remote_enable', '1', 'Set "xdebug.remote_enable = 1" of "php.ini" file because this is needed to do breakpoint debugging if server permits.');
    B::iniCheck('xdebug.remote_handler', 'dbgp', 'Set \'xdebug.remote_handler = "dbgp"\' of "php.ini" file because this is needed to do remote debugging.');
    // Connects when remote debug begins.
    B::iniSet('xdebug.remote_mode', 'req');
    B::iniCheck('xdebug.remote_port', '9000', 'Set "xdebug.remote_port = 9000" of "php.ini" file. This is "NetBeans IDE" port number of own terminal. Also, we use default value because it is the default of "NetBeans IDE".');
    // Enables '@' operator.
    B::iniSet('xdebug.scream', '0');
    // Shows local variables.
    B::iniSet('xdebug.show_local_vars', '1');
}
////////////////////////////////////////////////////////////////////////////////
// ### This uses "false" because this setting doesn't have relation with release. ###
// This makes all errors, warnings and note a stop at breakpoint or a display.
B::iniSet('error_reporting', (string) PHP_INT_MAX);
// This changes "php.ini" file setting into "display_errors = On" to display error, warning and note which isn't done handling by error handler.
B::iniSet('display_errors', '1');
// This changes "php.ini" file setting into "display_startup_errors = On" to display error in case of start-up.
B::iniSet('display_startup_errors', '1');
// In case of debugging, this changes "php.ini" file setting into "log_errors = Off" because this doesn't record log.
B::iniSet('log_errors', '');
// This outputs the message which it is possible to click to lead to the page which explains the function which generated a HTML error.
B::iniSet('html_errors', '1');
B::assert(B::getStatic('$_maxLogFileByteSize') % 4096 === 0);
B::assert(1 <= B::getStatic('$_maxLogParamNestingLevel') && B::getStatic('$_maxLogParamNestingLevel') <= 100);
B::assert(1 <= B::getStatic('$_maxLogElementNumber') && B::getStatic('$_maxLogElementNumber') <= 100);
B::assert(1 <= B::getStatic('$_maxLogStringSize'));
