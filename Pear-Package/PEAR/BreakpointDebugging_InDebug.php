<?php

/**
 * This class executes error or exception handling, and it is excepted in release mode.
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
use \BreakpointDebugging_InAllCase as BA;
use \BreakpointDebugging_Window as BW;

/**
 * This class executes error or exception handling, and it is excepted in release mode.
 *
 * PHP version 5.3.2-5.4.x
 *
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
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
     * The class method call locations.
     *
     * @var array
     */
    private static $_callLocations = array ();

    /**
     * Setting option filenames.
     *
     * @var array
     */
    private static $_onceFlagPerPackageInDebug = array ();

    /**
     * Include-paths.
     *
     * @var string
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
        parent::$staticPropertyLimitings['$exeMode'] = 'BreakpointDebugging_PHPUnit.php';
        $tmp = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
        parent::$staticPropertyLimitings['$_maxLogFileByteSize'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogParamNestingLevel'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogElementNumber'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogStringSize'] = $tmp;
        parent::$staticPropertyLimitings['$_workDir'] = $tmp;
        parent::$staticPropertyLimitings['$_developerIP'] = $tmp;
        parent::$staticPropertyLimitings['$_onceErrorDispFlag'] = 'BreakpointDebugging/PHPUnit/FrameworkTestCase.php';
        parent::$staticPropertyLimitings['$_callingExceptionHandlerDirectly'] = array ('BreakpointDebugging/ErrorInAllCase.php',);
    }

    /**
     * If "Apache HTTP Server" does not support "suEXEC", this method displays security warning.
     *
     * @return void
     */
    static function checkSuperUserExecution()
    {
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // In case of Windows.
            return;
        }
        $processUser = posix_getpwuid(posix_geteuid());
        // If this is remote debug, unix and root user.
        if (BA::$exeMode === B::REMOTE //
            && $processUser['name'] === 'root' //
        ) {
            BW::virtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileTemplate());
            BW::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, 'Security warning: Recommends to change to "Apache HTTP Server" which Supported "suEXEC" because this "Apache HTTP Server" is executed by "root" user.');
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
     * <pre>
     * Example:
     *
     * <code>
     *      $pFile = B::fopen(array ($filePath, 'w+b'));
     * </code>
     *
     * </pre>
     *
     * @param array $params            Same as parent.
     * @param int   $permission        Same as parent.
     * @param int   $timeout           Same as parent.
     * @param int   $sleepMicroSeconds Same as parent.
     *
     * @return Same as parent.
     */
    static function fopen(array $params, $permission = 0600, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        self::assert(func_num_args() <= 4);
        self::assert(is_int($permission) && 0 <= $permission && $permission <= 0777);
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
            \BreakpointDebugging_PHPUnit::handleUnitTestException($pException);
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
            $paths = explode(';', getenv('path'));
            while (true) {
                foreach ($paths as $path) {
                    $path = rtrim($path, '\/');
                    if (is_file($path . '/php.exe')) {
                        break 2;
                    }
                }
                BW::virtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileTemplate());
                BW::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, 'Path environment variable has not been set for "php.exe" command.' . PHP_EOL . `path`);
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
            && (BA::$exeMode & B::UNIT_TEST) //
            && (!isset(\BreakpointDebugging_PHPUnit::$unitTestDir) || strpos($fullFilePath, \BreakpointDebugging_PHPUnit::$unitTestDir) === 0) //
        ) {
            return;
        }
        // If project work directory does not exist.
        if (!isset(parent::$pwd)) {
            return;
        } else {
            // Keeps the project work directory at "__destruct" and shutdown.
            chdir(parent::$pwd);
        }
        if (!isset(self::$_includePaths)) {
            self::$_includePaths = ini_get('include_path');
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
        parent::breakpoint("'$class$function()' must not invoke in '$fullFilePath' file.", debug_backtrace());
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
     *      Example: \BreakpointDebugging::assert(3 <= $value && $value <= 5); // $value should be 3-5.
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
     * <pre>
     * Example:
     *
     * <code>
     *      $gDebugValue = \BreakpointDebugging::convertMbStringForDebug('SJIS', $scalar1, $array2, $scalar2);
     * </code>
     *
     * </pre>
     *
     * @return array Some changed variables.
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
     * Executes function by parameter array, then displays executed function line, file, parameters and results.
     * Does not exist in case of release because this method uses for a function verification display.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      $return = \BreakpointDebugging::displayVerification('function_name', func_get_args());
     *      $return = \BreakpointDebugging::displayVerification('function_name', array($object, $resource, &$reference));
     * </code>
     *
     * </pre>
     *
     * @param string $functionName Function name.
     * @param array  $params       Parameter array.
     *
     * @return Executed function result.
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
        BW::virtualOpen(__CLASS__, $functionVerificationHtmlFileContent);
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

        BW::htmlAddition(__CLASS__, 'pre', 0, ob_get_clean());

        return $return;
    }

    ///////////////////////////// For package user until here in case of debug mode. /////////////////////////////
}

// When "Xdebug" does not exist.
if (!B::getXebugExists()) {
    global $_BreakpointDebugging_EXE_MODE;

    if (!($_BreakpointDebugging_EXE_MODE & B::REMOTE)) { // In case of local.
        exit(
            '<pre>'
            . '### ERROR ###' . PHP_EOL
            . 'FILE: ' . __FILE__ . ' LINE: ' . __LINE__ . PHP_EOL
            . '"Xdebug" extension has been not loaded though this is a local host.' . PHP_EOL
            . '"Xdebug" extension is required because (uses breakpoint, displays for fatal error and avoids infinity recursive function call).' . PHP_EOL
            . '</pre>'
        );
    }
}

B::checkPathEnvironmentVariable();
register_shutdown_function('\BreakpointDebugging::checkSuperUserExecution');
