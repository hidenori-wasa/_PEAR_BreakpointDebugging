<?php

/**
 * This file can make verification of MySQLi_Result without writing a code by including.
 *
 * "*_Option.php" file does not use on release. Therefore, response time is zero on release.
 * These file names put "_" to become error when we do autoload.
 *
 * Note: I ignore error of following of "PHP_CodeSniffer" because this class is overriding.
 *       Method name "<class name>::<method name>" is not in camel caps format
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012, Hidenori Wasa
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
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/Validate/MySQLi
 */

namespace Validate;

/**
 * This is wrapper class of MySQLi_Result class for verification, and it is except release mode.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_Result extends MySQLi_Result_For_InAllCase
{

    /**
     * Rapper method of "MySQLi_Result::__construct()" for verification.
     *
     * @param object $pNativeClass "\MySQLi_Result" native class.
     * @param object $pMySqlI      "\Validate\MySQLi" class.
     */
    function __construct($pNativeClass, $pMySqlI)
    {
        assert(func_num_args() === 2);
        assert($pNativeClass instanceof \MySQLi_Result);
        assert($pMySqlI instanceof MySQLi);

        parent::__construct($pNativeClass, $pMySqlI);
    }

    /**
     * Rapper method of "MySQLi_Result::close()" for verification.
     *
     * @return Same.
     */
    function close()
    {
        assert(func_num_args() === 0);
        // This must not be closed.
        assert(!$this->pr_isClose);

        parent::close();
    }

    /**
     * Rapper method of "MySQLi_Result::data_seek()" for verification.
     *
     * @param int $offset Same.
     *
     * @return void
     */
    function data_seek($offset)
    {
        assert(func_num_args() === 1);
        assert(is_int($offset));
        assert(0 <= $offset && $offset < $this->pr_pNativeClass->num_rows);

        parent::data_seek($offset);
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_all()" for verification.
     * This method does not exist in "XAMPP 1.7.3".
     *
     * @param int $resulttype Same.
     *
     * @return Same
     */
    function fetch_all($resulttype = MYSQLI_NUM)
    {
        switch (func_num_args()) {
        case 1:
            assert(is_int($resulttype));
            assert($resulttype & MYSQLI_BOTH);
        case 0:
            break;
        default:
            assert(false);
        }

        return $this->pr_pNativeClass->fetch_all($resulttype);
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_array()" for verification.
     *
     * @param int $resulttype Same.
     *
     * @return Same.
     */
    function fetch_array($resulttype = MYSQLI_BOTH)
    {
        switch (func_num_args()) {
        case 1:
            assert(is_int($resulttype));
            assert($resulttype & MYSQLI_BOTH);
        case 0:
            break;
        default:
            assert(false);
        }

        return $this->pr_pNativeClass->fetch_array($resulttype);
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_assoc()" for verification.
     *
     * @return Same.
     */
    function fetch_assoc()
    {
        assert(func_num_args() === 0);

        return $this->pr_pNativeClass->fetch_assoc();
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_row()" for verification.
     *
     * @return Same.
     */
    function fetch_row()
    {
        assert(func_num_args() === 0);

        return $this->pr_pNativeClass->fetch_row();
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_field()" for verification.
     *
     * @return Same.
     */
    function fetch_field()
    {
        assert(func_num_args() === 0);

        return $this->pr_pNativeClass->fetch_field();
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_field_direct()" for verification.
     *
     * @param int $fieldNumber Same.
     *
     * @return Same.
     */
    function fetch_field_direct($fieldNumber)
    {
        assert(func_num_args() === 1);
        assert(is_int($fieldNumber));
        assert(0 <= $fieldNumber && $fieldNumber < $this->pr_pNativeClass->field_count);

        return parent::fetch_field_direct($fieldNumber);
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_fields()" for verification.
     *
     * @return Same.
     */
    function fetch_fields()
    {
        assert(func_num_args() === 0);
        assert($this->pr_pNativeClass->field_count > 0);

        return parent::fetch_fields();
    }

    /**
     * Rapper method of "MySQLi_Result::fetch_object()" for verification.
     *
     * @return Same.
     */
    function fetch_object()
    {
        switch (func_num_args()) {
        case 2:
            assert(is_array(func_get_arg(1)));
        case 1:
            assert(is_string(func_get_arg(0)));
        case 0:
            break;
        default:
            assert(false);
        }

        // Call class-auto-method by parameter array.
        return call_user_func_array(array($this->pr_pNativeClass, 'fetch_object'), func_get_args());
    }

    /**
     * Rapper method of "MySQLi_Result::field_seek()" for verification.
     *
     * @param int $fieldNumber Same.
     *
     * @return void
     */
    function field_seek($fieldNumber)
    {
        assert(func_num_args() === 1);
        assert(is_int($fieldNumber));
        assert(0 <= $fieldNumber && $fieldNumber < $this->pr_pNativeClass->field_count);

        parent::field_seek($fieldNumber);
    }

}

?>
