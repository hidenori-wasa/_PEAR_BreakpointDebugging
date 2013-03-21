<?php

/**
 * This file can make error handling of MySQLi without writing a code by including.
 *
 * Note: I ignore error of following of "PHP_CodeSniffer" because this class is overriding.
 *       Method name "<class name>::<method name>" is not in camel caps format
 *
 * ### The advantage of this package. ###
 * This package can do error handling and verification by using wrapper class of MySQLi, MySQLi_STMT and MySQLi_Result class.
 * Also, this package accelerates because verification code disappears on release.
 *
 * ### The execution procedure. ###
 * Procedure 1: Please, set php file format to utf8, but we should create backup of php files because multibyte strings may be destroyed.
 * Procedure 2: Please, copy *_MySetting*.php as your project php file.
 * Procedure 3: Please, edit *_MySetting*.php for customize.
 *      Then, it fixes part setting about all debugging modes.
 * Procedure 4: Please, copy following in your project php file.
 *      "require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';"
 * Procedure 5: Please, rewrite following in your project php file.
 *      from "new \MySQLi" to "new \Validate\MySQLi"
 * Procedure 6: Please, change signature because this is a variable length reference parameter.
 *      MySQLi_STMT::bind_param(), MySQLi_STMT::bind_result()
 * Procedure 7: Please, copy following in your project "my.ini" or "my.cnf" file.
 *      [mysqld]
 *      # It sets character sets of server, data base, table, column to "utf8". It sets collating sequence to the default "utf8_general_ci".
 *      character_set_server=utf8
 *      # It ignores character sets information which was sent from client and it uses character sets of default of server.
 *      skip-character-set-client-handshake
 *      # It writes database name, a table name, a table alias name in storage with lowercase. Therefore, it works in all OS.
 *      lower_case_table_names=1
 *      # "init_connect" is SQL statement to execute when connecting.
 *      # "SET NAMES 'utf8'" sets
 *      #       character_set_client (Character sets which client sends)
 *      #       character_set_connection (Character sets of literal character string)
 *      #       character_set_results (Character sets of query-result to return to client) to "utf8".
 *      #       And it sets collation_connection (Collating sequence of connection character sets) to the default collating sequence of "utf8"( utf8_general_ci).
 *      init_connect="SET NAMES 'utf8'"
 *      [mysqldump]
 *      default-character-set=utf8
 *      [mysql]
 *      default-character-set=utf8
 *
 * ### Exception hierarchical structure ###
 * PEAR_Exception
 *      BreakpointDebugging_Exception
 *          \Validate\MySQLi_Exception
 *              \Validate\MySQLi_Query_Exception
 *                  \Validate\MySQLi_Query_Warning_Exception
 *                  \Validate\MySQLi_Query_Error_Exception
 *              \Validate\MySQLi_Connect_Exception
 *              \Validate\MySQLi_Error_Exception
 *
 * ### Useful function index. ###
 * Safe "\Validate\MySQLi::query()".
 *      function \Validate\MySQLi::safeQuery($query, $queryParamType)
 * Safe "\Validate\MySQLi_STMT::bind_param()".
 *      function \Validate\MySQLi_STMT::safeBindParam($refParams)
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

use \BreakpointDebugging as B;

global $_BreakpointDebugging_EXE_MODE;

require_once __DIR__ . '/../BreakpointDebugging/OverrideClass.php';
require_once './PEAR_Setting/Validate_MySQLi_MySetting.php';

/**
 * This class is own package exception.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_Exception extends \BreakpointDebugging_Exception
{

}

/**
 * This class is own package query exception.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_Query_Exception extends MySQLi_Exception
{

}

/**
 * This class is own package query warning exception.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_Query_Warning_Exception extends MySQLi_Query_Exception
{

}

/**
 * This class is own package query error exception.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_Query_Error_Exception extends MySQLi_Query_Exception
{

}

/**
 * This class is own package connect exception.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_Connect_Exception extends MySQLi_Exception
{

}

/**
 * This class is own package error exception.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_Error_Exception extends MySQLi_Exception
{

}

/**
 * This is wrapper class of MySQLi class for error handling.
 *
 * @category PHP
 * @package  Validate_MySQLi
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate/MySQLi
 */
class MySQLi_InAllCase extends \BreakpointDebugging_OverrideClass
{
    /**
     * @var string Native class name( Variable name is fixed ).
     */
    protected static $pr_nativeClassName = '\MySQLi';

    /**
     * @var bool Is this closed?
     */
    protected $pr_isClose = false;

