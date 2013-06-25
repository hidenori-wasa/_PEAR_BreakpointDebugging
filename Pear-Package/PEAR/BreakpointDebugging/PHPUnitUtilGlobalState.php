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
 * Copyright (c) 2013, Hidenori Wasa
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
    private static $_globalSerializeKeysStoring = array ();
    private static $_propertySerializeKeysStoring = array ();

    /**
     * Stores variables. We should not store by serialization because serialization cannot store resource and array element reference variable.
     *
     * @param array $blacklist             The list to except from doing global variables backup.
     * @param array $variables
     * @param array &$variablesStoring
     * @param array &$serializeKeysStoring
     *
     * @return void
     */
    private static function _storeVariable(array $blacklist, array $variables, array &$variablesStoring, array &$serializeKeysStoring)
    {
        if (!empty($variablesStoring)) {
            return;
        }

        foreach ($variables as $key => $value) {
            if (in_array($key, $blacklist)
                || $value instanceof Closure
            ) {
                continue;
            }
            if ($key === 'GLOBALS'
                || (!is_object($value) && !is_array($value))
            ) {
                $variablesStoring[$key] = $value;
                continue;
            }
            do {
                if (is_array($value)) {
                    foreach ($value as $value2) {
                        if (is_object($value2)) {
                            break 2;
                        }
                        if (is_array($value2)) {
                            foreach ($value2 as $value3) {
                                if (is_object($value3)
                                    || is_array($value3)
                                ) {
                                    break 3;
                                }
                            }
                        }
                    }
                    $variablesStoring[$key] = $value;
                    continue 2;
                }
            } while (false);
            $variablesStoring[$key] = serialize($value);
            $serializeKeysStoring[$key] = null;
        }
    }

    /**
     * Restores variable. We must not restore by reference copy because variable ID changes.
     *
     * @param array &$variables
     * @param array $variablesStoring
     * @param array $serializeKeysStoring
     */
    private static function _restoreVariable(array &$variables, array $variablesStoring, array $serializeKeysStoring)
    {
        foreach ($variables as $key => $value) {
            if (!array_key_exists($key, $variablesStoring)) {
                unset($variables[$key]);
            }
        }
        foreach ($variablesStoring as $key => $value) {
            if (array_key_exists($key, $serializeKeysStoring)) {
                $variables[$key] = unserialize($value);
            } else {
                $variables[$key] = $value;
            }
        }
    }

    /**
     * Bug fix of parent class method. Also, ups Speed of parent class method.
     *
     * @param array $blacklist The list to except from doing global variables backup.
     *
     * @return void
     */
    static function backupGlobals(array $blacklist)
    {
        self::_storeVariable($blacklist, $GLOBALS, parent::$globals, self::$_globalSerializeKeysStoring);
    }

    /**
     * Bug fix of parent class method. Also, ups Speed of parent class method.
     *
     * @param array $blacklist Does not use.
     *
     * @return void
     */
    static function restoreGlobals(array $blacklist = array ())
    {
        self::_restoreVariable($GLOBALS, parent::$globals, self::$_globalSerializeKeysStoring);
    }

    /**
     * Bug fix of parent class method. Also, ups Speed of parent class method.
     *
     * @param array $blacklist The list to except from doing static attributes backup.
     *
     * @return void
     */
    static function backupStaticAttributes(array $blacklist)
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
                            // Static property backup.
                            // $backup[$attributeName] = serialize($attributeValue);
                            $backup[$attributeName] = $attributeValue; // ### To:
                        }
                    }
                }
            }

            if (!empty($backup)) {
                // ### Bug fix from:
                // Static class properties backup.
                // parent::$staticAttributes[$declaredClassName] = $backup;
                parent::$staticAttributes[$declaredClassName] = array (); // ### To:
                self::_storeVariable(array (), $backup, parent::$staticAttributes[$declaredClassName], self::$_propertySerializeKeysStoring); // ### To:
            }
        }
    }

    /**
     * Bug fix of parent class method. Also, ups Speed of parent class method.
     *
     * @return void
     */
    static function restoreStaticAttributes()
    {
        foreach (parent::$staticAttributes as $className => $staticAttributes) {
            $properties = array (); // ### To:
            self::_restoreVariable($properties, $staticAttributes, self::$_propertySerializeKeysStoring); // ### To:
            foreach ($staticAttributes as $name => $value) {
                $reflector = new ReflectionProperty($className, $name);
                $reflector->setAccessible(TRUE);
                // ### Bug fix from:
                // $reflector->setValue(unserialize($value));
                $reflector->setValue($properties[$name]); // ### To:
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

        parent::$globals = array ();
        parent::$staticAttributes = array ();
    }

}

?>
