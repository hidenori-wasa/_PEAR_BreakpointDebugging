<?php

/**
 * Bug fix of parent class method.
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012-2013, Hidenori Wasa
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
/**
 * Bug fix of parent class method.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_PHPUnitUtilGlobalState extends \PHPUnit_Util_GlobalState
{
    /**
     * Bug fix of parent class method. Also, ups Speed of parent class method.
     *
     * @param array $blacklist The list to except from doing static attributes backup.
     *
     * @return void
     */
    public static function backupStaticAttributes(array $blacklist)
    {
        if (!empty(parent::$staticAttributes)) {
            return;
        }

        // Scans the declared classes.
        $declaredClasses = get_declared_classes();
        for ($i = count($declaredClasses) - 1; $i >= 0; $i--) {
            $declaredClassName = $declaredClasses[$i];
            // $declaredClassName = 'SomethingTest'; // For debug.
            // $declaredClassName = 'PHPUnit_Framework_TestCase'; // For debug.
            // Excepts unit test classes.
            if (stripos($declaredClassName, 'PHPUnit') === 0
                || stripos($declaredClassName, 'File_Iterator') === 0
                || stripos($declaredClassName, 'PHP_CodeCoverage') === 0
                || stripos($declaredClassName, 'PHP_Invoker') === 0
                || stripos($declaredClassName, 'PHP_Timer') === 0
                || stripos($declaredClassName, 'PHP_TokenStream') === 0
                || stripos($declaredClassName, 'sfYaml') === 0
                || stripos($declaredClassName, 'Text_Template') === 0
                // ### Bug fixed from: && !$declaredClasses[$i] instanceof PHPUnit_Framework_Test
                || strripos($declaredClassName, 'Test', - strlen('Test')) === strlen($declaredClassName) - strlen('Test') // ### To:
            // However, this has been not supporting except "*Test.php".
            ) {
                continue;
            }
            // Class reflection.
            $classReflection = new ReflectionClass($declaredClassName);
            if (!$classReflection->isUserDefined()) {
                break;
            }

            $backup = array ();
            // Properties reflection.
            foreach ($classReflection->getProperties() as $attribute) {
                // If static property existing in declared class.
                if ($attribute->isStatic()
                    && $attribute->class === $declaredClassName // ### Bug fixed by this additional code.
                ) {
                    $attributeName = $attribute->getName();
                    // If static property does not exist in black list (PHPUnit_Framework_TestCase::$backupStaticAttributesBlacklist).
                    if (!isset($blacklist[$declaredClassName])
                        || !in_array($attributeName, $blacklist[$declaredClassName])
                    ) {
                        $attribute->setAccessible(TRUE);
                        $attributeValue = $attribute->getValue();

                        if (!$attributeValue instanceof Closure) {
                            // ### Bug fix from:
                            //// Static property backup.
                            // $backup[$attributeName] = serialize($attributeValue);
                            // ### To:
                            // Stores static property. We must not store by serialization because serialization cannot store resource and array element reference variable.
                            $backup[$attributeName] = $attributeValue;
                        }
                    }
                }
            }

            if (!empty($backup)) {
                // Static class properties backup.
                parent::$staticAttributes[$declaredClassName] = $backup;
            }
        }
    }

    /**
     * Bug fix of parent class method. Also, ups Speed of parent class method.
     *
     * @return void
     */
    public static function restoreStaticAttributes()
    {
        foreach (parent::$staticAttributes as $className => $staticAttributes) {
            foreach ($staticAttributes as $name => $value) {
                $reflector = new ReflectionProperty($className, $name);
                $reflector->setAccessible(TRUE);
                // ### Bug fix from:
                // $reflector->setValue(unserialize($value));
                // ### To:
                // Restores static property. We must not restore by reference copy because variable ID changes.
                $reflector->setValue($value);
            }
        }
    }

    /**
     * Initializes global variables for next unit test file.
     *
     * @return void
     */
    static function initializeGlobalsForNextTestFile()
    {
        \BreakpointDebugging::limitAccess('BreakpointDebugging/PHPUnitTextUICommand.php', true);

        parent::$staticAttributes = array ();
    }

}

?>
