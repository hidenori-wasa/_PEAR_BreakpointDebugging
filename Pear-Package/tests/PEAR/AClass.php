<?php

class tests_PEAR_AClass
{
    static $staticProperty = 'Initial value of static property.';
    public $autoProperty = 'Initial value of auto property.';
    static $recursiveStaticProperty = array ();

}

\tests_PEAR_AClass::$recursiveStaticProperty = array (&\tests_PEAR_AClass::$recursiveStaticProperty, 'Recursive static property element.');

?>
