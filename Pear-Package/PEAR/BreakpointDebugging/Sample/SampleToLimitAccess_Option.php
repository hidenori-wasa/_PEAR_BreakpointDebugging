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
     * Limits properties accessing.
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct();

        $tmp = array (
            'BreakpointDebugging/Sample/SampleToLimitAccess.php',
            'BreakpointDebugging/Sample/SampleToLimitAccess_Option.php'
        );
        self::$staticProperties['$_staticA>limits'] = $tmp;
        self::$staticProperties['$_staticB>limits'] = $tmp;
        $this->autoProperties['_autoA>limits'] = $tmp;
        $this->autoProperties['_autoB>limits'] = $tmp;
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
        B::limitAccess($this->autoProperties[$propertyName . '>limits']);
        parent::__set($propertyName, $value);
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
        B::limitAccess(self::$staticProperties[$propertyName . '>limits']);
        parent::setStatic($propertyName, $value);
    }

    /**
     * Something.
     *
     * @param mixed $testValue The test value.
     *
     * @return void
     */
    function somthing($testValue)
    {
        $this->_autoA = $testValue;
        B::assert($this->_autoA === $testValue);
        $this->_autoB = $testValue;
        B::assert($this->_autoB === $testValue);
        // $this->_notExist = $testValue;
        // $this->_notExist;

        T::setStatic('$_staticA', $testValue);
        B::assert(T::getStatic('$_staticA') === $testValue);
        T::setStatic('$_staticB', $testValue);
        B::assert(T::getStatic('$_staticB') === $testValue);
        // T::setStatic('$_notExist', $testValue);
        // T::getStatic('$_notExist');
    }

}

?>
