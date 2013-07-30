<?php

/**
 * Utility for static state.
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
 * @category   PHP
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
 * Utility for static state.
 *
 * @category   PHP
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
     * @var array Global variable serialization-keys storage.
     */
    private static $_globalSerializationKeysStorage = array ();

    /**
     * @var array Static attributes serialization-keys storage.
     */
    private static $_staticAttributesSerializationKeysStorage = array ();

    /**
     * Stores global variables.
     *
     * @param array  $blacklist      The list to except from storage global variables.
     * @param bool   $checkJustice   Checks justice?
     * @param string $testMethodName The test class method name.
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    static function backupGlobals(array $blacklist, $checkJustice = false, $testMethodName = '')
    {
        B::storeVariables($blacklist, $GLOBALS, parent::$globals, self::$_globalSerializationKeysStorage, true, $checkJustice, $testMethodName);
    }

    /**
     * Restores global variables.
     *
     * @param array $blacklist Does not use.
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    static function restoreGlobals(array $blacklist = array ())
    {
        B::restoreVariables($GLOBALS, parent::$globals, self::$_globalSerializationKeysStorage);
    }

    /**
     * Stores static class attributes.
     *
     * @param array $blacklist    The list to except from storage static class attributes.
     * @param bool  $checkJustice Checks justice?
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    static function backupStaticAttributes(array $blacklist, $checkJustice = false)
    {
        // Scans the declared classes.
        $declaredClasses = get_declared_classes();
        for ($i = count($declaredClasses) - 1; $i >= 0; $i--) {
            $declaredClassName = $declaredClasses[$i];
            // Excepts existing class.
            if (array_key_exists($declaredClassName, parent::$staticAttributes)) {
                continue;
            }
            // Excepts unit test classes.
            if (preg_match('`^ (PHP (Unit | (_ (CodeCoverage | Invoker | (T (imer | oken_Stream))))) | File_Iterator | sfYaml | Text_Template )`xXi', $declaredClassName) === 1
                || is_subclass_of($declaredClassName, 'PHPUnit_Util_GlobalState') // For extended class of my package.
                || is_subclass_of($declaredClassName, 'PHPUnit_Framework_Test')
            ) {
                continue;
            }
            // Class reflection.
            $classReflection = new \ReflectionClass($declaredClassName);
            // If it is not user defined class.
            if (!$classReflection->isUserDefined()) {
                continue;
            }

            $backup = array ();
            // Static properties reflection.
            foreach ($classReflection->getProperties(\ReflectionProperty::IS_STATIC) as $attribute) {
                // If it is not property of base class. Because reference variable cannot be extended.
                if ($attribute->class === $declaredClassName) {
                    if ($checkJustice) {
                        B::exitForError(
                            PHP_EOL
                            . PHP_EOL
                            . '<b>We must use autoload by "new ' . $declaredClassName . '"' . PHP_EOL
                            . "\t" . 'instead of include "*.php" file of "class ' . $declaredClassName . '" which defines static status inside function or class method' . PHP_EOL
                            . "\t" . 'because "php" version 5.3.0 cannot detect an included static status definition realtime.</b>' . PHP_EOL
                            . ' '
                        );
                    }
                    $attributeName = $attribute->name;
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
            if ($checkJustice) {
                continue;
            }

            parent::$staticAttributes[$declaredClassName] = array ();
            if (!empty($backup)) {
                // Stores static class properties.
                B::storeVariables(array (), $backup, parent::$staticAttributes[$declaredClassName], self::$_staticAttributesSerializationKeysStorage);
            }

            // Checks existence of local static variable of static class method.
            foreach ($classReflection->getMethods(ReflectionMethod::IS_STATIC) as $methodReflection) {
                if ($methodReflection->class === $declaredClassName) {
                    $result = $methodReflection->getStaticVariables();
                    // If static variable has been existing.
                    if (!empty($result)) {
                        B::exitForError(
                            PHP_EOL
                            . PHP_EOL
                            . '<b>We must use private static property instead of use local static variable of class static method' . PHP_EOL
                            . "\t" . 'because "php" version 5.3.0 cannot restore its value.' . PHP_EOL
                            . "\t" . 'FILE: ' . $methodReflection->getFileName() . PHP_EOL
                            . "\t" . 'LINE: ' . $methodReflection->getStartLine() . PHP_EOL
                            . "\t" . 'CLASS: ' . $methodReflection->class . PHP_EOL
                            . "\t" . 'METHOD: ' . $methodReflection->name . '</b>' . PHP_EOL
                            . ' '
                        );
                    }
                }
            }
        }
    }

    /**
     * Restores static class attributes.
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    static function restoreStaticAttributes()
    {
        foreach (parent::$staticAttributes as $className => $staticAttributes) {
            $properties = array ();
            B::restoreVariables($properties, $staticAttributes, self::$_staticAttributesSerializationKeysStorage);
            foreach ($staticAttributes as $name => $value) {
                $reflector = new ReflectionProperty($className, $name);
                $reflector->setAccessible(TRUE);
                $reflector->setValue($properties[$name]);
            }
        }
    }

}

?>