    /**
     * If there is a "MySQLi" query warning, it throw "MySQLi_Query_Warning_Exception".
     *
     * @return void
     */
    private function _checkWarning()
    {
        // The number of warnings which except connection of MySQLi class.
        if ($this->pr_pNativeClass->warning_count) {
            $pResult = $this->pr_pNativeClass->query('SHOW WARNINGS');
            if ($pResult) {
                for ($count = $pResult->num_rows - 1; $count >= 0; $count--) {
                    $return = $pResult->data_seek($count);
                    assert($return !== false);
                    $warning = $pResult->fetch_assoc();
                    if ($warning['Level'] === 'Note') {
                        continue;
                    } else if ($warning['Level'] === 'Warning') {
                        $pResult->close();
                        throw new MySQLi_Query_Warning_Exception(B::convertMbString($warning['Message']), (int) $warning['Code']);
                    } else {
                        assert(false);
                    }
                }
                $pResult->close();
            }
        }
    }

    /**
     * Constructor for override.
     */
    function __construct()
    {
        // This creates a native class object.
        $pNativeClass = self::newArray(self::$pr_nativeClassName, func_get_args());
        // Connection check.
        if ($pNativeClass->connect_errno) {
            throw new MySQLi_Connect_Exception(B::convertMbString($pNativeClass->connect_error), $pNativeClass->connect_errno);
        }
        // This becomes overriding without inheritance of native class ( extension module class ).
        parent::__construct($pNativeClass);
    }

    /**
     * Destructor for close.
     */
    function __destruct()
    {
        // When not closed.
        if (!$this->pr_isClose) {
            $this->close();
        }
    }

    private function _throwQueryError()
    {
        throw new MySQLi_Query_Error_Exception(B::convertMbString($this->pr_pNativeClass->error), $this->pr_pNativeClass->errno);
    }

    private function _throwError()
    {
        throw new MySQLi_Error_Exception(B::convertMbString($this->pr_pNativeClass->error), $this->pr_pNativeClass->errno);
    }

    /**
     * Safe "\Validate\MySQLi::query()"
     *
     * @param string $query          Same as first parameter of "\MySQLi::prepare()".
     * @param string $queryParamType Same as first parameter of "\MySQLi_STMT::bind_param".
     * [param4, [...]] mixed $refParams Same as parameter of order greater than first of "\MySQLi_STMT::bind_param".
     *
     * @return Same.
     *
     * @example safeQuery('SELECT ColumA FROM TableA WHERE (NumericColum >= ?) AND (StringColum LIKE ?)', 'is', $NumericColum, $StringColum);
     */
    function safeQuery($query, $queryParamType)
    {
        $charNumber = strlen($queryParamType);
        for ($charCount = 0, $paramCount = 2; $charCount < $charNumber; $charCount++, $paramCount++) {
            $queryParam = func_get_arg($paramCount);
            switch ($queryParamType[$charCount]) {
                case 'i': // Integer type.
                    $queryParam = mb_convert_kana($queryParam, 'a');
                    // Verifies integer.
                    if (preg_match('`^[+-]?[0-9]+$`xX', $queryParam) === 0) {
                        $this->_throwQueryError();
                    }
                    break;
                case 'd': // Double type.
                    $queryParam = mb_convert_kana($queryParam, 'a');
                    // Verifies float.
                    if (preg_match('`^[+-]?[.0-9]*[0-9]$`xX', $queryParam) === 0) {
                        $this->_throwQueryError();
                    }
                    break;
                case 's': // String type.
                case 'b': // Blob type.
                    $queryParam = $this->real_escape_string($queryParam);
                    $queryParam = "'$queryParam'";
                    break;
                default:
                    assert(false);
            }
            $query = substr_replace($query, $queryParam, strpos($query, '?'), strlen('?'));
        }
        return $this->query($query);
    }

    /**
     * Rapper method of "\MySQLi::query()" for error handling.
     *
     * @param string $query      Same.
     * @param int    $resultMode Same.
     *
     * @return object \Validate\MySQLi_Result
     */
    function query($query, $resultMode = MYSQLI_STORE_RESULT)
    {
        $result = $this->pr_pNativeClass->query($query, $resultMode);
        if ($result === false) { // In case of error.
            $this->_throwQueryError();
        }
        $this->_checkWarning();
        if ($result === true) {
            return true;
        }
        return new MySQLi_Result($result, $this);
    }

    /**
     * Rapper method of "MySQLi::close()" for error handling.
     *
     * @return void
     */
    function close()
    {
        if (!$this->pr_pNativeClass->close()) {
            $this->_throwError();
        }
        // This enables a close flag.
        $this->pr_isClose = true;
    }

