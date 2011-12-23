<?php

// Native emulating class who is out of namespace.
class BaseNativeClass
{
    const BASE_NATIVE_CONST1 = 'BASE_NATIVE_CONST1';
    const BASE_NATIVE_CONST2 = 'BASE_NATIVE_CONST2';
    
    private $basePrivateProperty = 'basePrivateProperty';
    protected $baseProtectedProperty = 'baseProtectedProperty';
    public $basePublicProperty = 'basePublicProperty';
    private static $basePrivateStaticProperty = 'basePrivateStaticProperty';
    protected static $baseProtectedStaticProperty = 'baseProtectedStaticProperty';
    public static $basePublicStaticProperty = 'basePublicStaticProperty';
    
    function __construct()
    {
        global $otherClass;

        self::$basePrivateStaticProperty = $otherClass;
    }
    
    private function basePrivateFunction()
    {
        var_dump('Called basePrivateFunction.');
    }
    
    protected function baseProtectedFunction()
    {
        var_dump('Called baseProtectedFunction.');
    }

    public function basePublicFunction()
    {
        var_dump('Called basePublicFunction.');
    }

    private static function basePrivateStaticFunction()
    {
        var_dump('Called basePrivateStaticFunction.');
    }
    
    protected static function baseProtectedStaticFunction()
    {
        var_dump('Called baseProtectedStaticFunction.');
    }

    public static function basePublicStaticFunction()
    {
        var_dump('Called basePublicStaticFunction.');
    }
}

class NativeClass extends BaseNativeClass
{
    const NATIVE_CONST1 = 'NATIVE_CONST1';
    const NATIVE_CONST2 = 'NATIVE_CONST2';
    
    private $privateProperty = 'privateProperty';
    protected $protectedProperty = 'protectedProperty';
    public $publicProperty = 'publicProperty';
    private static $privateStaticProperty = 'privateStaticProperty';
    protected static $protectedStaticProperty = 'protectedStaticProperty';
    public static $publicStaticProperty = 'publicStaticProperty';
    
    function __construct()
    {
        global $otherClass;
        
        $this->privateProperty = $otherClass;
    }
    
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

    public static function publicStaticFunction()
    {
        var_dump('Called publicStaticFunction.');
    }
}

class OtherClass
{
    const OTHER_CONST1 = 'OTHER_CONST1';
    const OTHER_CONST2 = 'OTHER_CONST2';
    
    private $otherPrivateProperty = 'otherPrivateProperty';
    protected $otherProtectedProperty = 'otherProtectedProperty';
    public $otherPublicProperty = 'otherPublicProperty';
    private static $otherPrivateStaticProperty = 'otherPrivateStaticProperty';
    protected static $otherProtectedStaticProperty = 'otherProtectedStaticProperty';
    public static $otherPublicStaticProperty = 'otherPublicStaticProperty';
}

$otherClass = new OtherClass();
new NativeClass();

?>
