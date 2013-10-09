<?php

class PHPUnit1Test extends \BreakpointDebugging_PHPUnitStepExecution_PHPUnitFrameworkTestCase
{
    static $initialValueOfGlobal;
    static $initialReferenceOfGlobal;
    static $initialValueOfAutoProperty;
    static $initialValueOfStaticProperty;
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
    public function testStoring_A()
    {
        // Stores the initial value and the initial reference.
        self::$initialReferenceOfGlobal = &$_FILES;
        self::$initialValueOfGlobal = $_FILES;
        self::$initialValueOfAutoProperty = $this->_testObject->autoProperty;
        self::$initialValueOfStaticProperty = \tests_PEAR_AClass::$staticProperty;

        // Changes the value and the reference.
        $_FILES = &$referenceChange;
        $_FILES = 'The change value of global variable.';
        $this->_testObject->autoProperty = 'The change value of auto property.';
        \tests_PEAR_AClass::$staticProperty = 'The change value of static property.';
    }

    /**
     * @covers \Example<extended>
     */
    public function testStoring_B()
    {
        // Asserts the value and the reference.
        parent::assertTrue(array (self::$initialReferenceOfGlobal) === array ($_FILES));
        parent::assertTrue(self::$initialValueOfGlobal === $_FILES);
        parent::assertTrue(self::$initialValueOfAutoProperty === $this->_testObject->autoProperty);
        parent::assertTrue(self::$initialValueOfStaticProperty === \tests_PEAR_AClass::$staticProperty);

        // Changes the value and the reference.
        $_FILES = &$referenceChange2;
        $_FILES = 'The change value of global variable. 2';
        $this->_testObject->autoProperty = 'The change value of auto property. 2';
        \tests_PEAR_AClass::$staticProperty = 'The change value of static property. 2';
    }

}

?>
