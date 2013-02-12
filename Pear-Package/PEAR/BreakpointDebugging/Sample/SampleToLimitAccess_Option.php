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
        self::$staticPropertyLimitings['$_staticA'] = $tmp;
        self::$staticPropertyLimitings['$_staticB'] = $tmp;
        $this->autoPropertyLimitings['_autoA'] = $tmp;
        $this->autoPropertyLimitings['_autoB'] = $tmp;
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
        B::limitAccess($this->autoPropertyLimitings[$propertyName]);

        parent::__set($propertyName, $value);
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
        B::limitAccess(self::$staticPropertyLimitings[$propertyName]);

        return parent::refStatic($propertyName);
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

        $staticA = &T::refStatic('$_staticA');
        $staticA = $testValue;
        B::assert(T::getStatic('$_staticA') === $staticA);
        $staticB = &T::refStatic('$_staticB');
        $staticB = $testValue;
        B::assert(T::getStatic('$_staticB') === $staticB);
        // $notExist = &T::refStatic('$_notExist');
        // T::getStatic('$_notExist');
    }

}

?>
