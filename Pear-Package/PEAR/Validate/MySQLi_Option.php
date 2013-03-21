<?php

/**
 * This file can make verification of MySQLi without writing a code by including.
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
 * This is wrapper class of MySQLi class for verification, and it is except release mode.
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

    /**
     * Rapper method of "MySQLi::__construct()" for verification.
     */
    function __construct()
    {
        switch (func_num_args()) {
        case 6:
            assert(is_string(func_get_arg(5)));
        case 5:
            assert(is_int(func_get_arg(4)));
            assert(0 <= func_get_arg(4) && func_get_arg(4) <= 65535);
        case 4:
            assert(is_string(func_get_arg(3)));
        case 3:
            assert(is_string(func_get_arg(2)) || is_null(func_get_arg(2)));
        case 2:
            assert(is_string(func_get_arg(1)));
        case 1:
            assert($this->_isHost(func_get_arg(0)) || is_null(func_get_arg(0)));
        case 0:
            break;
        default:
            assert(false);
        }

        forward_static_call_array(array('parent', '__construct'), func_get_args());
    }

    /**
     * Verification of the host name.
     *
     * @param string $hostName Host name.
     *
     * @return bool Is this a host name?
     */
    private function _isHost($hostName)
    {
        // In case of IPv4 or IPv6.
        if (filter_var($hostName, FILTER_VALIDATE_IP) !== false) {
            return true;
        }
        // In case of 'localhost'.
        if (strncasecmp($hostName, 'localhost', strlen('localhost')) === 0) {
            return true;
        }
        // In case of the host name.
        $topEnd = '[a-z0-9]';
        $domain = $topEnd . '([a-z0-9\-]{0,61}' . $topEnd . ')?';
        $pattern = '`^' . $domain . '\.' . $domain . '(\.' . $domain . ')* $`xXi'; // Regular expression of the host name
        if (preg_match($pattern, $hostName) === 1) {
            return true;
        }
        return false;
    }

    /**
     * "\Validate\MySQLi::safeQuery()" for verification.
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
        assert(is_string($query));
        assert(is_string($queryParamType));
        assert(strlen($queryParamType) === func_num_args() - 2);
        assert(strpos($query, '?') !== false);

        $return = call_user_func_array(array('parent', 'safeQuery'), \func_get_args());
        assert($return !== false);
        return $return;
    }

    // This omits because it isn't possible to use with MyISAM storage engine.
    // bool MySQLi::autocommit(bool $mode)

    /**
     * Rapper method of "MySQLi::character_set_name()" for verification.
     *
     * @return Same.
     */
    function character_set_name()
    {
        assert(func_num_args() === 0);

        return $this->pr_pNativeClass->character_set_name();
    }

    // This omits because it isn't possible to use with MyISAM storage engine.
    // bool MySQLi::commit(void)
    // This doesn't use because this debugs using debugger.
    // bool MySQLi::debug(string $message)
    // This doesn't use the dump because this is obscure.
    // bool MySQLi::dump_debug_info(void)

    /**
     * Rapper method of "MySQLi::get_charset()" for verification.
     *
     * @return Same.
     */
    function get_charset()
    {
        assert(func_num_args() === 0);

        return $this->pr_pNativeClass->get_charset();
    }

    /**
     * Rapper method of "MySQLi::get_client_info()" for verification.
     *
     * @return Same.
     */
    function get_client_info()
    {
        assert(func_num_args() === 0);

        return $this->pr_pNativeClass->get_client_info();
    }

    /**
     * Rapper method of "MySQLi::get_connection_stats()" for verification.
     * This method does not exist in "XAMPP 1.7.3".
     *
     * @return Same.
     */
    function get_connection_stats()
    {
        assert(func_num_args() === 0);

        $result = $this->pr_pNativeClass->get_connection_stats();
        assert($result !== null);
        return $result;
    }

    // Probably, this doesn't use because this exists only at the SVN version.
    // mysqli_warning MySQLi::get_warnings( void)

    /**
     * Rapper method of "MySQLi::query()" for verification.
     *
     * @param string $query      Same.
     * @param int    $resultMode Same.
     *
     * @return object \Validate\MySQLi_Result
     */
    function query($query, $resultMode = MYSQLI_STORE_RESULT)
    {
        switch (func_num_args()) {
        case 2:
            assert(is_int($resultMode));
            assert($resultMode === MYSQLI_STORE_RESULT || $resultMode === MYSQLI_ASYNC || $resultMode === MYSQLI_USE_RESULT);
        case 1:
            assert(is_string($query));
            break;
        default:
            assert(false);
        }

        return parent::query($query, $resultMode);
    }

    /**
     * Rapper method of "MySQLi::close()" for verification.
     *
     * @return void
     */
    function close()
    {
        assert(func_num_args() === 0);
        // This should not be closed.
        assert(!$this->pr_isClose);

        parent::close();
    }

    /**
     * Rapper method of "MySQLi::change_user()" for verification.
     *
     * @param string $user     Same.
     * @param string $password Same.
     * @param string $database Same.
     *
     * @return void
     */
    function change_user($user, $password, $database)
    {
        assert(func_num_args() === 3);
        assert(is_string($user));
        assert(is_string($password));
        assert(is_string($database));

        parent::change_user($user, $password, $database);
    }

    /**
     * Rapper method of "MySQLi::options()" for verification.
     *
     * @param int   $option Same.
     * @param mixed $value  Same.
     *
     * @return void
     */
    function options($option, $value)
    {
        assert(func_num_args() === 2);
        assert(is_int($option));
        assert($option === MYSQLI_OPT_CONNECT_TIMEOUT || $option === MYSQLI_OPT_LOCAL_INFILE || $option === MYSQLI_INIT_COMMAND || $option === MYSQLI_READ_DEFAULT_FILE || $option === MYSQLI_READ_DEFAULT_GROUP);
        assert(is_scalar($value));

        $return = $this->pr_pNativeClass->options($option, $value);
        assert($return);
    }

    /**
     * Rapper method of "MySQLi::real_connect()" for verification.
     *
     * @param string $hostNameOrIP          Same.
     * @param string $userName              Same.
     * @param string $passWord              Same.
     * @param string $databaseName          Same.
     * @param int    $portNumber            Same.
     * @param string $socketNameOrNamedPipe Same.
     * @param int    $optionBitFlags        Same.
     *
     * @return void
     */
    function real_connect($hostNameOrIP = null, $userName = null, $passWord = null, $databaseName = null, $portNumber = null, $socketNameOrNamedPipe = null, $optionBitFlags = null)
    {
        switch (func_num_args()) {
        case 7:
            assert(is_int($optionBitFlags));
            assert($optionBitFlags & (MYSQLI_CLIENT_COMPRESS | MYSQLI_CLIENT_FOUND_ROWS | MYSQLI_CLIENT_IGNORE_SPACE | MYSQLI_CLIENT_INTERACTIVE | MYSQLI_CLIENT_SSL));
        case 6:
            assert(is_string($socketNameOrNamedPipe));
        case 5:
            assert(is_int($portNumber));
            assert(0 <= $portNumber && $portNumber <= 65535);
        case 4:
            assert(is_string($databaseName));
        case 3:
            assert(is_string($passWord));
        case 2:
            assert(is_string($userName));
        case 1:
            // When the prefix is "p:".
            if (strncasecmp($hostNameOrIP, 'p:', strlen('p:')) === 0) {
                // This deletes a prefix.
                $hostNameOrIP = substr($hostNameOrIP, strlen('p:'), strlen($hostNameOrIP) - strlen('p:'));
            }
            assert($this->_isHost($hostNameOrIP));
        case 0:
            break;
        default:
            assert(false);
        }

        call_user_func_array(array('parent', 'real_connect'), func_get_args());
    }

    /**
     * Rapper method of "MySQLi::set_charset()" for verification.
     *
     * @param string $charset Same.
     *
     * @return void
     */
    function set_charset($charset)
    {
        assert(func_num_args() === 1);
        assert(is_string($charset));

        $return = $this->pr_pNativeClass->set_charset($charset);
        assert($return);
    }

    /**
     * Rapper method of "MySQLi::kill()" for verification.
     *
     * @param int $processid Same.
     *
     * @return void
     */
    function kill($processid)
    {
        assert(func_num_args() === 1);
        assert(is_int($processid));

        parent::kill($processid);
    }

    // This doesn't use because this doesn't use MySQLi::multi_query().
    // bool MySQLi::more_results(void)
    // This doesn't use because it is for security and it becomes complicated code.
    // bool MySQLi::multi_query(string $query)
    // This doesn't use because this doesn't use MySQLi::multi_query().
    // bool MySQLi::next_result(void)

    /**
     * Rapper method of "MySQLi::ping()" for verification.
     *
     * @return void
     */
    function ping()
    {
        assert(func_num_args() === 0);

        parent::ping();
    }

    /**
     * Rapper method of "MySQLi::poll()" for verification.
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
        switch (func_num_args()) {
        case 5:
            assert(is_int($usec));
        case 4:
            assert(is_int($sec));
            assert(is_array($reject));
            assert(is_array($error));
            assert(is_array($read));
            break;
        default:
            assert(false);
        }

        return parent::poll($read, $error, $reject, $sec, $usec);
    }

    /**
     * Rapper method of "MySQLi::real_escape_string()" for verification.
     *
     * @param string $escapeString Same.
     *
     * @return Same.
     */
    function real_escape_string($escapeString)
    {
        assert(func_num_args() === 1);
        assert(is_string($escapeString));

        return $this->pr_pNativeClass->real_escape_string($escapeString);
    }

    // This doesn't use because it is possible to substitute in MySQLi::query().
    // bool MySQLi::real_query( string $query)

    /**
     * Rapper method of "MySQLi::reap_async_query()" for verification.
     * This method does not exist in "XAMPP 1.7.3".
     *
     * @return object \Validate\MySQLi_Result
     */
    function reap_async_query()
    {
        assert(func_num_args() === 0);

        return parent::reapAsyncQuery();
    }

    /**
     * Rapper method of "MySQLi::prepare()" for verification.
     *
     * @param string $query Same.
     *
     * @return object \Validate\MySQLi_STMT
     */
    function prepare($query)
    {
        assert(func_num_args() === 1);
        assert(is_string($query));

        return parent::prepare($query);
    }

    // This omits because it isn't possible to use with MyISAM storage engine.
    // bool MySQLi::rollback(void)

    /**
     * Rapper method of "MySQLi::select_db()" for verification.
     *
     * @param string $database Same.
     *
     * @return void
     */
    function select_db($database)
    {
        assert(func_num_args() === 1);
        assert(is_string($database));

        parent::select_db($database);
    }

    // This doesn't exist at MySQLi library.
    // void MySQLi::set_local_infile_default(void)
    // This doesn't exist at MySQLi library.
    // bool MySQLi::set_local_infile_handler(MySQLi $link , callback $read_func)
    // This doesn't exist at MySQLi library.
    // bool MySQLi::ssl_set(string $key , string $cert , string $ca , string $capath , string $cipher)

    /**
     * Rapper method of "MySQLi::stat()" for verification.
     *
     * @return Same.
     */
    function stat()
    {
        $return = $this->pr_pNativeClass->stat();
        assert(func_num_args() === 0);
        assert($return !== false);
        return $return;
    }

    /**
     * Rapper method of "MySQLi::stmt_init()" for verification.
     *
     * @return object \Validate\MySQLi_STMT
     */
    function stmt_init()
    {
        assert(false); // This isn't used. Because, it is possible to substitute in MySQLi::prepare().
        assert(func_num_args() === 0);

        return parent::stmt_init();
    }

    /**
     * Rapper method of "MySQLi::store_result()" for verification.
     *
     * @return object \Validate\MySQLi_Result
     */
    function store_result()
    {
        assert(false); // This isn't used. Because, it is possible to substitute in MySQLi::query().
        assert(func_num_args() === 0);

        return parent::store_result();
    }

    /**
     * Rapper method of "MySQLi::thread_safe()" for verification.
     *
     * @return Same.
     */
    function thread_safe()
    {
        assert(func_num_args() === 0);

        return $this->pr_pNativeClass->thread_safe();
    }

    /**
     * Rapper method of "MySQLi::use_result()" for verification.
     *
     * @return object \Validate\MySQLi_Result
     */
    function use_result()
    {
        assert(false); // This isn't used. Because, it is possible to substitute in MySQLi::query().
        assert(func_num_args() === 0);

        return parent::use_result();
    }

}

?>
