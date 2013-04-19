<?php

/**
 * Dummy.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
chdir('../../../');
require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;
use \TestClass as T;

B::isUnitTestExeMode(false); // Checks the execution mode.
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
                'BreakpointDebugging/Sample/SampleToLimitAccess_Option.php'
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
        //B::limitAccess('BreakpointDebugging/Sample/SampleToLimitAccess_Option.php');
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_Option.php'
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
        //B::limitAccess('BreakpointDebugging/Sample/SampleToLimitAccess_Option.php');
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_Option.php'
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

// if (B::getStatic('$exeMode') & B::REMOTE_RELEASE) { // In case of release. // Actual "php" code.
if (B::getStatic('$exeMode') & B::LOCAL_DEBUG_OF_RELEASE) {
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

} else { // In case of not release.
    include_once __DIR__ . '/SampleToLimitAccess_Option.php';
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

echo '<pre>END.</pre>';

?>
