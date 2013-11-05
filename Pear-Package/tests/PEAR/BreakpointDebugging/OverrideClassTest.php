<?php

use \BreakpointDebugging as B;

// Overrides a class without inheritance.
class NativeClassOverriding extends \BreakpointDebugging_OverrideClass
{
    protected static $pr_nativeClassName = '\NativeClass'; // Native class name ( Variable name is fixed ).
    public static $object; // The static property must code by the same name.

    function __construct()
    {
        // This creates a native class object.
        $pNativeClass = self::newArray(self::$pr_nativeClassName, func_get_args());
        // This is the code to override a class without inheritance.
        parent::__construct($pNativeClass);
        // This refers to a static property.
        self::$object = &\NativeClass::$object;
    }

}

/**
 * Test class for BreakpointDebugging_OverrideClass.
 * Generated by PHPUnit on 2012-10-15 at 17:14:55.
 */
class BreakpointDebugging_OverrideClassTest extends \BreakpointDebugging_PHPUnitStepExecution_PHPUnitFrameworkTestCase
{
    protected $NativeClassOverriding;

    function setUp()
    {
        parent::setUp();
        // Creates class object which does a native-class overriding.
        $this->NativeClassOverriding = new NativeClassOverriding('test1', 'test2');
    }

    function tearDown()
    {
        // Destructs class object.
        $this->NativeClassOverriding = null;
        parent::tearDown();
    }

    /**
     * @covers \BreakpointDebugging_OverrideClass<extended>
     */
    public function test__get()
    {
        // Gets auto property which have been not defined.
        parent::assertTrue($this->NativeClassOverriding->float === 3.3);
    }

    /**
     * @covers \BreakpointDebugging_OverrideClass<extended>
     */
    public function test__set()
    {
        // Sets auto property which have been not defined.
        $this->NativeClassOverriding->float = 'New string.';
        parent::assertTrue($this->NativeClassOverriding->float === 'New string.');
    }

    /**
     * @covers \BreakpointDebugging_OverrideClass<extended>
     */
    public function test__call_A()
    {
        ob_start();
        // Calls auto-class-method which have been not defined in "NativeClassOverriding" class.
        $this->NativeClassOverriding->publicFunction();
    }

    /**
     * @covers \BreakpointDebugging_OverrideClass<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage call_user_func_array() expects parameter 1 to be a valid callback, class 'NativeClass' does not have a method 'notExistFunction'
     */
    public function test__call_B()
    {
        ob_start();
        // Calls auto-class-method which have been not defined.
        $this->NativeClassOverriding->notExistFunction();
    }

    /**
     * @covers \BreakpointDebugging_OverrideClass<extended>
     */
    public function test__callStatic()
    {
        ob_start();
        // Calls static-class-method which have been not defined in "NativeClassOverriding" class.
        NativeClassOverriding::publicStaticFunction();
        NativeClassOverriding::publicStaticFunction('test1', 'test2');
    }

    /**
     * @covers \BreakpointDebugging_OverrideClass<extended>
     *
     * Tests static property which set at "NativeClassOverriding::__construct()".
     */
    public function testStaicProperty()
    {
        global $object;

        // Gets static property.
        parent::assertTrue(NativeClassOverriding::$object === $object);
        // Sets static property.
        NativeClassOverriding::$object = 'dummy';
        parent::assertTrue(NativeClassOverriding::$object !== $object);
    }

}

?>
