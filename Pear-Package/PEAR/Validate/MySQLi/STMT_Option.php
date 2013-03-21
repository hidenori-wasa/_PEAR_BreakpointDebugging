<?php

/**
 * This file can make verification of MySQLi_STMT without writing a code by including.
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
 * This is wrapper class of MySQLi_STMT class for verification, and it is except release mode.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_STMT extends MySQLi_STMT_InAllCase
{

    /**
     * @var bool Does a results of rows exist?
     */
    private $_pr_isExistResultRows = false;

    /**
     * @var bool Is this buffering a results of rows?
     */
    private $_pr_isBuffering = false;

    /**
     * Rapper method of "MySQLi_STMT::__construct()" for verification.
     *
     * @param object $pNativeClass "\MySQLi_STMT" native class.
     * @param object $pMySqlI      "\Validate\MySQLi" class.
     */
    function __construct($pNativeClass, $pMySqlI)
    {
        assert(func_num_args() === 2);
        assert($pNativeClass instanceof \MySQLi_STMT);
        assert($pMySqlI instanceof MySQLi);

        parent::__construct($pNativeClass, $pMySqlI);
    }

    /**
     * "\Validate\MySQLi_STMT::safeBindParam()" for verification.
     *
     * @param array $refParams Reference parameters.
     *
     * @return void
     *
     * @example safeBindParam(array('is', &$inputPercentage, &$inputCustomerName));
     */
    function safeBindParam($refParams)
    {
        assert(is_string($refParams[0]));
        assert(strlen($refParams[0]) === count($refParams) - 1);

        parent::safeBindParam($refParams);
    }

    /**
     * Rapper method of "MySQLi_STMT::bind_param()" for verification.
     * Warning: Signature is different because this is a variable length reference parameter.
     *
     * @param array $refParams Reference parameters.
     *
     * @return void
     *
     * @example bind_param( array( $format, &$variable1, &$variable2));
     */
    function bind_param($refParams)
    {
        assert(func_num_args() === 1);
        assert(is_array($refParams));

        $return = parent::bind_param($refParams);
        assert($return);
    }

    /**
     * Rapper method of "MySQLi_STMT::execute()" for verification.
     *
     * @return void
     */
    function execute()
    {
        assert(func_num_args() === 0);
        assert(!$this->_pr_isExistResultRows);

        parent::execute();

        // When the result-rows exists.
        if ($this->pr_pNativeClass->field_count > 0) {
            $this->_pr_isExistResultRows = true;
        }
    }

    /**
     * Rapper method of "MySQLi_STMT::bind_result()" for verification.
     * Warning: Signature is different because this is a variable length reference parameter.
     *
     * @param array $refParams Reference parameters.
     *
     * @return void
     *
     * @example bind_result( array( &variable1, &variable2));
     */
    function bind_result($refParams)
    {
        assert(func_num_args() === 1);
        assert(is_array($refParams));

        $return = parent::bind_result($refParams);
        assert($return);
    }

    /**
     * Rapper method of "MySQLi_STMT::fetch()" for verification.
     *
     * @return Same.
     */
    function fetch()
    {
        assert(func_num_args() === 0);
        assert($this->_pr_isExistResultRows);

        return parent::fetch();
    }

    /**
     * Rapper method of "MySQLi_STMT::close()" for verification.
     *
     * @return void
     */
    function close()
    {
        assert(func_num_args() === 0);
        // This must not close.
        assert(!$this->pr_isClose);
        // The result-rows doesn't exist.
        $this->_pr_isExistResultRows = false;

        $return = parent::close();
        assert($return);
    }

    /**
     * Rapper method of "MySQLi_STMT::store_result()" for verification.
     *
     * @return void
     */
    function store_result()
    {
        assert(func_num_args() === 0);
        assert(!$this->_pr_isBuffering);
        $this->_pr_isBuffering = true;

        parent::store_result();
    }

    /**
     * Rapper method of "MySQLi_STMT::free_result()" for verification.
     *
     * @return Same.
     */
    function free_result()
    {
        assert(func_num_args() === 0);
        assert($this->_pr_isBuffering);
        $this->_pr_isBuffering = false;

        $this->pr_pNativeClass->free_result();
    }

    /**
     * Rapper method of "MySQLi_STMT::send_long_data()" for verification.
     *
     * @param int    $paramNumber Same.
     * @param string $sendData    Same.
     *
     * @return void
     */
    function send_long_data($paramNumber, $sendData)
    {
        assert(func_num_args() === 2);
        assert(is_int($paramNumber));
        assert(0 <= $paramNumber && $paramNumber < $this->pr_pNativeClass->param_count);
        assert(is_string($sendData));

        parent::send_long_data($paramNumber, $sendData);
    }

    /**
     * Rapper method of "MySQLi_STMT::prepare()" for verification.
     *
     * @param string $query Same.
     *
     * @return void
     */
    function prepare($query)
    {
        assert(false); // This isn't used. Because, it is possible to substitute in MySQLi::prepare().
        assert(func_num_args() === 1);
        assert(is_string($query));

        $return = $this->pr_pNativeClass->prepare($query);
        assert($return);
    }

    /**
     * Rapper method of "MySQLi_STMT::result_metadata()" for verification.
     *
     * @return \Validate\MySQLi_Result
     */
    function result_metadata()
    {
        assert(func_num_args() === 0);

        return parent::result_metadata();
    }

    /**
     * Rapper method of "MySQLi_STMT::reset()" for verification.
     *
     * @return void
     */
    function reset()
    {
        assert(func_num_args() === 0);
        assert($this->_pr_isExistResultRows);
        // The result-rows doesn't exist.
        $this->_pr_isExistResultRows = false;

        parent::reset();
    }

    /**
     * Rapper method of "MySQLi_STMT::data_seek()" for verification.
     *
     * @param int $rowNumber Same.
     *
     * @return Same.
     */
    function data_seek($rowNumber)
    {
        assert(func_num_args() === 1);
        assert(0 <= $rowNumber && $rowNumber < $this->pr_pNativeClass->num_rows);

        $this->pr_pNativeClass->data_seek($rowNumber);
    }

    /**
     * Rapper method of "MySQLi_STMT::attr_get()" for verification.
     *
     * @param int $attr Same.
     *
     * @return Same.
     */
    function attr_get($attr)
    {
        assert(func_num_args() === 1);
        assert(is_int($attr));

        $attrValue = $this->pr_pNativeClass->attr_get($attr);
        assert($attrValue !== false);
        return $attrValue;
    }

    /**
     * Rapper method of "MySQLi_STMT::attr_set()" for verification.
     *
     * @param int $attr Same.
     * @param int $mode Same.
     *
     * @return void
     */
    function attr_set($attr, $mode)
    {
        assert(func_num_args() === 2);
        assert(is_int($attr));
        assert(is_int($mode));

        $return = $this->pr_pNativeClass->attr_set($attr, $mode);
        assert($return);
    }

}

?>