    /**
     * Rapper method of "MySQLi::change_user()" for error handling.
     *
     * @param string $user     Same.
     * @param string $password Same.
     * @param string $database Same.
     *
     * @return void
     */
    function change_user($user, $password, $database)
    {
        if (!$this->pr_pNativeClass->change_user($user, $password, $database)) {
            $this->_throwError();
        }
    }

    /**
     * Rapper method of "MySQLi::real_connect()" for error handling.
     *
     * @return void
     */
    function real_connect()
    {
        call_user_func_array(array ($this->pr_pNativeClass, 'real_connect'), func_get_args());
        // Connection check.
        if ($this->pr_pNativeClass->connect_errno) {
            throw new MySQLi_Connect_Exception(B::convertMbString($this->pr_pNativeClass->connect_error), $this->pr_pNativeClass->connect_errno);
        }
    }

    /**
     * Rapper method of "MySQLi::kill()" for error handling.
     *
     * @param int $processid Same.
     *
     * @return void
     */
    function kill($processid)
    {
        if (!$this->pr_pNativeClass->kill($processid)) {
            $this->_throwError();
        }
    }

    /**
     * Rapper method of "MySQLi::ping()" for error handling.
     *
     * @return void
     */
    function ping()
    {
        if (!$this->pr_pNativeClass->ping()) {
            $this->_throwError();
        }
    }

    /**
     * Rapper method of "MySQLi::poll()" for reference parameter.
     * This method does not exist in "XAMPP 1.7.3".
     *
     * @param array &$read   Same.
     * @param array &$error  Same.
     * @param array &$reject Same.
     * @param int   $sec     Same.
     * @param int   $usec    Same.
     *
     * @return Same.
     */
    function poll(&$read, &$error, &$reject, $sec, $usec = 0)
    {
        return $this->pr_pNativeClass->poll($read, $error, $reject, $sec, $usec);
    }

    /**
     * Rapper method of "MySQLi::reap_async_query()" for error handling.
     * This method does not exist in "XAMPP 1.7.3".
     *
     * @return object \Validate\MySQLi_Result
     */
    function reap_async_query()
    {
        $pResult = $this->pr_pNativeClass->reap_async_query();
        if ($pResult === false) {
            $this->_throwQueryError();
        }
        return new MySQLi_Result($pResult, $this);
    }

    /**
     * Rapper method of "MySQLi::prepare()" for error handling.
     *
     * @param string $query Same.
     *
     * @return object \Validate\MySQLi_STMT
     */
    function prepare($query)
    {
        $pStmt = $this->pr_pNativeClass->prepare($query);
        assert($pStmt !== false);
        return new MySQLi_STMT($pStmt, $this);
    }

    /**
     * Rapper method of "MySQLi::select_db()" for error handling.
     *
     * @param string $database Same.
     *
     * @return void
     */
    function select_db($database)
    {
        if (!$this->pr_pNativeClass->select_db($database)) {
            $this->_throwError();
        }
    }

    /**
     * Rapper method of "MySQLi::stmt_init()" for error handling.
     *
     * @return object \Validate\MySQLi_STMT
     */
    function stmt_init()
    {
        return new MySQLi_STMT($this->pr_pNativeClass->stmt_init(), $this);
    }

    /**
     * Rapper method of "MySQLi::store_result()" for error handling.
     *
     * @return object \Validate\MySQLi_Result
     */
    function store_result()
    {
        if (!$this->pr_pNativeClass->field_count) {
            return false;
        }
        $result = $this->pr_pNativeClass->store_result();
        if ($result === false) {
            $this->_throwQueryError();
        }
        return new MySQLi_Result($result, $this);
    }

    /**
     * Rapper method of "MySQLi::use_result()" for error handling.
     *
     * @return object \Validate\MySQLi_Result
     */
    function use_result()
    {
        if (!$this->pr_pNativeClass->field_count) {
            return false;
        }
        $result = $this->pr_pNativeClass->use_result();
        if ($result === false) {
            $this->_throwQueryError();
        }
        return new MySQLi_Result($result, $this);
    }

}

if ($_BreakpointDebugging_EXE_MODE === B::RELEASE) { // In case of release.
    /**
     * This is empty class for release mode.
     * This class detail is 'MySQLi_Option.php' file.
     *
     * @category PHP
     * @package  Validate_MySQLi
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/Validate/MySQLi
     */

    class MySQLi extends MySQLi_InAllCase
    {

    }

} else { // In case of not release.
    include_once __DIR__ . '/MySQLi_Option.php';
}

?>
