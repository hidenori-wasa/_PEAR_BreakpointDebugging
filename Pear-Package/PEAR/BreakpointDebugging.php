<?php

/**
 * Class for breakpoint debugging.
 *
 * LICENSE:
 * Copyright (c) 2012-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/docs/BREAKPOINTDEBUGGING_LICENSE.txt".
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
 * The class of both development mode and production mode.
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
     * The class method call locations.
     *
     * @var array
     */
    private static $_callLocations = array ();

    /**
     * Temporary variable.
     *
     * @var mixed
     */
    static $tmp;

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

    ///////////////////////////// For package user from here. /////////////////////////////
    /**
     * Stops execution for debug.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      use \BreakpointDebugging as B;
     *
     *      B::breakpoint('Error message.', debug_backtrace());
     * </code>
     *
     * </pre>
     *
     * @param string $message       Message.
     * @param array  $callStackInfo A call stack info.
     *
     * @return void
     */
    static function breakpoint($message, $callStackInfo)
    {
        \BreakpointDebugging::assert(func_num_args() === 2);
        \BreakpointDebugging::assert(is_string($message));
        \BreakpointDebugging::assert(is_array($callStackInfo));

        if (BREAKPOINTDEBUGGING_IS_PRODUCTION // Execution mode as production mode.
            || (self::$exeMode & B::IGNORING_BREAK_POINT) // Breakpoint has been ignored during unit-test.
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
     * Throws exception if assertion is false. Also, has identification code for debug unit test.
     *
     * This is commented out by "ProductionSwitcher".
     * Or, this does not exist in production mode.
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
     *      Example: \BreakpointDebugging::assert(3 <= $value && $value <= 5); // $value should be 3-5.
     *      Caution: Do not change variable's value in "$assertion" parameter because it is not executed in production mode.
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
        if (!is_int($id) //
            && !is_null($id) //
        ) {
            self::callExceptionHandlerDirectly('Exception identification number must be integer.', 3);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!$assertion) {
            if ($paramNumber === 1) {
                // For breakpoint debugging.
                self::breakpoint('Assertion failed.', debug_backtrace());
            }
            // For "@expectedExceptionMessage" annotation of "DEBUG_UNIT_TEST" mode.
            self::callExceptionHandlerDirectly('Assertion failed.', $id);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

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
        self::assert(func_num_args() === 3);
        self::assert(is_array($includePaths));
        self::assert(is_string($invokerFilePath));
        self::assert(is_string($fullFilePath));

        foreach ($includePaths as $includePath) {
            $invokerFullFilePath = realpath("$includePath/$invokerFilePath");
            if ($invokerFullFilePath === false) {
                continue;
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
     * This is commented out by "ProductionSwitcher".
     * Or, this does not exist in production mode.
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
        $line = $callStack[$key]['line'];
        if (array_key_exists($fullFilePath, self::$_callLocations) //
            && array_key_exists($line, self::$_callLocations[$fullFilePath]) //
        ) {
            // Skips same.
            return;
        }
        // Stores the invoking location information.
        self::$_callLocations[$fullFilePath][$line] = true;

        self::assert(func_num_args() <= 2);
        self::assert(is_array($invokerFilePaths) || is_string($invokerFilePaths));
        self::assert(is_bool($enableUnitTest));

        if (!$enableUnitTest //
            && (self::$exeMode & self::UNIT_TEST) //
            && (!isset(\BreakpointDebugging_PHPUnit::$unitTestDir) || strpos($fullFilePath, \BreakpointDebugging_PHPUnit::$unitTestDir) === 0) //
        ) {
            return;
        }
        // If project work directory does not exist.
        if (!isset(self::$pwd)) {
            return;
        } else {
            // Keeps the project work directory at "__destruct" and shutdown.
            chdir(self::$pwd);
        }
        $includePaths = ini_get('include_path');
        $includePaths = explode(PATH_SEPARATOR, $includePaths);
        if (is_array($invokerFilePaths)) {
            foreach ($invokerFilePaths as $invokerFilePath) {
                if (self::_checkInvokerFilePath($includePaths, $invokerFilePath, $fullFilePath)) {
                    return;
                }
            }
        } else {
            if (self::_checkInvokerFilePath($includePaths, $invokerFilePaths, $fullFilePath)) {
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
        self::breakpoint("'$class$function()' must not invoke in '$fullFilePath' file.", debug_backtrace());
        self::callExceptionHandlerDirectly("'$class$function()' must not invoke in '$fullFilePath' file.", 4);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd

    /**
     * "ini_set()" with validation. And, registers the location to comment out or replace.
     *
     * "BreakpointDebugging_IniSetOptimizer.php" page replaces to "ini_set()" this class method or comments out this class method.
     * Sets with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
     *
     * @param string $phpIniVariable "php.ini" variable.
     * @param string $setValue       Value of variable.
     *
     * @return void
     */
    static function iniSet($phpIniVariable, $setValue)
    {
        if (func_num_args() !== 2) {
            self::displayErrorAtInitialization('Parameter number must be 2.', 'Assertion failed.');
            return false;
        }
        if ($phpIniVariable === 'error_log') {
            self::displayErrorAtInitialization('Parameter 1 must not be "error_log".', 'Assertion failed.');
            return false;
        }
        if (!is_string($phpIniVariable)) {
            self::displayErrorAtInitialization('Parameter 1 must be character string.', 'Assertion failed.');
            return false;
        }
        if (!is_string($setValue)) {
            self::displayErrorAtInitialization('Parameter 2 must be character string.', 'Assertion failed.');
            return false;
        }

        if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of production server.
            $getValue = ini_get($phpIniVariable);
            // Registers the location to comment out or replace.
            if ($setValue === $getValue) {
                self::ini('COMMENT_OUT');
                return;
            } else {
                self::ini('REPLACE_TO_NATIVE');
            }
        }
        if (ini_set($phpIniVariable, $setValue) === false) {
            self::displayErrorAtInitialization('"ini_set()" failed.', 'Assertion failed.');
            return false;
        }

        return true;
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
     * Is this page top?
     *
     * <pre>
     * Example:
     *
     * <code>
     * if (\BreakpointDebugging::isTopPage()) { // Skips the following if unit test execution.
     * </code>
     *
     * </pre>
     *
     * @return bool Is this page top?
     */
    static function isTopPage()
    {
        $callStack = debug_backtrace();
        if (array_key_exists(1, $callStack)) {
            return false;
        }
        return true;
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
     * Error exit. Error exit location can be detected by call stack after break.
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
            spl_autoload_unregister('\\' . \BreakpointDebugging_PHPUnit_StaticVariableStorage::AUTOLOAD_NAME);
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
            throw new \BreakpointDebugging_ErrorException('First parameter type was mistaken.');
        }
        exit;
    }

    /**
     * Checks security before development page is run.
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
                        $message = <<<EOD
<pre style="color:red"><strong>
"define('BREAKPOINTDEBUGGING_MODE', 'RELEASE');"
    must be set into "BreakpointDebugging_MySetting.php".
</strong></pre>
EOD;
                        break;
                    case 'LOCAL':
                        if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1') { // If local.
                            break 2;
                        }
                        $message = <<<EOD
<pre style="color:red"><strong>
This page must be executed in local server.
</strong></pre>
EOD;
                        break;
                    case 'REMOTE':
                        if (self::$exeMode & self::REMOTE) { // If remote.
                            break 2;
                        }
                        $message = <<<EOD
<pre style="color:red"><strong>
This page must be executed in remote server.
</strong></pre>
EOD;
                        break;
                    default :
                        throw new \BreakpointDebugging_ErrorException('"' . __METHOD__ . '" parameter1 is mistake.', 101);
                }
                BW::virtualOpen(self::ERROR_WINDOW_NAME, self::getErrorHtmlFileTemplate());
                BW::htmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, $message);
                return false;
            }
        }
        // Checks client IP address.
        if ($_SERVER['REMOTE_ADDR'] !== self::$_developerIP) {
            BW::virtualOpen(self::ERROR_WINDOW_NAME, self::getErrorHtmlFileTemplate());
            BW::htmlAddition(
                self::ERROR_WINDOW_NAME, 'pre', 0, '<b>"$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" must be set ' . PHP_EOL
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
                self::ERROR_WINDOW_NAME, 'pre', 0, '<b>"https" protocol must be used.' . PHP_EOL
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
"define('BREAKPOINTDEBUGGING_MODE', 'DEBUG_UNIT_TEST');" or
"define('BREAKPOINTDEBUGGING_MODE', 'RELEASE_UNIT_TEST');"
    must be set into "{$pearSettingDirName}BreakpointDebugging_MySetting.php".
Or, set "const BREAKPOINTDEBUGGING_IS_PRODUCTION = false;" of "{$pearSettingDirName}BreakpointDebugging_MySetting.php"
with "./BreakpointDebugging_ProductionSwitcher.php".
EOD;
            BW::htmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, '<b>' . $errorMessage . '</b>');
            exit;
        }
    }

    /**
     * Gets "$exeMode" property.
     *
     * @return int Execution mode.
     */
    static function getExeMode()
    {
        return self::$exeMode;
    }

    /**
     * It references "$exeMode" property.
     *
     * @return int& Execution mode.
     */
    static function &refExeMode()
    {
        \BreakpointDebugging::limitAccess(array ('BreakpointDebugging/Error.php', 'BreakpointDebugging/ErrorInAllCase.php', 'BreakpointDebugging.php', 'BreakpointDebugging_PHPUnit.php'));

        return self::$exeMode;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getXebugExists()
    {
        return self::$_xdebugExists;
    }

    /**
     * Sets private property. Do not call this.
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
     * Gets this class's property.
     *
     * @return array This class's property.
     */
    static function getGet()
    {
        return self::$_get;
    }

    /**
     * Gets this class's property.
     *
     * @return string This class's property.
     */
    static function getDeveloperIP()
    {
        return self::$_developerIP;
    }

    /**
     * Refers to this class's property.
     *
     * @return string This class's property.
     */
    static function &refDeveloperIP()
    {
        \BreakpointDebugging::limitAccess(array (BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php', './BreakpointDebugging.php'));

        return self::$_developerIP;
    }

    /**
     * Gets this class's property.
     *
     * @return int This class's property.
     */
    static function getMaxLogFileByteSize()
    {
        return self::$_maxLogFileByteSize;
    }

    /**
     * Refers to this class's property.
     *
     * @return int This class's property.
     */
    static function &refMaxLogFileByteSize()
    {
        \BreakpointDebugging::limitAccess(BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php');

        return self::$_maxLogFileByteSize;
    }

    /**
     * Gets this class's property.
     *
     * @return int This class's property.
     */
    static function getMaxLogParamNestingLevel()
    {
        return self::$_maxLogParamNestingLevel;
    }

    /**
     * Refers to this class's property.
     *
     * @return int This class's property.
     */
    static function &refMaxLogParamNestingLevel()
    {
        \BreakpointDebugging::limitAccess(BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php');

        return self::$_maxLogParamNestingLevel;
    }

    /**
     * Gets this class's property.
     *
     * @return int This class's property.
     */
    static function getMaxLogElementNumber()
    {
        return self::$_maxLogElementNumber;
    }

    /**
     * Refers to this class's property.
     *
     * @return int This class's property.
     */
    static function &refMaxLogElementNumber()
    {
        \BreakpointDebugging::limitAccess(BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php');

        return self::$_maxLogElementNumber;
    }

    /**
     * Gets this class's property.
     *
     * @return int This class's property.
     */
    static function getMaxLogStringSize()
    {
        return self::$_maxLogStringSize;
    }

    /**
     * Refers to this class's property.
     *
     * @return int This class's property.
     */
    static function &refMaxLogStringSize()
    {
        \BreakpointDebugging::limitAccess(BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php');

        return self::$_maxLogStringSize;
    }

    /**
     * Gets this class's property.
     *
     * @return bool This class's property.
     */
    static function getCallingExceptionHandlerDirectly()
    {
        return self::$_callingExceptionHandlerDirectly;
    }

    /**
     * Refers to this class's property.
     *
     * @return bool This class's property.
     */
    static function &refCallingExceptionHandlerDirectly()
    {
        \BreakpointDebugging::limitAccess('./BreakpointDebugging/ErrorInAllCase.php');

        return self::$_callingExceptionHandlerDirectly;
    }

    /**
     * Gets this class's property.
     *
     * @return array This class's property.
     */
    static function getValuesToTrace()
    {
        return self::$_valuesToTrace;
    }

    /**
     * Refers to this class's property.
     *
     * @return array This class's property.
     */
    static function &refValuesToTrace()
    {
        \BreakpointDebugging::limitAccess('');

        return self::$_valuesToTrace;
    }

    /**
     * Gets this class's property.
     *
     * @return array This class's property.
     */
    static function getNotFixedLocations()
    {
        return self::$_notFixedLocations;
    }

    /**
     * Gets error message.
     *
     * @param string $errorMessage Error message.
     *
     * @return void
     */
    static function getErrorHTML($errorMessage, $callStackLevel = 1)
    {
        $fileInfo = debug_backtrace();
        if (array_key_exists($callStackLevel, $fileInfo)) {
            $fileInfo = $fileInfo[$callStackLevel];
        } else {
            return '';
        }
        if (array_key_exists('file', $fileInfo)) {
            $filename = $fileInfo['file'];
        } else {
            $filename = '';
        }
        if (array_key_exists('line', $fileInfo)) {
            $lineNumber = $fileInfo['line'];
        } else {
            $lineNumber = '';
        }
        return <<<EOD
<pre style="color:red">
ERROR MESSAGE: $errorMessage
    FILE: $filename
    LINE: $lineNumber
</pre>
EOD;
    }

    /**
     * Displays error at initialization.
     *
     * @param string $errorMessage     Error message.
     * @param string $okButtionMessage OK button message.
     *
     * @return void
     */
    static function displayErrorAtInitialization($errorMessage, $okButtionMessage)
    {
        echo self::getErrorHTML($errorMessage, 2);
        echo '<script>alert(\'' . $okButtionMessage . '\')</script>';
        flush();
    }

    /**
     * Checks "php.ini" file-setting.
     *
     * "BreakpointDebugging_IniSetOptimizer.php" page comments out this class method.
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
        B::limitAccess(array (
            BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php',
            BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting_InDevelopment.php',
        ));

        if (func_num_args() !== 3) {
            self::displayErrorAtInitialization('Parameter number must be 3.', 'Assertion failed.');
            return false;
        }
        if (!is_string($phpIniVariable)) {
            self::displayErrorAtInitialization('Parameter 1 must be character string.', 'Assertion failed.');
            return false;
        }
        if (is_array($cmpValue)) {
            foreach ($cmpValue as $eachCmpValue) {
                if (!is_string($eachCmpValue)) {
                    self::displayErrorAtInitialization('Array element of parameter 2 must be character string.', 'Assertion failed.');
                    return false;
                }
            }
        } else if (!is_string($cmpValue)) {
            self::displayErrorAtInitialization('Parameter 2 must be character string or array.', 'Assertion failed.');
            return false;
        }
        if (!is_string($errorMessage)) {
            self::displayErrorAtInitialization('Parameter 3 must be character string.', 'Assertion failed.');
            return false;
        }

        if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of production server.
            self::ini('COMMENT_OUT');
        }

        $value = (string) ini_get($phpIniVariable);
        $cmpResult = false;
        if (is_array($cmpValue)) {
            foreach ($cmpValue as $eachCmpValue) {
                if ($value === $eachCmpValue) {
                    $cmpResult = true;
                    break;
                }
            }
        } else {
            if (in_array($cmpValue, array ('', '0'), true)) {
                if (!in_array($value, array ('', '0'), true)) {
                    $cmpResult = true;
                }
            } else if ($value !== $cmpValue) {
                $cmpResult = true;
            }
        }
        if ($cmpResult) {
            ob_start();
            var_dump($value);
            $errorMessage .= PHP_EOL . 'Current value = ' . ob_get_clean();
            self::displayErrorAtInitialization($errorMessage, '"php.ini" variable checking failed.');
            return false;
        }

        return true;
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
        \BreakpointDebugging::limitAccess('BreakpointDebugging_InDebug.php');

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
        self::assert(func_num_args() <= 3);
        self::assert(is_int($timeout));
        self::assert(is_int($sleepMicroSeconds));

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
        \BreakpointDebugging::limitAccess('BreakpointDebugging_InDebug.php');

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
    static function unlink($params, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        \BreakpointDebugging::assert(is_array($params));

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
        \BreakpointDebugging::limitAccess('BreakpointDebugging_InDebug.php');

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
        \BreakpointDebugging::limitAccess('BreakpointDebugging_InDebug.php');

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
        if (count($parentArray) > self::$_maxLogElementNumber) {
            $parentArray = array_slice($parentArray, 0, self::$_maxLogElementNumber, true);
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
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

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

    /**
     * Checks recursive data error.
     *
     * Please, see "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting_InDevelopment.php'" for below.
     *      xdebug.var_display_max_children
     *      xdebug.var_display_max_data
     *      xdebug.var_display_max_depth
     *
     * @param mixed $value A value for check.
     * @throws \BreakpointDebugging_ErrorException
     *
     * @return void
     */
    static function checkRecursiveDataError($value)
    {
        ob_start();
        var_dump($value);
        $varDumpResult = ob_get_clean();
        $varDumpResult = strip_tags($varDumpResult);
        $lines = explode("\n", $varDumpResult);
        while ((list(, $line) = each($lines)) !== false) {
            B::assert(is_string($line));
            $result = preg_match('`^ [[:blank:]]* \'GLOBALS\'`xX', $line);
            if ($result === 1) {
                next($lines);
                continue;
            }
            $result = preg_match('`^ [[:blank:]]* &`xX', $line);
            B::assert($result !== false);
            if ($result === 1) {
                throw new \BreakpointDebugging_ErrorException('Recursive data must not be used because of error cause. Also, "\Closure" object must not be used as auto property because it may be included to static variable.');
            }
        }
    }

    ///////////////////////////// For package user until here. /////////////////////////////
    /**
     * For "self::iniSet()" and "self::iniCheck()".
     *
     * @param string $changeKind The change kind. 'COMMENT_OUT' or 'REPLACE_TO_NATIVE'.
     *
     * @return void
     */
    protected static function ini($changeKind)
    {
        if ((BA::$exeMode & B::REMOTE) // In case of remote server.
            && isset($_SERVER['SERVER_ADDR']) // In case of common gateway.
        ) {
            $backTrace = debug_backtrace();
            $fileInfoToOptimize = $backTrace[1];
            $baseName = basename($fileInfoToOptimize['file']);
            $cmpNameSuffix = '_MySetting.php';
            $cmpNameLength = strlen($cmpNameSuffix);
            // Checks that a file name suffix is "_MySetting.php".
            if (!substr_compare($baseName, $cmpNameSuffix, 0 - $cmpNameLength, $cmpNameLength, true)) {
                // @codeCoverageIgnoreStart
                include_once './BreakpointDebugging_Optimizer.php';
                // Registers the location to comment out or replace.
                \BreakpointDebugging_Optimizer::setInfoToOptimize($fileInfoToOptimize['file'], $fileInfoToOptimize['line'], $changeKind);
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
            './BreakpointDebugging_PEAR_Setting/BreakpointDebugging_MySetting.php',
            ), true
        );

        // Unlinks synchronization files.
        $lockFilePaths = array (
            'LockByFileExistingOfInternal.txt',
            'LockByFileExisting.txt',
        );
        foreach ($lockFilePaths as $lockFilePath) {
            $lockFilePath = BREAKPOINTDEBUGGING_WORK_DIR_NAME . $lockFilePath;
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

        \BreakpointDebugging::limitAccess(array ('BreakpointDebugging_InDebug.php'));

        self::$pwd = getcwd();
        self::$_get = $_BreakpointDebugging_get;
        unset($_BreakpointDebugging_get);
        self::$_nativeExeMode = self::$exeMode = $_BreakpointDebugging_EXE_MODE;
        unset($GLOBALS['_BreakpointDebugging_EXE_MODE']);
        self::$_maxLogElementNumber = count($_SERVER); // Default value.
        $dirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
        self::$iniDisplayString = <<<EOD
### "\BreakpointDebugging::iniSet()" or "\BreakpointDebugging::iniCheck()": The following line of "{$dirName}[package name]_MySetting.php" must be commented out because set value and value of php.ini is same.
EOD;

        if (!BREAKPOINTDEBUGGING_IS_PRODUCTION) { // In case of development.
            if (is_dir(BREAKPOINTDEBUGGING_WORK_DIR_NAME)) {
                self::chmod(BREAKPOINTDEBUGGING_WORK_DIR_NAME, 0700);
            } else {
                self::mkdir(array (BREAKPOINTDEBUGGING_WORK_DIR_NAME));
            }
            // Copies the "BreakpointDebugging_*.php" file into current work directory.
            self::copyResourceToCWD('BreakpointDebugging_ErrorLogFilesManager.php', '');
            self::copyResourceToCWD('BreakpointDebugging_ProductionSwitcher.php', '');
        }

        // If development or local mode.
        if (!BREAKPOINTDEBUGGING_IS_PRODUCTION || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
            // If common gateway.
            if (isset($_SERVER['SERVER_ADDR'])) {
                // Initializes sync files.
                self::initializeSync();
            }
        }
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
        // Sets global internal error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds.)
        set_error_handler('\BreakpointDebugging_Error::handleInternalError', -1);

        try {
            if (BREAKPOINTDEBUGGING_IS_PRODUCTION //
                && BREAKPOINTDEBUGGING_IS_CAKE //
                && defined('CAKE_CORE_INCLUDE_PATH') //
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
        \BreakpointDebugging::limitAccess(array ('BreakpointDebugging.php', 'BreakpointDebugging_InDebug.php'));

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
        \BreakpointDebugging::limitAccess('BreakpointDebugging/ErrorInAllCase.php');
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
     */
    static function shutdown()
    {
        // Keeps the project work directory at "__destruct" and shutdown.
        chdir(self::$pwd);
    }

}

global $_BreakpointDebugging_EXE_MODE;

if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // Production mode.
    /**
     * The class for production mode.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://opensource.org/licenses/mit-license.php  MIT License
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    final class BreakpointDebugging extends \BreakpointDebugging_InAllCase
    {

        /**
         * Empties in production mode.
         *
         * @return void
         */
        static function limitAccess()
        {

        }

        /**
         * Empties in production mode.
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

    }

    // Ignores "Xdebug" if production mode because its mode must not stop or display.
    BA::setXebugExists(false);
} else { // If development mode.
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

if (B::getExeMode() & BA::UNIT_TEST) { // Unit test.
    include_once 'BreakpointDebugging_PHPUnit.php';
} else {

    /**
     * Dummy class in case of not unit-test.
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
