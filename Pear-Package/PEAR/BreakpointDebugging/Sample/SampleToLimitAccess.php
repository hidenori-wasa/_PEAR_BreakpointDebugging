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
abstract class BaseTestClass
{
    /**
     * @var array Static properties reference.
     */
    private static $_staticProperties;

    /**
     * @var mixed Dummy.
     */
    static $_staticA = 'staticA';
    // private static $_staticA = 'staticA';

    /**
     * @var mixed Dummy.
     */
    static $_staticB = 'staticB';
    // private static $_staticB = 'staticB';

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
        self::$_staticProperties['$_staticA'] = &self::$_staticA;
        self::$_staticProperties['$_staticB'] = &self::$_staticB;
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
        B::limitAccess('SampleToLimitAccess.php');
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
        return self::$_staticProperties[$propertyName];
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
        B::limitAccess('SampleToLimitAccess.php');
        self::$_staticProperties[$propertyName] = $value;
    }

}

global $_BreakpointDebugging_EXE_MODE;
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
    class TestClass extends \BaseTestClass
    {

    }

} else {
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
    class TestClass extends \BaseTestClass
    {
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
            switch ($propertyName) {
                case '_autoA':
                    // B::limitAccess('...');
                    break;
                case '_autoB':
                    // B::limitAccess('...');
                    break;
                default :
                    throw new \BreakpointDebugging_ErrorException(__CLASS__ . "::$$propertyName property does not exist.");
            }
            parent::__set($propertyName, $value);
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
            return parent::getStatic($propertyName);
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
            switch ($propertyName) {
                case '$_staticA':
                    // B::limitAccess('...');
                    break;
                case '$_staticB':
                    // B::limitAccess('...');
                    break;
                default :
                    throw new \BreakpointDebugging_ErrorException(__CLASS__ . "::$propertyName property does not exist.");
            }
            parent::setStatic($propertyName, $value);
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
    // $pTestClass->autoC = $testValue;
    // $pTestClass->autoC;

    T::setStatic('$_staticA', $testValue);
    B::assert(T::getStatic('$_staticA') === $testValue);
    B::assert(T::$_staticA === $testValue);
    T::setStatic('$_staticB', $testValue);
    B::assert(T::getStatic('$_staticB') === $testValue);
    B::assert(T::$_staticB === $testValue);
    // T::setStatic('$_staticC', $testValue);
    // T::getStatic('$_staticC');
}

echo '<pre>END.</pre>';

?>
