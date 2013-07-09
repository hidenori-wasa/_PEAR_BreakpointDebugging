<?php

/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */
use \BreakpointDebugging as B;

/**
 *
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: 3.6.11
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class BreakpointDebugging_PHPUnitUtilGlobalState extends \PHPUnit_Util_GlobalState
{
    /**
     * @var array Global variable serialization-keys storing.
     */
    private static $_globalSerializationKeysStoring = array ();

    /**
     * @var array Static attributes serialization-keys storing.
     */
    private static $_staticAttributesSerializationKeysStoring = array ();

    /**
     * Stores global variables.
     *
     * @param array $blacklist The list to except from storing global variables.
     *
     * @return void
     */
    static function backupGlobals(array $blacklist)
    {
        B::storeVariables($blacklist, $GLOBALS, parent::$globals, self::$_globalSerializationKeysStoring);
    }

    /**
     * Restores global variables.
     *
     * @param array $blacklist Does not use.
     *
     * @return void
     */
    static function restoreGlobals(array $blacklist = array ())
    {
        B::restoreVariables($GLOBALS, parent::$globals, self::$_globalSerializationKeysStoring);
    }

    /**
     * Stores static class attributes.
     *
     * @param array $blacklist The list to except from storing static class attributes.
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
            // $declaredClassName = 'PHPUnit_Framework_TestCase'; // For debug.
            // $declaredClassName = 'BreakpointDebugging_ErrorTest'; // For debug.
            // $declaredClassName = 'BreakpointDebugging'; // For debug.
            // Excepts unit test classes.
            if (stripos($declaredClassName, 'PHPUnit') === 0
                || stripos($declaredClassName, 'File_Iterator') === 0
                || stripos($declaredClassName, 'PHP_CodeCoverage') === 0
                || stripos($declaredClassName, 'PHP_Invoker') === 0
                || stripos($declaredClassName, 'PHP_Timer') === 0
                || stripos($declaredClassName, 'PHP_TokenStream') === 0
                || stripos($declaredClassName, 'sfYaml') === 0
                || stripos($declaredClassName, 'Text_Template') === 0
                || is_subclass_of($declaredClassName, 'PHPUnit_Util_GlobalState')
                || is_subclass_of($declaredClassName, 'PHPUnit_Framework_Test')
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
                    && $attribute->class === $declaredClassName
                ) {
                    $attributeName = $attribute->getName();
                    // If static property does not exist in black list (PHPUnit_Framework_TestCase::$backupStaticAttributesBlacklist).
                    if (!isset($blacklist[$declaredClassName])
                        || !in_array($attributeName, $blacklist[$declaredClassName])
                    ) {
                        $attribute->setAccessible(TRUE);
                        $attributeValue = $attribute->getValue();

                        if (!$attributeValue instanceof Closure) {
                            $backup[$attributeName] = $attributeValue;
                        }
                    }
                }
            }

            if (!empty($backup)) {
                // Stores static class properties.
                parent::$staticAttributes[$declaredClassName] = array ();
                B::storeVariables(array (), $backup, parent::$staticAttributes[$declaredClassName], self::$_staticAttributesSerializationKeysStoring, false);
            }
        }
    }

    /**
     * Restores static class attributes.
     *
     * @return void
     */
    static function restoreStaticAttributes()
    {
        foreach (parent::$staticAttributes as $className => $staticAttributes) {
            $properties = array ();
            B::restoreVariables($properties, $staticAttributes, self::$_staticAttributesSerializationKeysStoring);
            foreach ($staticAttributes as $name => $value) {
                $reflector = new ReflectionProperty($className, $name);
                $reflector->setAccessible(TRUE);
                $reflector->setValue($properties[$name]);
            }
        }
    }

    /**
     * Initializes static class attributes for next unit test file.
     *
     * @return void
     */
    static function initializeStaticAttributesForNextTestFile()
    {
        \BreakpointDebugging::limitAccess('BreakpointDebugging/PHPUnitTextUICommand.php', true);

        parent::$staticAttributes = array ();
        self::$_staticAttributesSerializationKeysStoring = array ();
    }

}

?>
