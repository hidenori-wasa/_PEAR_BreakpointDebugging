<?php

/**
 * Black list for except property from static backup.
 *
 * LICENSE:
 * Copyright (c) 2014-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
use \BreakpointDebugging as B;

/**
 * Black list for except property from static backup.
 *
 * PHP version 5.3.2-5.4.x
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_BlackList
{
    static $self;

    /**
     * "\BreakpointDebugging_LockByShmopRequest::_sharedMemoryID" property.
     *
     * @var int
     */
    private $_lockByShmopRequestSharedMemoryID;

    /**
     * "\BreakpointDebugging_LockByShmopRequest::_pPipe" property.
     *
     * @var resource
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
