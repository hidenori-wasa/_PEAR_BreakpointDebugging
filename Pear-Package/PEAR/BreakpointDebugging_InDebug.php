<?php

/**
 * Class for breakpoint debugging in case of debug mode.
 *
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * ### The basic concept. ###
 * This is package for breakpoint debugging.
 * Also, you can use as basics of other PHP program or PEAR package if you want
 * because this package has necessary minimum features.
 *      It is "php.ini" file setting fixation feature, PHP code synchronization feature,
 *      error log feature by global handler,
 *      the override class feature and execution mode.
 *
 * ### Environment which can do breakpoint debugging. ###
 * Debugger which can use breakpoint.
 * At April, 2013 recommendation debugging environment is
 * "WindowsXP Professional" + "NetBeans IDE 7.1.2" + "XAMPP 1.7.3 (VC++6.0)" or
 * "Ubuntu desktop" + "NetBeans IDE 7.1.2" + "XAMPP for Linux 1.7.3".
 * Notice: Use "phpMyAdmin" to see database and to execute "MySQL" command.
 *         Also, "NetBeans IDE 7.4" cannot keep switchback in format of "if" statement at December, 2013.
 *         However, "NetBeans IDE 7.4" supports "PHP5.4" and "HTML5".
 * @example
 *      if ($a
 *          && $b           // Cannot keep switchback of this line.
 *          && ($c || $d)   // Cannot keep switchback of this line.
 *          || ($e && $f)   // Cannot keep switchback of this line.
 *      ) {
 *          return;
 *      }
 *
 * Caution 1: The code format setting of "NetBeans" disperses to two menu because setting per IDE and setting per project exists.
 *          Those must have both directions link button because setting may not be executed, however, those is not.
 * Caution 2: Do not use "XAMPP 1.7.7 (PHP 5.3.8, VC++9.0)" because execution speed does slowdown in step execution when we run unit test.
 *          Because execution speed did slowdown even though I changed "XAMPP 1.7.7 (PHP 5.3.8, VC++9.0)" to "PHP 5.3.1 (VC++9.0)".
 *          Therefore, "NetBeans" symbolic debugger must use "PHP5.3" which was compiled with "VC++6.0".
 *
 * ### Recommendation file cache extention of production server. ###
 * I recommend "Zend OPcache" extention.
 * Because this extension is stable.
 *      1. My unit tests succeeded with it. Such as calling CLI from CGI with "popen()" function.
 *      2. It is core extension of "PHP5.5".
 *      3. Its development team have several "PHP" coder.
 * Also, this extension is fast.
 *      1. The speed decelerates hardly even if the number of users increases.
 *      2. This extention caches op code after optimization.
 *              As the example, we can code to except debugging code from cache at release.
 *              @example
 *                  if (false) { // Excepts from this line.
 *                      // Debug codes.
 *                          .
 *                          .
 *                          .
 *                  } // Excepts until this line.
 * How to make this Zend extention is same as pecl extention in case of Windows "VC9".
 * However, I cannot make for "PHP 5.3.1" of "XAMPP 1.7.3" is compiled with "VC6".
 * Usage of "php_opcache.dll" file:
 *      1. Move to "C:\xampp\php\ext\php_opcache.dll".
 *      2. Add following lines into "php.ini" file, then save the file.
 *          zend_extension = "C:\xampp\php\ext\php_opcache.dll" ; This line must be before next line.
 *          zend_extension = "...\php_xdebug-....dll"
 *
 *          [Zend OPcache]
 *          opcache.memory_consumption = 128
 *          opcache.interned_strings_buffer = 8
 *          opcache.max_accelerated_files = 4000
 *          opcache.fast_shutdown = 1
 *          ; Constant Value: 0         We cannot call command to get result from CGI because a deprecated prefix is added in case of first time.
 *          opcache.enable_cli = 0
 *          ; Constant Value: 1         Because we must cache modified "*.php" files.
 *          opcache.validate_timestamps = 1
 *          ; Development Value: 0      Because we must cache modified "*.php" files.
 *          ; Production Value: 2       Because production server want to modify a file during execution.
 *          opcache.file_update_protection = 0
 *          ; Development Value: 0      Because we must cache modified "*.php" files.
 *          ; Production Value: 60      Because production server does not want to access a file as much as possible.
 *          opcache.revalidate_freq = 0
 *      3. Restart apache.
 *
 * ### The advantage of breakpoint debugging. ###
 * Can find a position of a bug immediately.
 * In addition to it, we can examine its result by selecting and pointing
 * (variable, function or conditional expression) of code using mouse pointer
 * except variable of "use as" statement,
 * if we check "the watch and the balloon evaluation" of "[tool] - [option] - [PHP] - [debug]" in case of "NetBeans IDE 7.1.2".
 * Also, we have to empty watch variables when its variable does not exist.
 * Therefore, can debug quickly.
 *
 * ### How to code breakpoint debugging. ###
 * We must code as follows to process in "BreakpointDebugging" class.
 * We should verify an impossible "parameters and return value" of
 * "function and method" with "\BreakpointDebugging::assert()".
 * Also, we should verify other impossible values of those.
 * We do not need error and exception handler coding because an error and an exception
 * which wasn't caught are processed by global handler in "BreakpointDebugging" class.
 *
 * @example
 * <?php
 *
 * require_once './BreakpointDebugging_Inclusion.php';
 *
 * use \BreakpointDebugging as B;
 *
 * B::checkExeMode(); // Checks the execution mode.
 *
 * $exeMode = B::getStatic('$exeMode');
 * $logData = 'Data character string.';
 * if ($exeMode & B::RELEASE) { // If release execution mode.
 *      $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(); // Creates a lock instance.
 *      $lockByFlock->lock(); // Locks php-code.
 *      file_put_contents('Somethig.log', $logData);
 *      $lockByFlock->unlock(); // Unlocks php-code.
 * } else { // If debug execution mode.
 *      B::assert(is_string($logData));
 *      echo $logData;
 * }
 *
 * ?>
 *
 * ### Running procedure. ###
 * Please, run the following procedure.
 * Procedure 1: Install "XDebug" by seeing "http://xdebug.org/docs/install"
 *      in case of your local host.
 *      "Xdebug" extension is required because "uses breakpoint,
 *      displays for fatal error and detects infinity recursive function call".
 * Procedure 2: If you want remote debug, set 'xdebug.remote_host =
 *      "<name or ip of host which debugger exists>"' into "php.ini" file, if remote server supports.
 * Procedure 3: Set *.php file format to utf8, but we should create backup of
 *      php files because multibyte strings may be destroyed.
 * Procedure 4: Copy
 *          "BreakpointDebugging_Inclusion.php",
 *          "BreakpointDebugging_ErrorLogFilesManager.php" and
 *          "BreakpointDebugging_PHPUnitStepExecution_DisplayCodeCoverageReport.php" (Requires "BreakpointDebugging_PHPUnitStepExecution" package.)
 *      into your project directory.
 *      And, copy
 *          "BreakpointDebugging_MySetting*.php"
 *      to "const BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME" directory of your project directory.
 * Procedure 5: Edit BreakpointDebugging_MySetting*.php for customize.
 *      Then, it fixes part setting about all execution modes.
 *      Especially, "$_BreakpointDebugging_EXE_MODE = 2;" is important to security.
 * Procedure 6: Copy following in your project php code.
 *      require_once './BreakpointDebugging_Inclusion.php';
 * Procedure 7: Check debugging-mode using "B::checkExeMode()" in start page,
 *      and set
 *          BREAKPOINTDEBUGGING_MODE=DEBUG,
 *          BREAKPOINTDEBUGGING_MODE=RELEASE,
 *          BREAKPOINTDEBUGGING_MODE=DEBUG_UNIT_TEST or     (Requires "BreakpointDebugging_PHPUnitStepExecution" package.)
 *          BREAKPOINTDEBUGGING_MODE=RELEASE_UNIT_TEST      (Requires "BreakpointDebugging_PHPUnitStepExecution" package.)
 *      to your project parameter setting.
 *      Then, use "B::getStatic('$exeMode')" to get value.
 *      Lastly, we should execute all codes using
 *          "\BreakpointDebugging_PHPUnitStepExecution::executeUnitTest()" and
 *          "\BreakpointDebugging_PHPUnitStepExecution::displayCodeCoverageReport()"
 *      of "BreakpointDebugging_PHPUnitStepExecution" package before release.
 *      Then, we must enable "$_BreakpointDebugging_EXE_MODE = 2;" of "BreakpointDebugging_MySetting.php" in case of production server use.
 *      Because server must not display "XDebug" and error logging information.
 * Procedure 8: If you use "XAMPP for Linux",
 *      Make the "SetPermission.sh" file to change "owner and access permission" of all directories and files below "htdocs".
 *      Then, change its property as executable program.
 *      Attention: Write your user name into <user name>.
 *          #!/bin/bash
 *
 *          # This file is purpose which sets permission before upload.
 *          # Each server permission is different, therefore you must customize this file to adjust.
 *
 *          # Changes owner of all directories and files.
 *          sudo chown <user name> /opt/lampp/htdocs/ -R
 *          # Changes permission of all directories inside "htdocs".
 *          sudo find /opt/lampp/htdocs/ -type d -exec sudo chmod 0705 {} \;
 *          # Changes permission of all files inside "htdocs".
 *          sudo find /opt/lampp/htdocs/ -type f -regex ".*" -exec sudo chmod 0604 {} \;
 *          # Notifies end.
 *          echo "Permission was set."
 *          # Waits until input.
 *          read Wait
 *
 *      Do double click "SetPermission.sh" file to change "owner and access permission" with of all directories and files below "htdocs".
 *
 *      Add execution directory path of "XAMPP" to environment pass by adding to end line of "envvars" file.
 *          sudo gedit /opt/lampp/bin/envvars
 *              export PATH=$PATH:/opt/lampp/bin
 *
 *      Change user name and access permission.
 *      Attention: Write your user name into <user name>.
 *          sudo gedit /opt/lampp/etc/httpd.conf
 *              #User nobody
 *              User <user name>
 *              #Group nogroup
 *              Group <user name>
 *
 *              <Directory "/opt/lampp/htdocs">
 *                      .
 *                      .
 *                      .
 *                  #Order allow,deny
 *                  Order deny,allow
 *                  #Allow from all
 *                  deny from all
 *                  Allow from 127.0.0.1 localhost
 *
 *              <Directory "/opt/lampp/cgi-bin">
 *                      .
 *                      .
 *                      .
 *                  #Order allow,deny
 *                  Order deny,allow
 *                  #Allow from all
 *                  deny from all
 *                  Allow from 127.0.0.1 localhost
 * Procedure 9: If you can change "php.ini" file,
 *      use "B::iniCheck()" instead of "B::iniSet()" in "*_MySetting.php" file,
 *      and move it to "*_MySetting_InDebug.php" file
 *      because decreases the read and the parse bytes.
 *      Also, use "B::iniCheck()" instead of "B::iniSet()"
 *      in "*_MySetting_InDebug.php" file.
 *
 * Caution: Do not execute "ini_set('error_log', ...)" because
 * this package uses local log rotation instead of system log.
 *
 * Option procedure: Register at top of the function or method or file
 *      which has been not fixed. Copy following.
 *      "\BreakpointDebugging::registerNotFixedLocation(self::$_isRegister[__METHOD__]);"
 *      Then, we can discern function or method or file
 *      which has been not fixed with browser screen or log.
 * Option procedure: Register local variable or global variable
 *      which you want to see with "\BreakpointDebugging::addValuesToTrace()".
 *
 * ### Exception hierarchical structure ###
 *  PEAR_Exception
 *      BreakpointDebugging_Exception_InAllCase
 *          BreakpointDebugging_Exception
 *              BreakpointDebugging_ErrorException
 *              BreakpointDebugging_OutOfLogRangeException
 *
 * ### Useful class index. ###
 * This class override a class without inheritance, but only public member can be inherited.
 *      class BreakpointDebugging_OverrideClass
 * Class which locks php-code by file existing.
 *      class BreakpointDebugging_LockByFileExisting
 * Class which locks php-code by shared memory operation.
 *      class BreakpointDebugging_LockByShmop
 * Class which locks php-code by "flock()".
 *      class BreakpointDebugging_LockByFlock
 *
 * My viewpoint about PHP-types for reading my PHP code.
 *      ### About PHP-types structure.
 *      Any type of PHP has ID which has pointer which specifies movable memory of "type and value".
 *      Its movable memory has "reference count" and "flag which means a reference".
 *
 *      ### About variable copy.
 *      Variable copy is ID copy, and it increments reference count of movable memory.
 *      And, object type copy is ID copy too. However, object type is not scalar type.
 *      Therefore, we must use "$cloneObject = clone $object;" if we want object internal copy.
 *      And, array type copy is its elements ID copy. However, element of reference specifies same ID area.
 *      Therefore, copied element value may be changed if original copy element is changed even though we copied array type.
 *
 *      ### About variable reference copy.
 *      Variable reference copy specifies same ID area, and it increments reference count of movable memory, and checks "flag which means a reference".
 *
 *      ### About "unset()" function.
 *      "unset()" function decrements reference count.
 *      Then, memory area which is pointed is deleted if reference count became 0.
 *
 *      ### About "__destruct()" class method call.
 *      "__destruct()" class method is called if we overwrite null value to variable because value of all reference is disabled.
 *      However, memory area and reference count is kept.
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
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;
use \BreakpointDebugging_InAllCase as BA;

/**
 * This class executes error or exception handling, and it is except release mode.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging extends \BreakpointDebugging_InAllCase
{
    /**
     * @var array The class method call locations.
     */
    private static $_callLocations = array ();

    /**
     * @var array Setting option filenames.
     */
    private static $_onceFlag = array ();

    /**
     * @var string Include-paths.
     */
    private static $_includePaths;

    /**
     * Limits static properties accessing.
     *
     * @return void
     */
    static function initialize()
    {
        B::limitAccess('BreakpointDebugging.php');

        B::assert(func_num_args() === 0);

        parent::initialize();

        parent::$staticProperties['$_includePaths'] = &self::$_includePaths;
        parent::$staticPropertyLimitings['$exeMode'] = 'BreakpointDebugging_PHPUnitStepExecution.php';
        $tmp = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
        parent::$staticPropertyLimitings['$_userName'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogFileByteSize'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogParamNestingLevel'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogElementNumber'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogStringSize'] = $tmp;
        parent::$staticPropertyLimitings['$_workDir'] = $tmp;
        parent::$staticPropertyLimitings['$_developerIP'] = $tmp;
        parent::$staticPropertyLimitings['$_onceErrorDispFlag'] = 'BreakpointDebugging/PHPUnitStepExecution/PHPUnitFrameworkTestCase.php';
        parent::$staticPropertyLimitings['$_callingExceptionHandlerDirectly'] = array (
            'BreakpointDebugging/Error.php',
            'BreakpointDebugging/PHPUnitStepExecution/PHPUnitFrameworkTestCase.php'
        );
    }

    /**
     * If "Apache HTTP Server" does not support "suEXEC", this method displays security warning.
     *
     * @return void
     */
    static function checkSuperUserExecution()
    {
        // If this is remote debug, unix and root user.
        if (BA::$exeMode === B::REMOTE
            && !BREAKPOINTDEBUGGING_IS_WINDOWS
            && trim(`echo \$USER`) === 'root'
        ) {
            parent::windowVirtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileContent());
            B::windowHtmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, 'Security warning: Recommends to change to "Apache HTTP Server" which Supported "suEXEC" because this "Apache HTTP Server" is executed by "root" user.');
        }
    }

    /**
     * For debug.
     *
     * @param string $propertyName Same as parent.
     *
     * @return Same as parent.
     */
    static function getStatic($propertyName)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_string($propertyName));

        return parent::getStatic($propertyName);
    }

    /**
     * Gets a static property reference.
     *
     * @param string $propertyName Static property name.
     *
     * @return mixed& Static property.
     */
    static function &refStatic($propertyName)
    {
        self::limitAccess(parent::$staticPropertyLimitings[$propertyName]);

        self::assert(func_num_args() === 1);
        self::assert(is_string($propertyName));

        return parent::refStatic($propertyName);
    }

    /**
     * For debug.
     *
     * @return Same as parent.
     */
    static function getXebugExists()
    {
        self::assert(func_num_args() === 0);

        return parent::getXebugExists();
    }

    /**
     * For debug.
     *
     * @param bool $value Same as parent.
     *
     * @return Same as parent.
     */
    static function setXebugExists($value)
    {
        self::limitAccess('BreakpointDebugging.php');

        self::assert(func_num_args() === 1);
        self::assert(is_bool($value));

        parent::setXebugExists($value);
    }

    /**
     * For debug.
     *
     * @param string $phpIniVariable Same as parent.
     * @param mixed  $cmpValue       Same as parent.
     * @param string $errorMessage   Same as parent.
     *
     * @return Same as parent.
     */
    static function iniCheck($phpIniVariable, $cmpValue, $errorMessage)
    {
        self::assert(func_num_args() === 3);
        self::assert(is_string($phpIniVariable));
        self::assert(is_string($cmpValue) || is_array($cmpValue));
        self::assert(is_string($errorMessage));

        parent::iniCheck($phpIniVariable, $cmpValue, $errorMessage);
    }

    /**
     * For debug.
     *
     * @param string $string Same as parent.
     *
     * @return Same as parent.
     */
    static function convertMbString($string)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_string($string));

        return parent::convertMbString($string);
    }

    /**
     * For debug.
     *
     * @param string $name              Same as parent.
     * @param int    $permission        Same as parent.
     * @param int    $timeout           Same as parent.
     * @param int    $sleepMicroSeconds Same as parent.
     *
     * @return Same as parent.
     */
    static function chmod($name, $permission, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        self::assert(func_num_args() <= 4);
        self::assert(is_string($name));
        self::assert(is_int($permission));
        self::assert(is_int($timeout));
        self::assert(is_int($sleepMicroSeconds));

        return parent::chmod($name, $permission, $timeout, $sleepMicroSeconds);
    }

    /**
     * For debug.
     *
     * @param array $params            Same as parent.
     * @param int   $timeout           Same as parent.
     * @param int   $sleepMicroSeconds Same as parent.
     *
     * @return Same as parent.
     */
    static function mkdir(array $params, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        self::assert(func_num_args() <= 3);
        self::assert(is_int($timeout));
        self::assert(is_int($sleepMicroSeconds));

        return parent::mkdir($params, $timeout, $sleepMicroSeconds);
    }

    /**
     * For debug.
     *
     * @param array $params            Same as parent.
     * @param int   $permission        Same as parent.
     * @param int   $timeout           Same as parent.
     * @param int   $sleepMicroSeconds Same as parent.
     *
     * @example $pFile = B::fopen(array ($filePath, 'w+b'));
     *
     * @return Same as parent.
     */
    static function fopen(array $params, $permission = null, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        self::assert(func_num_args() <= 4);
        self::assert((is_int($permission) || is_null($permission)) && 0 <= $permission && $permission <= 0777);
        self::assert(is_int($timeout));
        self::assert(is_int($sleepMicroSeconds));

        return parent::fopen($params, $permission, $timeout, $sleepMicroSeconds);
    }

    /**
     * For debug.
     *
     * @param array $intArray Same as parent.
     *
     * @return Same as parent.
     */
    static function compressIntArray($intArray)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_array($intArray));

        return parent::compressIntArray($intArray);
    }

    /**
     * For debug.
     *
     * @param mixed $compressBytes Same as parent.
     *
     * @return Same as parent.
     */
    static function decompressIntArray($compressBytes)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_string($compressBytes) || $compressBytes === false);

        return parent::decompressIntArray($compressBytes);
    }

    /**
     * For debug.
     *
     * @param object $pException Same as parent.
     *
     * @return Same as parent.
     */
    static function handleException($pException)
    {
        self::assert(func_num_args() === 1);
        self::assert($pException instanceof \Exception);

        if (BA::$exeMode & B::UNIT_TEST) {
            \BreakpointDebugging_PHPUnitStepExecution::handleUnitTestException($pException);
        }

        parent::handleException($pException);
    }

    /**
     * For debug.
     *
     * @param int    $errorNumber  Same as parent.
     * @param string $errorMessage Same as parent.
     *
     * @return Same as parent.
     */
    static function handleError($errorNumber, $errorMessage)
    {
        self::assert(is_int($errorNumber));
        self::assert(is_string($errorMessage));

        return parent::handleError($errorNumber, $errorMessage);
    }

    /**
     * Checks path environment variable for "php" command.
     *
     * @return void
     */
    static function checkPathEnvironmentVariable()
    {
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            $paths = getenv('path');
            $paths = explode(';', $paths);
            while (true) {
                foreach ($paths as $path) {
                    $path = rtrim($path, '\/');
                    if (is_file($path . '/php.exe')) {
                        break 2;
                    }
                }
                parent::windowVirtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileContent());
                B::windowHtmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, 'Path environment variable has not been set for "php.exe" command.' . PHP_EOL . `path`);
                exit;
            }
        } else {
            $result = `which php`;
            if (empty($result)) {
                $message = 'Path environment variable has not been set for "php" command.' . PHP_EOL
                    . '$PATH=' . `echo \$PATH` . PHP_EOL
                    . 'Please, search by (sudo find "<apache install directory>" -mount -name "envvars") command.' . PHP_EOL
                    . '    Example: sudo find "/opt/lampp/" -mount -name "envvars"' . PHP_EOL
                    . 'Then, add "export PATH=$PATH:<php command directory>" line to its file.' . PHP_EOL
                    . '    Example: export PATH=$PATH:/opt/lampp/bin' . PHP_EOL
                    . 'Then, restart apache.';
                parent::windowVirtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileContent());
                B::windowHtmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, htmlspecialchars($message, ENT_COMPAT));
                exit;
            }
        }
    }

    ///////////////////////////// For package user from here in case of debug mode. /////////////////////////////
    /**
     * Checks a invoker file path.
     *
     * @param array  $includePaths    The including paths.
     * @param string $invokerFilePath Invoker file path.
     * @param string $fullFilePath    A full file path.
     *
     * @return boolean
     */
    private static function _checkInvokerFilePath($includePaths, $invokerFilePath, $fullFilePath)
    {
        B::assert(func_num_args() === 3);
        B::assert(is_array($includePaths));
        B::assert(is_string($invokerFilePath));
        B::assert(is_string($fullFilePath));

        foreach ($includePaths as $includePath) {
            $invokerFullFilePath = realpath("$includePath/$invokerFilePath");
            if ($invokerFullFilePath === false) {
                continue;
            }
            if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
                $invokerFullFilePath = strtolower($invokerFullFilePath);
            }
            if ($fullFilePath === $invokerFullFilePath) {
                return true;
            }
        }
        return false;
    }

    /**
     * Limits the invoker file paths.
     *
     * @param mixed $invokerFilePaths Invoker file paths.
     * @param bool  $enableUnitTest   Is this enable in unit test?
     *
     * @return void
     */
    static function limitAccess($invokerFilePaths, $enableUnitTest = false)
    {
        $callStack = debug_backtrace();
        // Makes invoking location information.
        $count = count($callStack);
        if ($count === 1) {
            // @codeCoverageIgnoreStart
            // Because unit test file is not top page.
            // Skips top page.
            return;
            // @codeCoverageIgnoreEnd
        }
        do {
            for ($key = 1; $key < $count; $key++) {
                if (array_key_exists('file', $callStack[$key])) {
                    break 2;
                }
                // @codeCoverageIgnoreStart
                // Because unit test cannot run "call_user_func_array()" as global code.
            }
            // Skips when "file" key does not exist.
            return;
            // @codeCoverageIgnoreEnd
        } while (false);
        $fullFilePath = $callStack[$key]['file'];
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            $fullFilePath = strtolower($fullFilePath);
        }
        $line = $callStack[$key]['line'];
        if (array_key_exists($fullFilePath, self::$_callLocations)
            && array_key_exists($line, self::$_callLocations[$fullFilePath])
        ) {
            // Skips same.
            return;
        }
        // Stores the invoking location information.
        self::$_callLocations[$fullFilePath][$line] = true;

        self::assert(func_num_args() <= 2);
        self::assert(is_array($invokerFilePaths) || is_string($invokerFilePaths));
        self::assert(is_bool($enableUnitTest));

        if (!$enableUnitTest
            && (BA::$exeMode & B::UNIT_TEST)
            && (!isset(\BreakpointDebugging_PHPUnitStepExecution::$unitTestDir) || strpos($fullFilePath, \BreakpointDebugging_PHPUnitStepExecution::$unitTestDir) === 0)
        ) {
            return;
        }

        if (!isset(self::$_includePaths)) {
            self::$_includePaths = ini_get('include_path');
            if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
                self::$_includePaths = strtolower(self::$_includePaths);
            }
            self::$_includePaths = explode(PATH_SEPARATOR, self::$_includePaths);
        }
        if (is_array($invokerFilePaths)) {
            foreach ($invokerFilePaths as $invokerFilePath) {
                if (self::_checkInvokerFilePath(self::$_includePaths, $invokerFilePath, $fullFilePath)) {
                    return;
                }
            }
            // @codeCoverageIgnoreStart
        } else {
            // @codeCoverageIgnoreEnd
            if (self::_checkInvokerFilePath(self::$_includePaths, $invokerFilePaths, $fullFilePath)) {
                return;
            }
        }
        $class = '';
        $function = '';
        if (array_key_exists('class', $callStack[$key])) {
            $class = $callStack[$key]['class'] . '::';
        }
        if (array_key_exists('function', $callStack[$key])) {
            $function = $callStack[$key]['function'];
        }
        self::callExceptionHandlerDirectly("'$class$function()' must not invoke in '$fullFilePath' file.", 4);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * Throws exception if assertion is false. Also, has identification code for debug unit test.
     *
     * @param bool $assertion Assertion.
     * @param int  $id        Exception identification number inside function.
     *                        I recommend from 0 to 99 if you do not detect by unit test.
     *                        I recommend from 100 if you detect by unit test.
     *                        This number must not overlap with other assertion or exception identification number inside function.
     *
     * @return void
     * @usage
     *      \BreakpointDebugging::assert(<judgment expression>[, <identification number inside function>]);
     *      It is possible to assert that <judgment expression> is "This must be". Especially, this uses to verify a function's argument.
     *      @example: \BreakpointDebugging::assert(3 <= $value && $value <= 5); // $value should be 3-5.
     *      Caution: Don't change the value of variable in "\BreakpointDebugging::assert()" function because there isn't executed in case of release.
     */
    static function assert($assertion, $id = null)
    {
        $paramNumber = func_num_args();
        if ($paramNumber > 2) {
            self::callExceptionHandlerDirectly('Parameter number mistake.', 1);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!is_bool($assertion)) {
            self::callExceptionHandlerDirectly('Assertion must be bool.', 2);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!is_int($id)
            && !is_null($id)
        ) {
            self::callExceptionHandlerDirectly('Exception identification number must be integer.', 3);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!$assertion) {
            if ($paramNumber === 1) {
                // For breakpoint debugging.
                parent::breakpoint('Assertion failed.', debug_backtrace());
            }
            // For "@expectedExceptionMessage" annotation of "DEBUG_UNIT_TEST" mode.
            self::callExceptionHandlerDirectly('Assertion failed.', $id);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * This changes a character sets to display a multibyte character string with local window of debugger, and this returns it.
     *
     * @return array Some changed variables.
     *
     * @example $gDebugValue = \BreakpointDebugging::convertMbStringForDebug('SJIS', $scalar1, $array2, $scalar2);
     */
    static function convertMbStringForDebug()
    {
        // In case of local.
        if (!(BA::$exeMode & B::REMOTE)) {
            // Character set string to want to display, and some variables.
            $mbStringArray = func_get_args();
            $mbParamArray = array_slice($mbStringArray, 1);
            return self::_convertMbStringForDebugSubroop($mbStringArray[0], $mbParamArray);
        }
    }

    /**
     * This changes a multibyte character string array, and this returns it.
     *
     * @param string $charSet      Character set.
     * @param array  $mbParamArray Parameter array.
     *
     * @return array This does return multibyte character string for display.
     */
    private static function _convertMbStringForDebugSubroop($charSet, $mbParamArray)
    {
        self::assert(func_num_args() === 2);
        self::assert(is_string($charSet));
        self::assert(is_array($mbParamArray));

        $displayMbStringArray = array ();
        $count = 0;
        foreach ($mbParamArray as $mbString) {
            if (is_array($mbString)) {
                $displayMbStringArray[$count] = self::_convertMbStringForDebugSubroop($charSet, $mbString);
            } else if (is_string($mbString)) {
                $displayMbStringArray[$count] = mb_convert_encoding($mbString, $charSet, 'auto');
            } else {
                $displayMbStringArray[$count] = $mbString;
            }
            $count++;
        }
        return $displayMbStringArray;
    }

    /**
     * "ini_set()" with validation except for release mode.
     * Sets with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
     *
     * @param string $phpIniVariable "php.ini" variable.
     * @param string $setValue       Value of variable.
     * @param bool   $doCheck        Does this class method check to copy to the release file?
     *
     * @return void
     */
    static function iniSet($phpIniVariable, $setValue, $doCheck = true)
    {
        self::assert(func_num_args() <= 3);
        self::assert($phpIniVariable !== 'error_log');
        self::assert(is_string($phpIniVariable));
        self::assert(is_string($setValue));
        self::assert(is_bool($doCheck));

        $getValue = ini_get($phpIniVariable);
        if ($setValue !== $getValue) {
            // In case of remote debug.
            if ($doCheck === true
                && (BA::$exeMode & B::REMOTE)
                && isset($_SERVER['SERVER_ADDR']) // In case of common gateway.
            ) {
                $backTrace = debug_backtrace();
                $baseName = basename($backTrace[0]['file']);
                $cmpName = '_MySetting_InDebug.php';
                if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
                    $baseName = strtolower($baseName);
                    $cmpName = strtolower($cmpName);
                }
                $cmpNameLength = strlen($cmpName);
                if (!substr_compare($baseName, $cmpName, 0 - $cmpNameLength, $cmpNameLength, true)) {
                    // @codeCoverageIgnoreStart
                    echo '<body style="background-color:black;color:white">';
                    $notExistFlag = true;
                    foreach (self::$_onceFlag as $cmpName) {
                        if (!strcmp($baseName, $cmpName)) {
                            $notExistFlag = false;
                            break;
                        }
                    }
                    if ($notExistFlag) {
                        self::$_onceFlag[] = $baseName;
                        $packageName = substr($baseName, 0, 0 - $cmpNameLength);
                        echo <<<EOD
<pre>
### "\BreakpointDebugging::iniSet()": You must copy from "./{$packageName}_MySetting_InDebug.php" to user place folder of "./{$packageName}_MySetting.php" for release because set value and value of php.ini differ.
### Also, if remote "php.ini" was changed, you must redo remote debug mode.
</pre>
EOD;
                    }
                    echo <<<EOD
<pre>
	file: {$backTrace[0]['file']}
	line: {$backTrace[0]['line']}
</pre>
EOD;
                    echo '</body>';
                }
                // @codeCoverageIgnoreEnd
            }
            if (ini_set($phpIniVariable, $setValue) === false) {
                throw new \BreakpointDebugging_ErrorException('"ini_set()" failed.', 101);
            }
        }
    }

    /**
     * Executes function by parameter array, then displays executed function line, file, parameters and results.
     * Does not exist in case of release because this method uses for a function verification display.
     *
     * @param string $functionName Function name.
     * @param array  $params       Parameter array.
     *
     * @return Executed function result.
     *
     * @example $return = \BreakpointDebugging::displayVerification('function_name', func_get_args());
     *          $return = \BreakpointDebugging::displayVerification('function_name', array($object, $resource, &$reference));
     */
    static function displayVerification($functionName, $params)
    {
        self::assert(func_num_args() === 2);
        self::assert(is_string($functionName));
        self::assert(is_array($params));

        $functionVerificationHtmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>functionVerification</title>
    </head>
    <body style="background-color: black; color: white; font-size: 25px">
        <pre></pre>
    </body>
</html>
EOD;
        B::windowVirtualOpen(__CLASS__, $functionVerificationHtmlFileContent);
        ob_start();

        self::$tmp = $params;
        $paramNumber = count($params);
        $propertyNameToSend = '\BreakpointDebugging::$tmp';
        $callStackInfo = debug_backtrace();
        echo '<b>Executed function information.</b></br></br>';
        echo "<b>FILE</b> = {$callStackInfo[0]['file']}</br>";
        echo "<b>LINE</b> = {$callStackInfo[0]['line']}</br>";
        echo '<b>NAME</b> = ' . $functionName . '(';
        $paramString = array ();
        for ($count = 0; $count < $paramNumber; $count++) {
            $paramString[] = $propertyNameToSend . '[' . $count . ']';
            var_dump($params[$count]);
        }
        echo ')';
        $code = $functionName . '(' . implode(',', $paramString) . ')';
        $return = eval('$return = ' . $code . '; echo "<br/><b>RETURN</b> = "; var_dump($return); return $return;');
        echo '//////////////////////////////////////////////////////////////////////////////////////';

        B::windowHtmlAddition(__CLASS__, 'pre', 0, ob_get_clean());

        return $return;
    }

    ///////////////////////////// For package user until here in case of debug mode. /////////////////////////////
}

// When "Xdebug" does not exist.
if (!B::getXebugExists()) {
    global $_BreakpointDebugging_EXE_MODE;
    if (!($_BreakpointDebugging_EXE_MODE & B::REMOTE)) { // In case of local.
        B::exitForError(
            PHP_EOL
            . '### ERROR ###' . PHP_EOL
            . 'FILE: ' . __FILE__ . ' LINE: ' . __LINE__ . PHP_EOL
            . '"Xdebug" extension has been not loaded though this is a local host.' . PHP_EOL
            . '"Xdebug" extension is required because (uses breakpoint, displays for fatal error and avoids infinity recursive function call).' . PHP_EOL
        );
    }
}

B::checkPathEnvironmentVariable();
register_shutdown_function('\BreakpointDebugging::checkSuperUserExecution');

?>
