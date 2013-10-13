<?php

use \BreakpointDebugging as B;

class PHPUnit2Test extends \BreakpointDebugging_PHPUnitStepExecution_PHPUnitFrameworkTestCase
{
    private $_testObject;

    function setUp()
    {
        parent::setUp();

        $this->_testObject = new \tests_PEAR_AClass();
    }

    function tearDown()
    {
        $this->_testObject = null;

        parent::tearDown();
    }

    /**
     * @covers \Example<extended>
     */
    public function testStoring_C()
    {
        // Asserts the value and the reference.
        parent::assertTrue(array (&\PHPUnit1Test::$initialReferenceOfGlobal) === array (&$_FILES));
        parent::assertTrue(\PHPUnit1Test::$initialValueOfGlobal === $_FILES);
        parent::assertTrue(\PHPUnit1Test::$initialValueOfAutoProperty === $this->_testObject->autoProperty);
        parent::assertTrue(\PHPUnit1Test::$initialValueOfStaticProperty === \tests_PEAR_AClass::$staticProperty);
        parent::assertTrue(count(array_diff(\PHPUnit1Test::$initialValueOfRecursiveStaticProperty, \tests_PEAR_AClass::$recursiveStaticProperty)) === 0);
        parent::assertTrue(count(array_diff(\tests_PEAR_AClass::$recursiveStaticProperty, \PHPUnit1Test::$initialValueOfRecursiveStaticProperty)) === 0);

        // Changes the value and the reference.
        $_FILES = &$referenceChange3;
        $_FILES = 'The change value of global variable. 3';
        $this->_testObject->autoProperty = 'The change value of auto property. 3';
        \tests_PEAR_AClass::$staticProperty = 'The change value of static property. 3';
        \tests_PEAR_AClass::$recursiveStaticProperty = 'The change value of recursive static property. 3';
    }

    /**
     * @covers \Example<extended>
     */
    public function testStoring_D()
    {
        // Asserts the value and the reference.
        parent::assertTrue(array (&\PHPUnit1Test::$initialReferenceOfGlobal) === array (&$_FILES));
        parent::assertTrue(\PHPUnit1Test::$initialValueOfGlobal === $_FILES);
        parent::assertTrue(\PHPUnit1Test::$initialValueOfAutoProperty === $this->_testObject->autoProperty);
        parent::assertTrue(\PHPUnit1Test::$initialValueOfStaticProperty === \tests_PEAR_AClass::$staticProperty);
        parent::assertTrue(count(array_diff(\PHPUnit1Test::$initialValueOfRecursiveStaticProperty, \tests_PEAR_AClass::$recursiveStaticProperty)) === 0);
        parent::assertTrue(count(array_diff(\tests_PEAR_AClass::$recursiveStaticProperty, \PHPUnit1Test::$initialValueOfRecursiveStaticProperty)) === 0);
    }

}

?>
