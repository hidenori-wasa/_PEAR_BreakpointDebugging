<?php

/**
 * This class executes error or exception handling, and it is excepted in release mode.
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
use \BreakpointDebugging_Window as BW;

/**
 * The class of debug mode.
 *
 * PHP version 5.3.2-5.4.x
 *
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when autoload is done.
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
     * If "Apache HTTP Server" does not support "suEXEC", this method displays security warning.
     *
     * @return void
     */
    static function checkSuperUserExecution()
    {
        //if (ob_get_level() > 0) {
        //    ob_end_flush();
        //}
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // In case of Windows.
            return;
        }
        $processUser = posix_getpwuid(posix_geteuid());
        // If this is remote debug, unix and root user.
        if (parent::$exeMode === parent::REMOTE //
            && $processUser['name'] === 'root' //
        ) {
            BW::virtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileTemplate());
            BW::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, 'Security warning: Recommends to change to "Apache HTTP Server" which Supported "suEXEC" because this "Apache HTTP Server" is executed by "root" user.');
        }
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

        if (parent::$exeMode & parent::UNIT_TEST) {
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
                echo '<pre>Path environment variable has not been set for "php.exe" command.' . PHP_EOL;
                foreach ($paths as $path) {
                    echo "\t" . $path . PHP_EOL;
                }
                echo 'Add ";C:\xampp\php;C:\xampp\mysql\bin" to environment path.' . PHP_EOL;
                echo '[control panel] - [system] - [detail] - [environment variables] - "Path" - [edit...]' . PHP_EOL;
                exit;
            }
        }
    }

    ///////////////////////////// For package user from here in case of debug mode. /////////////////////////////
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
        if (!(parent::$exeMode & parent::REMOTE)) {
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
