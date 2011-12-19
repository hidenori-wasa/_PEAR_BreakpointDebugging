<?php

// Native emulating class who is out of namespace.
class NativeClass
{
    private $privateProperty = 'privateProperty';
    protected $protectedProperty = 'protectedProperty';
    public $publicProperty = 'publicProperty';
    private static $privateStaticProperty = 'privateStaticProperty';
    protected static $protectedStaticProperty = 'protectedStaticProperty';
    public static $publicStaticProperty = 'publicStaticProperty';
    
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

?>
