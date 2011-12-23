<?php

// This file is sample code.

namespace Your_Name;
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

require_once './BreakpointDebugging_MySetting.php'; // You must include.
require_once './NativeClass.php'; // Test class.

/**
 * Function has been fixed.
 *
 * @param object $object
 *
 */
function fnTestC($object)
{
    assert(false); // This is error location.
    // throw new \Exception('test exception.'); // I am creating the exception handling of the remainder.
}

/**
 * Function has been not fixed.
 *
 * @param object $object
 *
 */
function fnTestB($object)
{
    static $isRegister; B::registerNotFixedLocation( $isRegister); // Register the function to be not fixed.
    
    fnTestC($object);
}

/**
 * Function has been fixed.
 *
 * @param object $object
 *
 */
function fnTestA($object)
{
    fnTestB($object);
}

fnTestA(new \NativeClass());
echo 'END';

?>
