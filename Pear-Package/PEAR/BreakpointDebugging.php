<?php

/**
 * Class for breakpoint debugging.
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
use \BreakpointDebugging_InAllCase as BA;
use \BreakpointDebugging_Window as BW;

require_once __DIR__ . '/PEAR/Exception.php';
/**
 * Own package exception.
 *
 * PHP version 5.3.2-5.4.x
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class BreakpointDebugging_Exception_InAllCase extends \PEAR_Exception
{

    /**
     * Constructs instance.
     *
     * @param string $message  Exception message.
     * @param int    $id       Exception identification number.
     * @param object $previous Previous exception.
     *
     * @return void
     */
    function __construct($message, $id = null, $previous = null)
    {
        if ($previous === null) {
            parent::__construct($message, $id);
        } else {
            parent::__construct($message, $previous, $id);
        }
    }

}

/**
 * This class executes error or exception handling.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class BreakpointDebugging_InAllCase
{
    // ### Debug mode constant number ###
    /**
     * This flag means executing on practical use server. This flag means executing on local development server if it is not set.
     *
     * @const int
     */
    const REMOTE = 1;

    /**
     * This flag means executing release code. This flag means executing debug code if it is not set.
     *
     * @const int
     */
    const RELEASE = 2;

    /**
     * This flag means executing unit test. This flag means not executing unit test if it is not set.
     *
     * @const int
     */
    const UNIT_TEST = 4;

    /**
     * This flag means ignoring break point. This flag means enabling break point if it is not set.
     *
     * @const int
     */
    const IGNORING_BREAK_POINT = 8;

    /**
     * Character string which means recursive array.
     *
     *  @const string
     */
    const RECURSIVE_ARRAY = '### Omits recursive array. ###';

    /**
     * Character string which means using "$GLOBALS".
     *
     * @const string
     */
    const GLOBALS_USING = '### Omits own recursive array inside "$GLOBALS". ###';

    /**
     * Error window name.
     *
     * @const string
     */
    const ERROR_WINDOW_NAME = 'BreakpointDebugging_Error';

    /**
     * "\BreakpointDebugging_PHPUnit" instance.
     *
     * @var object
     */
    private static $_phpUnit;

    /**
     * Temporary variable.
     *
     * @var mixed
     */
    static $tmp;

    /**
     * Static properties reference.
     *
     * @var array
     */
    protected static $staticProperties;

    /**
     * Static property limitings reference.
     *
     * @var array
     */
    protected static $staticPropertyLimitings;

    /**
     * "Xdebug" existing-flag.
     *
     * @var bool
     */
    private static $_xdebugExists;

    /**
     * Developer IP for remote error log file manager, remote code coverage report display and remote unit test.
     *
     * @var string
     */
    private static $_developerIP = '127.0.0.1';

    /**
     * This prepend to logging when self::handleException() is called.
     *
     * @var string
     */
    static $prependExceptionLog = '';

    /**
     * This prepend to logging when self::handleError() is called.
     *
     * @var string
     */
    static $prependErrorLog = '';

    /**
     * Maximum log file byte size.
     *
     * @var int
     */
    private static $_maxLogFileByteSize = 131072; // 1 << 17

    /**
     * Max log parameter nesting level.
     *
     * @var int
     */
    private static $_maxLogParamNestingLevel = 20;

    /**
     * Maximum count of elements in log. ( Total of parameters or array elements )
     *
     * @var int
     */
    private static $_maxLogElementNumber;

    /**
     * Maximum string type byte-count of log.
     *
     * @var int
     */
    private static $_maxLogStringSize = 3000;

    /**
     * Work directory of this package.
     *
     * @var string
     */
    private static $_workDir;

    /**
     * Locations to be not Fixed.
     *
     * @var array
     */
    private static $_notFixedLocations = array ();

    /**
     * Values to trace.
     *
     * @var array
     */
    private static $_valuesToTrace;

    /**
     * Once error display flag.
     *
     * @var bool
     */
    private static $_onceErrorDispFlag = false;

    /**
     * Calling exception handler directly?
     *
     * @var bool
     */
    private static $_callingExceptionHandlerDirectly = false;

    /**
     * The project work directory.
     *
     * @var string
     */
    protected static $pwd;

    /**
     * Execution mode.
     *
     * @var int
     */
    protected static $exeMode;

    /**
     * Native execution mode.
     *
     * @var int
     */
    private static $_nativeExeMode;

    /**
     * Error initialization flag of "self::iniCheck()" class method.
     *
     * @var string
     */
    private static $_iniCheckErrorInitializationFlag = true;

    /**
     * "$_GET" in case of common gateway, or "$_GET" which is built from last of command line parameters in case of command line.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      $queryString = '"' . B::httpBuildQuery(array ('ADDITIONAL_KEY' => 1234)) . '"';
     *      $pPipe = popen('php.exe -f example.php -- ' . $queryString, 'r'); // For Windows.
     *      $pPipe = popen('php -f example.php -- ' . $queryString . ' &', 'r'); // For Unix.
     * </code>
     *
     * </pre>
     *
     * @var array
     */
    private static $_get;

    /**
     * The display character string for production server performance.
     *
     * @var string
     */
    protected static $iniDisplayString;

    /**
     * Setting option filenames.
     *
     * @var array
     */
    protected static $onceFlagPerPackage = array ();

    /**
     * Is it spl autoload call?
     *
     * @var bool
     */
    private static $_isSplAutoLoadCall = false;

    ///////////////////////////// For package user from here. /////////////////////////////
    /**
     * Debugs by using breakpoint.
     * We must define this class method outside namespace, and we must not change method name when we call this method.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      B::breakpoint('Error message.', debug_backtrace());
     * </code>
     *
     * </pre>
     *
     * @param string $message       Message.
     * @param array  $callStackInfo A call stack info.
     *
     * @return void
     *
     * @codeCoverageIgnore
     * Because I do not want to stop at breakpoint.
     */
    static function breakpoint($message, $callStackInfo)
    {
        \BreakpointDebugging::assert(func_num_args() === 2);
        \BreakpointDebugging::assert(is_string($message));
        \BreakpointDebugging::assert(is_array($callStackInfo));

        if (self::$exeMode === (B::REMOTE | B::RELEASE) //
            || (self::$exeMode & B::IGNORING_BREAK_POINT) //
        ) {
            return;
        }

        reset($callStackInfo);
        if (!empty($callStackInfo)) {
            $call = each($callStackInfo);
            $call = $call['value'];
            if (array_key_exists('file', $call)) {
                $errorFile = $call['file'];
            }
            if (array_key_exists('line', $call)) {
                $errorLine = $call['line'];
            }
        }

        flush();
        if (self::getXebugExists()) {
            xdebug_break(); // Breakpoint. See local variable value by doing step execution here.
            // Push stop button if is thought error message.
        }

        if (self::$_nativeExeMode === self::REMOTE) { // In case of remote debug.
            // Remote debug must end immediately to avoid eternal execution.
            exit;
        }
    }

    /**
     * Is this the debug execution mode? This class method is needed for code coverage report.
     *
     * @return bool Is this the debug execution mode?
     */
    static function isDebug()
    {
        return !(self::$exeMode & self::RELEASE);
    }

    /**
     * Generates URL-encoded query character string by adding specification to native.
     *
     * @param array $additionalElements Array of specification query-character-strings.
     *
     * @return string A URL-encoded query character string.
     */
    static function httpBuildQuery($additionalElements)
    {
        \BreakpointDebugging::assert(count(array_diff_key($additionalElements, self::$_get)) === count($additionalElements));

        return http_build_query(array_merge($additionalElements, self::$_get));
    }

    /**
     * Sets the "\BreakpointDebugging_PHPUnit" object.
     *
     * @param object $phpUnit "\BreakpointDebugging_PHPUnit".
     *
     * @return void
     */
    static function setPHPUnit($phpUnit)
    {
        B::limitAccess('BreakpointDebugging_PHPUnit.php', true);

        self::$_phpUnit = $phpUnit;
    }

    /**
     * Error exit. You can detect error exit location by call stack after break if you use this.
     *
     * @param mixed $error Error message or error exception instance.
     *
     * @return void
     *
     * @codeCoverageIgnore
     * Because this class method exits.
     */
    static function exitForError($error = '')
    {
        self::$exeMode &= ~B::IGNORING_BREAK_POINT;
        if (self::$_nativeExeMode & self::UNIT_TEST) {
            // Uses "BreakpointDebugging" package autoloader.
            spl_autoload_unregister(array (self::$_phpUnit->getStaticVariableStorageInstance(), 'loadClass'));
        }
        if (!BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of development.
            // Displays error call stack instead of log.
            self::$exeMode &= ~self::RELEASE;
            // Avoids exception throw.
            self::$_nativeExeMode &= ~self::UNIT_TEST;
            // Ends the output buffering.
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
        }
        if (is_string($error)) {
            self::handleError(E_USER_ERROR, $error);
        } else if ($error instanceof \Exception) {
            self::handleException($error);
        } else {
            throw new \BreakpointDebugging_ErrorException('You mistook type of first parameter.');
        }
        exit;
    }

    /**
     * Checks security before we run development page.
     *
     * @param mixed $necessaryExeMode Necessary execution mode. Does not check execution mode if this is null.
     *
     * @return bool Success or failure.
     */
    static function checkDevelopmentSecurity($necessaryExeMode = null)
    {
        \BreakpointDebugging::assert(func_num_args() <= 1);
        \BreakpointDebugging::assert($necessaryExeMode === null || is_string($necessaryExeMode));

        // Checks the execution mode.
        if ($necessaryExeMode !== null) {
            while (true) {
                switch ($necessaryExeMode) {
                    case 'RELEASE':
                        if (self::$exeMode & self::RELEASE) { // In case of release.
                            break 2;
                        }
                        $message = '<b>You must set' . PHP_EOL
                            . '    "define(\'BREAKPOINTDEBUGGING_MODE\', \'RELEASE\');"' . PHP_EOL
                            . 'into "BreakpointDebugging_MySetting.php".</b>';
                        break;
                    case 'LOCAL':
                        if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1') { // In case of local.
                            break 2;
                        }
                        $message = '<b>You must execute in local.</b>';
                        break;
                    default :
                        throw new \BreakpointDebugging_ErrorException('"' . __METHOD__ . '" parameter1 is mistake.', 101);
                }
                BW::virtualOpen(self::ERROR_WINDOW_NAME, self::getErrorHtmlFileTemplate());
                BW::htmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, $message);
                return false;
            }
        }
        if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1') { // In case of local.
            return true;
        }
        // Checks client IP address.
        if ($_SERVER['REMOTE_ADDR'] !== self::$_developerIP) {
            BW::virtualOpen(self::ERROR_WINDOW_NAME, self::getErrorHtmlFileTemplate());
            BW::htmlAddition(
                self::ERROR_WINDOW_NAME, 'pre', 0, '<b>You must set "$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" ' . PHP_EOL
                . "\t" . 'into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" file.' . PHP_EOL
                . 'Or, you mistook start "php" page.</b>'
            );
            return false;
        }
        // Checks the request protocol.
        if (!array_key_exists('HTTPS', $_SERVER) //
            || empty($_SERVER['HTTPS']) //
            || $_SERVER['HTTPS'] === 'off' //
        ) {
            BW::virtualOpen(self::ERROR_WINDOW_NAME, self::getErrorHtmlFileTemplate());
            BW::htmlAddition(
                self::ERROR_WINDOW_NAME, 'pre', 0, '<b>You must use "https" protocol.' . PHP_EOL
                . 'Or, you mistook start "php" page.</b>'
            );
            return false;
        }
        return true;
    }

    /**
     * Checks unit-test-execution-mode.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      <?php
     *      require_once './BreakpointDebugging_Inclusion.php';
     *      use \BreakpointDebugging as B;
     *      B::checkExeMode(); // Checks the execution mode.
     *          .
     *          .
     *          .
     * </code>
     *
     * </pre>
     *
     * @param bool $isUnitTest It is unit test?
     *
     * @return void
     *
     * @codeCoverageIgnore
     * Because this class method does not exist in case of unit test.
     */
    static function checkExeMode($isUnitTest = false)
    {
        if (self::$exeMode & self::UNIT_TEST) {
            \BreakpointDebugging_PHPUnit::checkExeMode($isUnitTest);
            return;
        }

        if ($isUnitTest) {
            BW::virtualOpen(self::ERROR_WINDOW_NAME, self::getErrorHtmlFileTemplate());
            $pearSettingDirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
            $errorMessage = <<<EOD
You must set
    "define('BREAKPOINTDEBUGGING_MODE', 'DEBUG_UNIT_TEST');" or
    "define('BREAKPOINTDEBUGGING_MODE', 'RELEASE_UNIT_TEST');"
into "{$pearSettingDirName}BreakpointDebugging_MySetting.php".
Or, set "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" of "{$pearSettingDirName}BreakpointDebugging_MySetting.php"
with "./BreakpointDebugging_ProductionSwitcher.php".
EOD;
            BW::htmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, '<b>' . $errorMessage . '</b>');
            exit;
        }
    }

    /**
     * Gets a static property value.
     *
     * @param string $propertyName Static property name.
     *
     * @return mixed Static property value.
     */
    static function getStatic($propertyName)
    {
        return self::$staticProperties[$propertyName];
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
        B::limitAccess('BreakpointDebugging_InDebug.php');

        return self::$staticProperties[$propertyName];
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getXebugExists()
    {
        B::limitAccess(
            array ('BreakpointDebugging.php',
                'BreakpointDebugging_InDebug.php'
            )
        );

        return self::$_xdebugExists;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param bool $value Same as property.
     *
     * @return void
     */
    static function setXebugExists($value)
    {
        self::$_xdebugExists = $value;
    }

    /**
     * Checks "php.ini" file-setting.
     *
     * @param string $phpIniVariable "php.ini" file-setting variable.
     * @param mixed  $cmpValue       Value which should set in case of string.
     *                               Value which should avoid in case of array.
     * @param string $errorMessage   Error message.
     *
     * @return void
     */
    static function iniCheck($phpIniVariable, $cmpValue, $errorMessage)
    {
        if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of production server.
            return;
        }

        B::limitAccess('BreakpointDebugging_InDebug.php');

        $value = (string) ini_get($phpIniVariable);
        $cmpResult = false;
        if (is_array($cmpValue)) {
            foreach ($cmpValue as $eachCmpValue) {
                if (!is_string($eachCmpValue)) {
                    throw new \BreakpointDebugging_ErrorException('', 101);
                }
                if ($value === $eachCmpValue) {
                    $cmpResult = true;
                    break;
                }
            }
        } else {
            if ($value !== $cmpValue) {
                $cmpResult = true;
            }
        }
        if ($cmpResult) {
            if (self::$_iniCheckErrorInitializationFlag) {
                self::$_iniCheckErrorInitializationFlag = false;
                BW::virtualOpen(self::ERROR_WINDOW_NAME, self::getErrorHtmlFileTemplate());
            }
            ob_start();

            echo "$errorMessage" . PHP_EOL
            . "Current value =";
            var_dump($value);

            BW::htmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, ob_get_clean());
        } else {
            if (self::$exeMode & self::RELEASE) { // In case of release mode.
                self::ini('_MySetting.php', self::$onceFlagPerPackage, self::$iniDisplayString);
            }
        }
    }

    /**
     * This registers as function or method being not fixed.
     *
     * @param bool $isRegister Is this registered?
     *
     * @return void
     *
     * Example: \BreakpointDebugging::registerNotFixedLocation(self::$_isRegister[__METHOD__]);
     */
    final static function registerNotFixedLocation(&$isRegister)
    {
        \BreakpointDebugging::assert(func_num_args() === 1);

        // When it has been registered.
        if (!empty($isRegister)) {
            return;
        }
        $isRegister = true;

        $backTrace = debug_backtrace();
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
        } else { // In case of scope of start page file.
            // @codeCoverageIgnoreStart
            $backTrace2['file'] = &$backTrace[0]['file'];
            // @codeCoverageIgnoreEnd
        }
        self::$_notFixedLocations[] = $backTrace2;
    }

    /**
     * Add values to trace.
     *
     * @param array $values Values.
     *
     * @return void
     *
     * Example: \BreakpointDebugging::addValuesToTrace(array('TEST_CONST' => TEST_CONST, '$testString' => $testString, '$varietyObject' => $varietyObject));
     */
    final static function addValuesToTrace($values)
    {
        \BreakpointDebugging::assert(func_num_args() === 1);
        \BreakpointDebugging::assert(is_array($values));

        $backTrace = debug_backtrace();
        $callInfo = &$backTrace[0];
        if (array_key_exists('file', $callInfo)) {
            // The file name to call.
            $file = &$callInfo['file'];
        } else {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        if (array_key_exists('line', $callInfo)) {
            // The line number to call.
            $line = &$callInfo['line'];
        } else {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
            if (array_key_exists('function', $backTrace2) //
                && ($backTrace2['function'] === 'include_once' || $backTrace2['function'] === '{closure}') //
            ) {
                // @codeCoverageIgnoreStart
                // Because including or lambda function is not unit test class method.
                $backTrace2['function'] = '';
            }
            // @codeCoverageIgnoreEnd
        } else { // In case of scope of start page file.
            // @codeCoverageIgnoreStart
            $backTrace2['file'] = &$backTrace[0]['file'];
            // @codeCoverageIgnoreEnd
        }
        self::$_valuesToTrace[$file][$line] = $backTrace2;
        self::$_valuesToTrace[$file][$line]['values'] = $values;
    }

    /**
     * This method changes it to unify multibyte character strings such as system-output or user input, and this returns UTF-8 multibyte character strings.
     * This is security for not mixing a character sets.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      \BreakpointDebugging::convertMbString($warning['Message']);
     * </code>
     *
     * </pre>
     *
     * @param string $string Character string which may be not UTF8.
     *
     * @return string UTF8 character string.
     */
    static function convertMbString($string)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        // Analyzes character sets of character string.
        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8' //
            || $charSet === 'ASCII' //
        ) {
            return $string;
        } else if ($charSet === false) {
            self::$_onceErrorDispFlag = true;
            throw new \BreakpointDebugging_ErrorException('This is not single character sets.', 101);
        }
        return mb_convert_encoding($string, 'UTF-8', $charSet);
    }

    /**
     * Changes the file or directory permission.
     *
     * @param string $name              The file or directory name.
     * @param int    $permission        The file or directory permission.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function chmod($name, $permission, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            return true;
        }
        // In case of Unix.
        if ((fileperms($name) & 0777) !== $permission) {
            return self::_retryForFilesystemFunction('chmod', array ($name, $permission), $timeout, $sleepMicroSeconds);
        }
        return true;
    }

    /**
     * "mkdir" method which sets permission and sets own user to owner.
     *
     * @param array $params            "mkdir()" parameters. But, default permission is "0700".
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function mkdir(array $params, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        if (!array_key_exists(1, $params)) {
            $params[1] = 0700;
        }
        return self::_retryForFilesystemFunction('mkdir', $params, $timeout, $sleepMicroSeconds);
    }

    /**
     * Removes directory with retry.
     *
     * @param array $params            "rmdir()" parameters.
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function rmdir(array $params, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        return self::_retryForFilesystemFunction('rmdir', $params, $timeout, $sleepMicroSeconds);
    }

    /**
     * Retries until specified timeout for function of filesystem because OS has permission and sometimes fails.
     *
     * @param string $functionName      Execution function name.
     * @param array  $params            Execution function parameters.
     * @param int    $timeout           The timeout.
     * @param int    $sleepMicroSeconds Micro seconds for sleep.
     * @param bool   $isTermination     This call is termination?
     *
     * @return mixed The result or false.
     *
     * @throw Instance of \Exception.
     */
    private static function _retryForFilesystemFunction($functionName, array $params, $timeout, $sleepMicroSeconds, $isTermination = false)
    {
        if ($isTermination) {
            $startTime = 0;
        } else {
            $startTime = time();
        }
        while (true) {
            $isTimeout = time() - $startTime > $timeout;
            try {
                if ($isTermination) {
                    // Detects error last.
                    $result = call_user_func_array($functionName, $params);
                    // @codeCoverageIgnoreStart
                } else {
                    // @codeCoverageIgnoreEnd
                    // Does not detect error except last.
                    set_error_handler('\BreakpointDebugging::handleError', 0);
                    try {
                        $result = @call_user_func_array($functionName, $params);
                    } catch (\Exception $e) {

                    }
                    restore_error_handler();
                }
                if ($result !== false) {
                    clearstatcache();
                    return $result;
                }
                if ($isTermination) {
                    // @codeCoverageIgnoreStart
                    // Because "PHPUnit" package throws exception.
                    return false;
                    // @codeCoverageIgnoreEnd
                }
                if ($isTimeout) {
                    usleep($sleepMicroSeconds);
                    return self::_retryForFilesystemFunction($functionName, $params, $timeout, $sleepMicroSeconds, true);
                }
            } catch (\Exception $e) {
                if ($isTimeout) {
                    // Detects exception last.
                    throw $e;
                }
            }
            // Waits micro second which is specified.
            usleep($sleepMicroSeconds);
        }
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * "fopen" method which sets the file mode, permission and sets own user to owner.
     *
     * @param array $params            "fopen()" parameters. But, default permission is "0600".
     * @param int   $permission        The file permission.
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return resource The file pointer resource or false.
     */
    static function fopen(array $params, $permission = 0600, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        $pFile = self::_retryForFilesystemFunction('fopen', $params, $timeout, $sleepMicroSeconds);
        if ($pFile) {
            B::chmod($params[0], $permission, $timeout, $sleepMicroSeconds);
            return $pFile;
            // @codeCoverageIgnoreStart
            // Because "PHPUnit" package throws exception.
        }
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * "file_put_contents()" with permission.
     *
     * @param string   $filename   Same as "file_put_contents()".
     * @param mixed    $params     Same as "file_put_contents()".
     * @param int      $permission The file permission.
     * @param int      $flags      Same as "file_put_contents()".
     * @param resource $context    Same as "file_put_contents()".
     *
     * @return mixed "false" if failure.
     */
    static function filePutContents($filename, $params, $permission = 0600, $flags = 0, $context = null)
    {
        $result = file_put_contents($filename, $params, $flags, $context);
        self::chmod($filename, $permission);
        return $result;
    }

    /**
     * Unlinks with retry.
     *
     * @param array $params            "unlink()" parameters.
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function unlink(array $params, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        return self::_retryForFilesystemFunction('unlink', $params, $timeout, $sleepMicroSeconds);
    }

    /**
     * Compresses integer array.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      fwrite($pFile, \BreakpointDebugging::compressIntArray(array(0xFFFFFFFF, 0x7C, 0x7D, 0x7E, 0x0A, 0x0D)));
     * </code>
     *
     * </pre>
     *
     * @param array $intArray Integer array.
     *
     * @return string Compression character string.
     */
    static function compressIntArray($intArray)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        $compressBytes = '';
        foreach ($intArray as $int) {
            \BreakpointDebugging::assert(preg_match('`^[0-9]$ | ^[1-9][0-9]+$`xX', $int) === 1);
            for ($diff = 1, $delimiter = 0x80, $tmpBytes = ''; $diff; $int = $diff / 0x7D) {
                // This changes from decimal number to 126 number.
                $diff = 0x7D * (int) ($int / 0x7D);
                $byte = $int - $diff;
                // Changes end of line character.
                if ($byte === 0xA) { // Changes "\n" to read a data by "fgets()" in Windows and Unix.
                    $tmpBytes .= chr(0x7E | $delimiter);
                } else if ($byte === 0xD) { // Changes "\r" to read a data by "fgets()" in Windows.
                    $tmpBytes .= chr(0x7F | $delimiter);
                } else {
                    $tmpBytes .= chr($byte | $delimiter);
                }
                $delimiter = 0;
            }
            $compressBytes .= strrev($tmpBytes);
        }
        // Adds "PHP_EOL" For data reading by "fgets()".
        return $compressBytes . PHP_EOL;
    }

    /**
     * Decompresses to integer array.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      while ($intArray = \BreakpointDebugging::decompressIntArray(fgets($pFile))) {
     * </code>
     *
     * </pre>
     *
     * @param mixed $compressBytes Compression character string by "\BreakpointDebugging::compressIntArray()".
     *
     * @return array Integer array.
     */
    static function decompressIntArray($compressBytes)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        $compressBytes = trim($compressBytes, PHP_EOL);
        $intArray = array ();
        $int = 0;
        $strlen = strlen($compressBytes);
        for ($count = 0; $count < $strlen; $count++) {
            // Gets compression byte.
            $compressByte = ord($compressBytes[$count]);
            // Trims delimiter-bit.
            $tmpByte = $compressByte & 0x7F;
            // Changes to end of line character.
            if ($tmpByte === 0x7E) {
                $tmpByte = 0xA; // "\n".
            } else if ($tmpByte === 0x7F) {
                $tmpByte = 0xD; // "\r".
            }
            // This changes from 126 number to decimal number.
            $int = $int * 0x7D + $tmpByte;
            // If this is delimiter bit.
            if ($compressByte & 0x80) {
                $intArray[] = $int;
                $int = 0;
            }
        }
        return $intArray;
    }

    /**
     * Clears recursive array element.
     *
     * @param array $parentArray  Parent array to search element.
     * @param array $parentsArray Array of parents to compare ID.
     *
     * @return array Array which changed.
     */
    private static function _clearRecursiveArrayElement($parentArray, $parentsArray)
    {
        if (count($parentArray) > B::getStatic('$_maxLogElementNumber')) {
            $parentArray = array_slice($parentArray, 0, B::getStatic('$_maxLogElementNumber'), true);
            $parentArray[] = ''; // Array element out of area.
        }

        // Changes the 'GLOBALS' nest element to string.
        if (array_diff_key($GLOBALS, $parentArray) === array ()) {
            $parentArray = array ();
            // Copies global variables except "$GLOBALS['GLOBALS']" element because "$GLOBALS" copy does not increment reference count, so it is special in array.
            foreach ($GLOBALS as $key => $value) {
                if ($key === 'GLOBALS') {
                    // Marks recursive child array.
                    $parentArray[$key] = self::GLOBALS_USING;
                    continue;
                }
                $parentArray[$key] = $value;
            }
        }

        // Creates array to change from recursive array to string.
        $changeArray = $parentArray;
        // Searches recursive array.
        foreach ($parentArray as $childKey => $childArray) {
            // If not recursive array.
            if (!is_array($childArray)) {
                continue;
            }
            // Stores the child array.
            $elementStorage = $parentArray[$childKey];
            // Checks reference of parents array by change from child array to string.
            $parentArray[$childKey] = self::RECURSIVE_ARRAY;
            foreach ($parentsArray as &$cmpParentArray) {
                $parentRecursiveArray = &$cmpParentArray[key($cmpParentArray)];
                // If a recursive array.
                if (!is_array($parentRecursiveArray)) {
                    // Restores recursive parent array.
                    $parentRecursiveArray = $elementStorage;
                    // Deletes recursive child array reference because avoids input parameter change.
                    // Because array copy copies reference ID of its array element.
                    unset($changeArray[$childKey]);
                    // Marks recursive child array.
                    $changeArray[$childKey] = self::RECURSIVE_ARRAY;
                    continue 2;
                }
            }
            // Restores child array.
            $parentArray[$childKey] = $elementStorage;
            // Adds current child element to parents array. Also, does reference copy because checks recursive-reference by using this.
            $parentsArray[][$childKey] = &$parentArray[$childKey];
            // Deletes recursive child array reference because avoids input parameter change.
            // Because array copy copies reference ID of its array element.
            unset($changeArray[$childKey]);
            // Clears recursive array element in under hierarchy.
            $changeArray[$childKey] = self::_clearRecursiveArrayElement($parentArray[$childKey], $parentsArray);
            // Takes out parent array.
            array_pop($parentsArray);
        }
        return $changeArray;
    }

    /**
     * Clears recursive array element.
     *
     * @param mixed $recursiveArray Recursive array. Keeps reference to this-variable by reference copy. CAUTION: This array is changed.
     *
     * @return mixed Array which changed.
     */
    static function clearRecursiveArrayElement(&$recursiveArray)
    {
        if (!is_array($recursiveArray)) {
            return $recursiveArray;
        }
        $parentsArray = array (array (&$recursiveArray));
        return self::_clearRecursiveArrayElement($recursiveArray, $parentsArray);
    }

    /**
     * Changes from full file path to a class name.
     *
     * @param string $fullFilePath Full file path.
     *
     * @return mixed Class name or false.
     */
    static function fullFilePathToClassName($fullFilePath)
    {
        if (empty($fullFilePath)) {
            return false;
        }
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        if (self::$exeMode & self::UNIT_TEST) {
            array_unshift($includePaths, dirname($_SERVER['SCRIPT_FILENAME']));
        }
        foreach ($includePaths as $includePath) {
            $fullIncludePath = realpath($includePath);
            $result = strpos($fullFilePath, $fullIncludePath);
            if ($result === 0) {
                $className = substr($fullFilePath, strlen($fullIncludePath) + 1, - strlen('.php'));
                // Changes directory separator and '-' to underscore.
                $className = str_replace(array ('/', '\\', '-'), '_', $className);
                if (!in_array($className, get_declared_classes())) {
                    continue;
                }
                return $className;
            }
        }
        return false;
    }

    /**
     * Copies resource to current work directory.
     *
     * @param string $resourceFileName      Resource file name.
     * @param string $resourceDirectoryPath Relative resource directory path.
     *
     * @return string Resource URI which copied to current work directory.
     */
    static function copyResourceToCWD($resourceFileName, $resourceDirectoryPath)
    {
        $relativeCWD = substr(self::$pwd, strlen($_SERVER['DOCUMENT_ROOT']) - strlen(self::$pwd) + 1);
        // If this mode is not production server release.
        if (!BREAKPOINTDEBUGGING_IS_PRODUCTION) {
            $includePaths = ini_get('include_path');
            $tmpIncludePaths = explode(PATH_SEPARATOR, $includePaths);
            $searchKey = array_search('.', $tmpIncludePaths, true);
            \BreakpointDebugging::assert($searchKey !== false);
            unset($tmpIncludePaths[$searchKey]);
            B::iniSet('include_path', implode(PATH_SEPARATOR, $tmpIncludePaths));
            $resourceFilePath = stream_resolve_include_path($resourceDirectoryPath . $resourceFileName);
            B::iniSet('include_path', $includePaths);
            $destResourceFilePath = self::$pwd . DIRECTORY_SEPARATOR . $resourceFileName;
            // If destination file does not exist.
            if (!is_file($destResourceFilePath)) {
                // Copies resource to current work directory.
                copy($resourceFilePath, $destResourceFilePath);
                goto AFTER_TREATMENT;
            }
            $resourceFileStat = stat($resourceFilePath);
            $destResourceFileStat = stat($destResourceFilePath);
            // If resource file was modified.
            if ($resourceFileStat['mtime'] > $destResourceFileStat['mtime']) {
                // Copies resource to current work directory.
                copy($resourceFilePath, $destResourceFilePath);
            }
        }

        AFTER_TREATMENT:
        return '//' . $_SERVER['SERVER_NAME'] . '/' . $relativeCWD . '/' . $resourceFileName;
    }

    ///////////////////////////// For package user until here. /////////////////////////////
    /**
     * For "self::iniSet()" and "self::iniCheck()".
     *
     * @param string $cmpNameSuffix      Comparison file name suffix.
     * @param array  $onceFlagPerPackage Once flag per package.
     * @param string $displayString      A display string.
     *
     * @return void
     */
    protected static function ini($cmpNameSuffix, &$onceFlagPerPackage, $displayString)
    {
        if ((BA::$exeMode & B::REMOTE) // In case of remote server.
            && isset($_SERVER['SERVER_ADDR']) // In case of common gateway.
        ) {
            $backTrace = debug_backtrace();
            $baseName = basename($backTrace[1]['file']);
            $cmpNameLength = strlen($cmpNameSuffix);
            if (!substr_compare($baseName, $cmpNameSuffix, 0 - $cmpNameLength, $cmpNameLength, true)) {
                // @codeCoverageIgnoreStart
                echo '<body style="background-color:black;color:white">';
                $notExistFlag = true;
                foreach ($onceFlagPerPackage as $cmpNameSuffix) {
                    if (!strcmp($baseName, $cmpNameSuffix)) {
                        $notExistFlag = false;
                        break;
                    }
                }
                if ($notExistFlag) {
                    $onceFlagPerPackage[] = $baseName;
                    $packageName = substr($baseName, 0, 0 - $cmpNameLength);
                    echo <<<EOD
<pre>
[package name] = $packageName
$displayString
</pre>
EOD;
                }
                echo <<<EOD
<pre>
	file: {$backTrace[1]['file']}
	line: {$backTrace[1]['line']}
</pre>
EOD;
                echo '</body>';
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Gets error HTML template.
     *
     * @return string Error HTML template.
     */
    static function getErrorHtmlFileTemplate()
    {
        return <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>ERROR</title>
	</head>
	<body style="background-color: black; color: white; font-size: 25px">
		<pre></pre>
	</body>
</html>
EOD;
    }

    /**
     * Initializes sync.
     *
     * @return void
     */
    static function initializeSync()
    {
        B::limitAccess(
            array (
            'BreakpointDebugging.php',
            'BreakpointDebugging/PHPUnit/FrameworkTestCaseSimple.php',
            ), true
        );

        // Unlinks synchronization files.
        $lockFilePaths = array (
            'LockByFileExistingOfInternal.txt',
            'LockByFileExisting.txt',
        );
        $workDir = B::getStatic('$_workDir');
        foreach ($lockFilePaths as $lockFilePath) {
            $lockFilePath = realpath($workDir . '/' . $lockFilePath);
            if (is_file($lockFilePath)) {
                B::unlink(array ($lockFilePath));
            }
            \BreakpointDebugging::assert(!is_file($lockFilePath));
        }
    }

    /**
     * Initializes static properties.
     *
     * @return void
     */
    static function initialize()
    {
        global $_BreakpointDebugging_EXE_MODE, $_BreakpointDebugging_get;

        B::limitAccess(array ('BreakpointDebugging_InDebug.php'));

        if (!BREAKPOINTDEBUGGING_IS_PRODUCTION // If development mode.
            && isset($_SERVER['SERVER_ADDR']) // If common gateway.
        ) {
            // Initializes sync files.
            B::initializeSync();
        }
        self::$pwd = getcwd();
        self::$_get = $_BreakpointDebugging_get;
        unset($_BreakpointDebugging_get);
        self::$staticProperties['$_get'] = &self::$_get;
        self::$_nativeExeMode = self::$exeMode = $_BreakpointDebugging_EXE_MODE;
        unset($GLOBALS['_BreakpointDebugging_EXE_MODE']);
        self::$staticProperties['$exeMode'] = &self::$exeMode;
        self::$staticProperties['$_developerIP'] = &self::$_developerIP;
        self::$staticProperties['$_maxLogFileByteSize'] = &self::$_maxLogFileByteSize;
        self::$staticProperties['$_maxLogParamNestingLevel'] = &self::$_maxLogParamNestingLevel;
        self::$_maxLogElementNumber = count($_SERVER); // Default value.
        self::$staticProperties['$_maxLogElementNumber'] = &self::$_maxLogElementNumber;
        self::$staticProperties['$_maxLogStringSize'] = &self::$_maxLogStringSize;
        self::$staticProperties['$_workDir'] = &self::$_workDir;
        self::$staticProperties['$_onceErrorDispFlag'] = &self::$_onceErrorDispFlag;
        self::$staticProperties['$_callingExceptionHandlerDirectly'] = &self::$_callingExceptionHandlerDirectly;
        self::$staticProperties['$_valuesToTrace'] = &self::$_valuesToTrace;
        self::$staticProperties['$_notFixedLocations'] = &self::$_notFixedLocations;
        $dirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
        self::$iniDisplayString = <<<EOD
### "\BreakpointDebugging::iniSet()" or "\BreakpointDebugging::iniCheck()": You must comment out following line of "{$dirName}[package name]_MySetting.php" because set value and value of php.ini is same.
EOD;
    }

    /**
     * It references "$staticProperties" property for static backup of "PHPUnit".
     *
     * @return array& "$staticProperties" property.
     */
    static function &refStaticProperties()
    {
        B::limitAccess('BreakpointDebugging_PHPUnit.php');

        return self::$staticProperties;
    }

    /**
     * It references "$staticPropertyLimitings" property for static backup of "PHPUnit".
     *
     * @return array& "$staticPropertyLimitings" property.
     */
    static function &refStaticPropertyLimitings()
    {
        B::limitAccess('BreakpointDebugging_PHPUnit.php');

        return self::$staticPropertyLimitings;
    }

    /**
     * Does autoload by path which was divided by name space separator and underscore separator as directory.
     *
     * @param string $className The class name which calls class member of static.
     *                          Or, the class name which creates new instance.
     *                          Or, the class name when extends base class.
     *
     * @return void
     */
    final static function loadClass($className)
    {
        if (self::$_nativeExeMode & self::UNIT_TEST) {
            if (!self::$_isSplAutoLoadCall) {
                self::$_isSplAutoLoadCall = true;
                $exception = false;
                try {
                    spl_autoload_call($className);
                } catch (\Exception $e) {
                    $exception = $e;
                }
                self::$_isSplAutoLoadCall = false;
                return $exception;
            }
        }

        // Trims the left name space root.
        $className = ltrim($className, '\\');
        // Changes underscore and name space separator to directory separator.
        $filePath = str_replace(array ('_', '\\'), '/', $className) . '.php';
        $absoluteFilePath = stream_resolve_include_path($filePath);
        if ($absoluteFilePath !== false) {
            include_once $absoluteFilePath;
        }
        return false;
    }

    /**
     * Global exception handler.
     * Displays exception-log in case of debug, or logs exception in case of release.
     *
     * @param object $pException Exception information.
     *
     * @return void
     */
    static function handleException($pException)
    {
        B::limitAccess(
            array (
                'BreakpointDebugging.php',
                'BreakpointDebugging_InDebug.php',
            )
        );

        // Sets global internal error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds.)
        set_error_handler('\BreakpointDebugging_Error::handleInternalError', -1);

        try {
            if (BREAKPOINTDEBUGGING_IS_PRODUCTION //
                && BREAKPOINTDEBUGGING_IS_CAKE //
            ) {
                \ErrorHandler::handleException($pException);
            }

            $error = new \BreakpointDebugging_Error();
            $error->handleException2($pException, self::$prependExceptionLog);
            if (self::$_nativeExeMode & self::UNIT_TEST) {
                \BreakpointDebugging_PHPUnit::displaysException($pException);
                \BreakpointDebugging_PHPUnit::handleUnitTestException($pException);
            }
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            // Because unit test may not cause internal exception inside "\BreakpointDebugging_ErrorInAllCase::handleException2()".
            if (self::$_nativeExeMode & self::UNIT_TEST) {
                restore_error_handler();
                throw $e;
            }
            \BreakpointDebugging_Error::handleInternalException($e);
            // @codeCoverageIgnoreEnd
        }

        restore_error_handler();
    }

    /**
     * Global error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     *
     * @return bool Without system log (true).
     */
    static function handleError($errorNumber, $errorMessage)
    {
        B::limitAccess(
            array (
                'BreakpointDebugging.php',
                'BreakpointDebugging_InDebug.php',
            )
        );

        // Sets global internal error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds.)
        set_error_handler('\BreakpointDebugging_Error::handleInternalError', -1);

        try {
            $error = new \BreakpointDebugging_Error();
            $error->handleError2($errorNumber, $errorMessage, self::$prependErrorLog, debug_backtrace());
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            // Because unit test may not cause internal exception inside "\BreakpointDebugging_ErrorInAllCase::handleError2()".
            if (self::$_nativeExeMode & self::UNIT_TEST) {
                restore_error_handler();
                throw $e;
            }
            \BreakpointDebugging_Error::handleInternalException($e);
            // @codeCoverageIgnoreEnd
        }

        restore_error_handler();

        return true;
    }

    /**
     * Calls global handler inside global handler.
     *
     * @param string $message A message.
     * @param mixed  $id      Exception identification number or null.
     *
     * @return void
     */
    protected static function callExceptionHandlerDirectly($message, $id)
    {
        if (self::$_onceErrorDispFlag) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        // Is this method.
        self::$_callingExceptionHandlerDirectly = true;
        self::$_onceErrorDispFlag = true;
        B::handleException(new \BreakpointDebugging_ErrorException($message, $id, null, 2));
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * Calls exception handler inside global error handling or global exception handling. (For this package developer).
     *
     * <pre>
     * Example:
     *
     * <code>
     *      \BreakpointDebugging::internalException($message, 1);
     * </code>
     *
     * </pre>
     *
     * @param string $message Exception message.
     * @param int    $id      Exception identification number inside function.
     *
     * @return void
     */
    final static function internalException($message, $id)
    {
        B::limitAccess('BreakpointDebugging/ErrorInAllCase.php');

        \BreakpointDebugging::assert(func_num_args() === 2);
        \BreakpointDebugging::assert(is_string($message));
        \BreakpointDebugging::assert(is_int($id));

        B::callExceptionHandlerDirectly($message, $id);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * Debugs by calling "__destructor()" of all object.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    static function shutdown()
    {
        // Keeps the project work directory at "__destruct" and shutdown.
        chdir(self::$pwd);
    }

}

global $_BreakpointDebugging_EXE_MODE;

if ($_BreakpointDebugging_EXE_MODE & BA::RELEASE) { // In case of release.
    /**
     * The class for release.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://opensource.org/licenses/mit-license.php  MIT License
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */

    abstract class BreakpointDebugging_Middle extends \BreakpointDebugging_InAllCase
    {

        /**
         * Empties in release.
         *
         * @return void
         */
        static function limitAccess()
        {

        }

        /**
         * Empties in release.
         *
         * @param bool $assertion Dummy.
         *
         * @return void
         *
         * @codeCoverageIgnore
         * Because this class method is overridden.
         */
        static function assert($assertion)
        {

        }

        /**
         * "ini_set()" with validation except for production mode.
         * Sets with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
         *
         * @param string $phpIniVariable "php.ini" variable.
         * @param string $setValue       Value of variable.
         *
         * @return void
         */
        static function iniSet($phpIniVariable, $setValue)
        {
            if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of production server.
                ini_set($phpIniVariable, $setValue);
            } else {
                $getValue = ini_get($phpIniVariable);
                // This displays to comment out it if "ini_set()" value is same "php.ini" value in case of remote release mode for production server performance.
                if ($setValue === $getValue) {
                    parent::ini('_MySetting.php', parent::$onceFlagPerPackage, parent::$iniDisplayString);
                } else {
                    if (ini_set($phpIniVariable, $setValue) === false) {
                        throw new \BreakpointDebugging_ErrorException('"ini_set()" failed.', 101);
                    }
                }
            }
        }

    }

    if ($_BreakpointDebugging_EXE_MODE & BA::UNIT_TEST) { // In case of unit test.
        /**
         * The class for release unit test.
         *
         * @category PHP
         * @package  BreakpointDebugging
         * @author   Hidenori Wasa <public@hidenori-wasa.com>
         * @license  http://opensource.org/licenses/mit-license.php  MIT License
         * @version  Release: @package_version@
         * @link     http://pear.php.net/package/BreakpointDebugging
         */
        final class BreakpointDebugging extends \BreakpointDebugging_Middle
        {

            /**
             * Assertion error is stopped at break point in case of release unit test.
             *
             * @param bool $assertion Assertion.
             *
             * @return void
             */
            static function assert($assertion)
            {
                if (!$assertion //
                    && !(BA::$exeMode & B::IGNORING_BREAK_POINT) //
                ) {
                    // @codeCoverageIgnoreStart
                    if (function_exists('xdebug_break')) {
                        xdebug_break(); // You must use "\BreakpointDebugging_PHPUnit::markTestSkippedInRelease(); // Because this unit test is assertion." at top of unit test class method.
                    } else {
                        // Because unit test is exited.
                        ini_set('xdebug.var_display_max_depth', 5);

                        BW::virtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileTemplate());
                        ob_start();

                        var_dump(debug_backtrace());

                        BW::htmlAddition(parent::ERROR_WINDOW_NAME, 'pre', 0, ob_get_clean());

                        // Ends the output buffering.
                        while (ob_get_level() > 0) {
                            ob_end_flush();
                        }
                        exit;
                    }
                }
                // @codeCoverageIgnoreEnd
            }

        }

    } else { // In case of not unit test.
        /**
         * The class for release.
         *
         * @category PHP
         * @package  BreakpointDebugging
         * @author   Hidenori Wasa <public@hidenori-wasa.com>
         * @license  http://opensource.org/licenses/mit-license.php  MIT License
         * @version  Release: @package_version@
         * @link     http://pear.php.net/package/BreakpointDebugging
         */
        final class BreakpointDebugging extends \BreakpointDebugging_Middle
        {

        }

        if (assert_options(ASSERT_ACTIVE, 0) === false) { // Ignore assert().
            throw new \BreakpointDebugging_ErrorException('', 101);
        }
    }

    if ($_BreakpointDebugging_EXE_MODE === (B::REMOTE | B::RELEASE)) { // In case of remote release.
        // Ignores "Xdebug" in case of remote release because must not stop.
        BA::setXebugExists(false);
    } else {
        BA::setXebugExists(extension_loaded('xdebug'));
    }
} else { // In case of debug.
    // This does not invoke extended class method exceptionally because its class is not defined.
    BA::setXebugExists(extension_loaded('xdebug'));
    include_once __DIR__ . '/BreakpointDebugging_InDebug.php';
    include_once __DIR__ . '/BreakpointDebugging_NativeFunctions_InDebug.php';
}

// Pushes autoload class method.
$result = spl_autoload_register('\BreakpointDebugging::loadClass');
\BreakpointDebugging::assert($result);
// Shifts global exception handler.
set_exception_handler('\BreakpointDebugging::handleException');
// Shifts global error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds.)
set_error_handler('\BreakpointDebugging::handleError', -1);
// Pushes the shutdown class method.
register_shutdown_function('\BreakpointDebugging::shutdown');
// Initializes static class.
B::initialize();

if (B::getStatic('$exeMode') & BA::UNIT_TEST) { // In case of unit test.
    include_once 'BreakpointDebugging_PHPUnit.php';
} else {

    /**
     * Dummy class for not unit test.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://opensource.org/licenses/mit-license.php  MIT License
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class BreakpointDebugging_Exception extends \BreakpointDebugging_Exception_InAllCase
    {

    }

}
/**
 * Own package error exception.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_ErrorException extends \BreakpointDebugging_Exception
{

}

/**
 * "Out of log range" exception.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_OutOfLogRangeException extends \BreakpointDebugging_Exception
{

}
