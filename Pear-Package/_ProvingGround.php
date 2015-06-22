<?php

require_once './BreakpointDebugging_Inclusion.php';
class A
{

    function something()
    {
        throw new \BreakpointDebugging_ErrorException('', 101);
    }

}

$a = new \A();
$a->something();

/*
if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // If production server.
}

\BreakpointDebugging::assert(true); // Assertion.

if (\BreakpointDebugging::isDebug()) { // If debug mode.
}*/