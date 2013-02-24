<?php

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(false); // Checks the execution mode.

    const RECURSIVE_ARRAY = 'recursive array!';
/**
 * Clears recursive array element.
 *
 * @param array $parentArray  Parent array to search element.
 * @param array $parentsArray Array of parents to compare ID.
 *
 * @return array Array which changed.
 */
function _clearRecursiveArrayElement($parentArray, $parentsArray)
{
    // Creates array to change from recursive array to string.
    $changingArray = $parentArray;
    // Searches recursive array.
    foreach ($parentArray as $childKey => $childArray) {
        // If not recursive array.
        if (!is_array($childArray)) {
            continue;
        }
        // Stores the child array.
        $elementStoring = $parentArray[$childKey];
        // Checks reference of parent arrays by changing from child array to string.
        $parentArray[$childKey] = RECURSIVE_ARRAY;
        foreach ($parentsArray as $cmpParentArrays) {
            // If a recursive array.
            if (!is_array(current($cmpParentArrays))) {
                // Deletes recursive array reference.
                unset($changingArray[$childKey]);
                // Marks recursive array.
                $changingArray[$childKey] = RECURSIVE_ARRAY;
                // Restores child array.
                $parentArray[$childKey] = $elementStoring;
                continue 2;
            }
        }
        // Restores child array.
        $parentArray[$childKey] = $elementStoring;
        // Adds parent array.
        $parentsArray[][$childKey] = &$parentArray[$childKey];
        // Calls this function recursively.
        $changingArray[$childKey] = _clearRecursiveArrayElement($parentArray[$childKey], $parentsArray);
        // Takes out parent array.
        array_pop($parentsArray);
    }
    return $changingArray;
}

/**
 * Clears recursive array element.
 *
 * @param array &$recursiveArray Recursive array.
 *
 * @return array Array which changed.
 */
function clearRecursiveArrayElement(&$recursiveArray)
{
    $parentsArray = array (array (&$recursiveArray));
    return _clearRecursiveArrayElement($recursiveArray, $parentsArray);
}

$testArray['element'] = 'String.';
$testArray['recursive'] = &$testArray;
$testArray['component']['recursive'] = &$testArray;
$testArray['component']['element'] = 'String.';
$testArray['component']['recursive2'] = &$testArray['component'];
$testArray['component']['component']['recursive'] = &$testArray['component'];
$testArray['component']['component']['element'] = 'String.';

$expectedArray['element'] = 'String.';
$expectedArray['recursive'] = RECURSIVE_ARRAY;
$expectedArray['component']['recursive'] = RECURSIVE_ARRAY;
$expectedArray['component']['element'] = 'String.';
$expectedArray['component']['recursive2'] = RECURSIVE_ARRAY;
$expectedArray['component']['component']['recursive'] = RECURSIVE_ARRAY;
$expectedArray['component']['component']['element'] = 'String.';

$resultArray = clearRecursiveArrayElement($testArray);
if ($expectedArray['element'] === $resultArray['element']
    && $expectedArray['recursive'] === $resultArray['recursive']
    && $expectedArray['component']['recursive'] === $resultArray['component']['recursive']
    && $expectedArray['component']['element'] === $resultArray['component']['element']
    && $expectedArray['component']['recursive2'] === $resultArray['component']['recursive2']
    && $expectedArray['component']['component']['recursive'] === $resultArray['component']['component']['recursive']
    && $expectedArray['component']['component']['element'] === $resultArray['component']['component']['element']
) {
    echo '<pre>Success!</pre>';
} else {
    var_dump($resultArray);
}

?>
