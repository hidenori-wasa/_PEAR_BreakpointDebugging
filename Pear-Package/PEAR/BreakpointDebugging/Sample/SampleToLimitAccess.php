<?php

/**
 * Dummy.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
chdir('../../../');
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;
use \TestClass as T;

B::checkExeMode(); // Checks the execution mode.
/**
 * Dummy.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class TestClass_InAllCase
{
    /**
     * @var mixed Dummy.
     */
    private static $_staticA = 'staticA';

    /**
     * @var mixed Dummy.
     */
    private static $_staticB = 'staticB';

    /**
     * @var mixed Dummy.
     */
    private $_autoA = 'autoA';

    /**
     * @var mixed Dummy.
     */
    private $_autoB = 'autoB';

    /**
     * Gets a auto property value.
     *
     * @param string $propertyName Auto property name.
     *
     * @return mixed Auto property value.
     */
    function __get($propertyName)
    {
        return $this->$propertyName;
    }

    /**
     * Sets a auto property value.
     *
     * @param string $propertyName Auto property name.
     * @param mixed  $value        Auto property value.
     *
     * @return void
     */
    function __set($propertyName, $value)
    {
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_InDebug.php'
            )
        );

        $this->$propertyName = $value;
    }

    /**
     * Gets this class's property.
     *
     * @return mixed This class's property.
     */
    static function getStaticA()
    {
        return self::$_staticA;
    }

    /**
     * Refers to this class's property.
     *
     * @return mixed This class's property.
     */
    static function &refStaticA()
    {
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_InDebug.php'
            )
        );

        return self::$_staticA;
    }

    /**
     * Gets this class's property.
     *
     * @return mixed This class's property.
     */
    static function getStaticB()
    {
        return self::$_staticB;
    }

    /**
     * Refers to this class's property.
     *
     * @return mixed This class's property.
     */
    static function &refStaticB()
    {
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_InDebug.php'
            )
        );

        return self::$_staticB;
    }

    /**
     * Something.
     *
     * @param mixed $testValue The test value.
     *
     * @return void
     */
    function somthingInAllCase($testValue)
    {
        $this->_autoA = $testValue;
        B::assert($this->_autoA === $testValue);
        $this->_autoB = $testValue;
        B::assert($this->_autoB === $testValue);
        // $this->_notExist = $testValue;
        // $this->_notExist;

        self::$_staticA = $testValue;
        B::assert(self::$_staticA === $testValue);
        self::$_staticB = $testValue;
        B::assert(self::$_staticB === $testValue);
        // self::$_notExist = $testValue;
        // self::$_notExist;
    }

}

if (\BreakpointDebugging::isDebug()) { // In case of debug.
    include_once __DIR__ . '/SampleToLimitAccess_InDebug.php';
} else { // In case of release.
    /**
     * Dummy.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class TestClass extends \TestClass_InAllCase
    {

        /**
         * Dummy.
         *
         * @param mixed $testValue Dummy.
         *
         * @return void
         */
        function somthing($testValue)
        {

        }

    }

}

$pTestClass = new T();

$testValues = array ('We can change', 777, $pTestClass, tmpfile(), array (123, 'ABC'));

foreach ($testValues as $testValue) {
    $pTestClass->_autoA = $testValue;
    B::assert($pTestClass->_autoA === $testValue);
    $pTestClass->_autoB = $testValue;
    B::assert($pTestClass->_autoB === $testValue);
    // $pTestClass->_notExist = $testValue;
    // $pTestClass->_notExist;
    $staticA = &T::refStaticA();
    $staticA = $testValue;
    B::assert(T::getStaticA() === $testValue);
    $staticB = &T::refStaticB();
    $staticB = $testValue;
    B::assert(T::getStaticB() === $testValue);
    // $notExist = &T::refNotExist();
    // T::getNotExist();

    $pTestClass->somthingInAllCase($testValue);
    $pTestClass->somthing($testValue);
}

$htmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>SAMPLE</title>
    </head>
    <body style="background-color: black; color: white; font-size: 25px">
        <pre></pre>
    </body>
</html>
EOD;
BW::virtualOpen('BreakpointDebugging_limitAccess', $htmlFileContent);
BW::htmlAddition('BreakpointDebugging_limitAccess', 'pre', 0, 'END.');
