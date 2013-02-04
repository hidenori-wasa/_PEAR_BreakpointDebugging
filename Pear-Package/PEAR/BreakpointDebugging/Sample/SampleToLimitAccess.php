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
use \BreakpointDebugging as B;
use \TestClass as T;

chdir('../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

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
     * @var array Auto properties reference.
     */
    protected $autoProperties;

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
     * Sets a static property value.
     *
     * @param string $propertyName Static property name.
     * @param mixed  $value        Static property value.
     *
     * @return void
     */
    static function setStatic($propertyName, $value)
    {
        B::limitAccess(
            array (
                'BreakpointDebugging/Sample/SampleToLimitAccess.php',
                'BreakpointDebugging/Sample/SampleToLimitAccess_Option.php'
            )
        );

        self::$staticProperties[$propertyName] = $value;
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

global $_BreakpointDebugging_EXE_MODE;
// if ($_BreakpointDebugging_EXE_MODE === B::RELEASE) { // In case of release. // Actual "php" code.
if ($_BreakpointDebugging_EXE_MODE === B::LOCAL_DEBUG_OF_RELEASE) {
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

    T::setStatic('$_staticA', $testValue);
    B::assert(T::getStatic('$_staticA') === $testValue);
    B::assert(T::$_staticA === $testValue);
    T::setStatic('$_staticB', $testValue);
    B::assert(T::getStatic('$_staticB') === $testValue);
    B::assert(T::$_staticB === $testValue);
    // T::setStatic('$_notExist', $testValue);
    // T::getStatic('$_notExist');

    $pTestClass->somthingInAllCase($testValue);
    $pTestClass->somthing($testValue);
}

echo '<pre>END.</pre>';

?>
