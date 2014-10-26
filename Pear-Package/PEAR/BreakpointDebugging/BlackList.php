<?php

/**
 * Black list for except property from static backup.
 *
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2014, Hidenori Wasa
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
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
use \BreakpointDebugging as B;

/**
 * Black list for except property from static backup.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_BlackList
{
    static $self;

    /**
     * @var int "\BreakpointDebugging_LockByShmopRequest::_sharedMemoryID" property.
     */
    private $_lockByShmopRequestSharedMemoryID;

    /**
     * @var resource "\BreakpointDebugging_LockByShmopRequest::_pPipe" property.
     */
    private $_lockByShmopRequestPPipe;

    /**
     * Returns reference of "\BreakpointDebugging_LockByShmopRequest::_sharedMemoryID" property.
     *
     * @return bool& Reference value.
     */
    static function &refLockByShmopRequestSharedMemoryID()
    {
        B::limitAccess('BreakpointDebugging/LockByShmopRequest.php');

        return self::$self->_lockByShmopRequestSharedMemoryID;
    }

    /**
     * Returns reference of "\BreakpointDebugging_LockByShmopRequest::_pPipe" property.
     *
     * @return bool& Reference value.
     */
    static function &refLockByShmopRequestPPipe()
    {
        B::limitAccess('BreakpointDebugging/LockByShmopRequest.php');

        return self::$self->_lockByShmopRequestPPipe;
    }

}

\BreakpointDebugging_BlackList::$self = new \BreakpointDebugging_BlackList();
