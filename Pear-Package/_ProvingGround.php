<?php

require_once './BreakpointDebugging_Inclusion.php';

if (BREAKPOINTDEBUGGING_IS_PRODUCTION) { // If production server.
}

\BreakpointDebugging::assert(true); // Assertion.

if (\BreakpointDebugging::isDebug()) { // If debug mode.
}