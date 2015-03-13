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
     * @var array Static properties reference.
     */
    protected static $staticProperties;

    /**
     * @var array Static property limitings reference.
     */
    protected static $staticPropertyLimitings;

    /**
     * @var array Auto properties reference.
     */
    protected $autoProperties;

    /**
     * @var array Auto properties limitings reference.
     */
    protected $autoPropertyLimitings;

    /**
     * @var mixed Dummy.
     */
    static $_staticA = 'staticA';
    // private static $_staticA = 'staticA'; // Actual "php" code.

    /**
     * @var mixed Dummy.
     */
    static $_staticB = 'staticB';
    // private static $_staticB = 'staticB'; // Actual "php" code.

    /**
     * @var mixed Dummy.
     */
    private $_autoA = 'autoA';

    /**
     * @var mixed Dummy.
     */
    private $_autoB = 'autoB';

    /**
     * Dummy.
     *
     * @return void
     */
    function __construct()
    {
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_InDebug.php'
            )
        );

        self::$staticProperties['$_staticA'] = &self::$_staticA;
        self::$staticProperties['$_staticB'] = &self::$_staticB;
    }

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
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_InDebug.php'
            )
        );

        return self::$staticProperties[$propertyName];
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

if (B::isDebug()) { // In case of debug.
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

    $staticA = &T::refStatic('$_staticA');
    $staticA = $testValue;
    B::assert(T::getStatic('$_staticA') === $testValue);
    B::assert(T::$_staticA === $staticA);
    $staticB = &T::refStatic('$_staticB');
    $staticB = $testValue;
    B::assert(T::getStatic('$_staticB') === $testValue);
    B::assert(T::$_staticB === $staticB);
    // $notExist = &T::refStatic('$_notExist');
    // T::getStatic('$_notExist');

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

?>
