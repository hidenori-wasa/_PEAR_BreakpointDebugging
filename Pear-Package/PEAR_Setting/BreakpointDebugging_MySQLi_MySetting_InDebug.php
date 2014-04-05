<?php

/**
 * This is file for various setting except for release.
 *
 * As for procedure, please, refer to the file level document block of BreakpointDebugging/MySQLi.php.
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero on release.
 * These file names put "_" to become error when we do autoload.
 *
 * PHP version 5.3.x, 5.4.x
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
 * @package  BreakpointDebugging_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @link     http://pear.php.net/package/BreakpointDebugging/MySQLi
 */
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ### "php.ini" the file setting ( This sets a security mainly ). ###
B::iniCheck('mysqli.max_persistent', '-1', 'This is different from the default. This is recommended to set "php.ini" file to "mysqli.max_persistent = -1".');
B::iniCheck('mysqli.allow_local_infile', '1', 'This is different from the default. This is recommended to set "php.ini" file to "mysqli.allow_local_infile = On".');
B::iniCheck('mysqli.allow_persistent', '1', 'This is different from the default. This is recommended to set "php.ini" file to "mysqli.allow_persistent = On".');
B::iniCheck('mysqli.max_links', '-1', 'This is different from the default. This is recommended to set "php.ini" file to "mysqli.max_links = -1".');
// "mysqli.cache_size" follows it because it is server setting.
// "mysqli.default_port" follows setting of server so as not to catch on fire wall.
// "mysqli.default_socket" follows it because it is server setting.
// "mysqli.default_host" follows it because it is server setting.
// "mysqli.default_user" follows it because it is server setting.
B::iniSet('mysqli.default_pw', ''); // This doesn't use because "mysqli.default_pw" is stolen.
B::iniCheck('mysqli.reconnect', '', 'This is different from the default. This is recommended to set "mysqli.reconnect = Off" inside of "php.ini" file.');

?>
