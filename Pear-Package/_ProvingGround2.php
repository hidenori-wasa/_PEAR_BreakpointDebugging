<?php

include_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;

$refStoreage[0] = &$_FILES;
$storeage[0] = $_FILES;

function func()
{
    $_FILES = 'Changes the value.';
    $_FILES = &$aReference2;
    unset($_FILES);
}

func();

$_FILES = &$refStoreage[0];
$_FILES = $storeage[0];
var_dump($_FILES, $refStoreage[0]);
exit;
