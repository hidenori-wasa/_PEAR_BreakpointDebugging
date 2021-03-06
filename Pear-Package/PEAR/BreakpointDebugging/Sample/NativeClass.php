<?php

// Native emulating class who is out of namespace.
class BaseNativeClass
{
    const BOOL_TRUE = true;
    const BOOL_FALSE = false;

    private $private = 'ThisIsNotDisplayed';
    protected $integer = 3;
    public $float = 3.3;
    private static $privateStatic = 'ThisIsNotDisplayed';
    protected static $string = 'GHI';
    public static $object;
    protected static $array;
    protected static $resource;
    protected static $null = null;

    function __construct($param1 = null, $param2 = null)
    {
        global $_BreakpointDebugging_testObject, $array;

        self::$object = $_BreakpointDebugging_testObject;
        self::$array = $array;
        self::$resource = tmpfile();
    }

    private function privateFunction()
    {
        var_dump('Called basePrivateFunction.');
    }

    protected function protectedFunction()
    {
        var_dump('Called baseProtectedFunction.');
    }

    public function publicFunction()
    {
        var_dump('Called basePublicFunction.');
    }

    private static function privateStaticFunction()
    {
        var_dump('Called basePrivateStaticFunction.');
    }

    protected static function protectedStaticFunction()
    {
        var_dump('Called baseProtectedStaticFunction.');
    }

    public static function publicStaticFunction()
    {
        var_dump('Called basePublicStaticFunction.');
    }

}

class NativeClass extends \BaseNativeClass
{

    private function privateFunction()
    {
        var_dump('Called privateFunction.');
    }

    protected function protectedFunction()
    {
        var_dump('Called protectedFunction.');
    }

    public function publicFunction()
    {
        var_dump('Called publicFunction.');
    }

    private static function privateStaticFunction()
    {
        var_dump('Called privateStaticFunction.');
    }

    protected static function protectedStaticFunction()
    {
        var_dump('Called protectedStaticFunction.');
    }

    public static function publicStaticFunction($param1 = null, $param2 = null)
    {
        var_dump('Called publicStaticFunction.');
    }

}

class OtherClass
{
    const OTHER_CONST1 = ' CONST1';
    const OTHER_CONST2 = 'CONST2 ';

    private $private = '<Private>';
    protected $protected = ' Protected';
    public $public = 'Public ';
    private static $privateStatic = 'PrivateStatic';
    protected static $protectedStatic = 'ProtectedStatic';
    public static $publicStatic = 'PublicStatic';

}

global $baseArray;
$baseArray = array (' baseArrayElement1', 'baseArrayElement2 ');

global $_BreakpointDebugging_testObject;
$_BreakpointDebugging_testObject = new \OtherClass();

global $array;
$array = array (true, 'bool' => false, 222 => 2, 2.2, 'DEF', $_BreakpointDebugging_testObject, $baseArray, tmpfile(), null);

global $varietyObject;
$varietyObject = new \NativeClass();

?>
