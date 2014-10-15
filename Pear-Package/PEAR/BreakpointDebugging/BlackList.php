<?php

use \BreakpointDebugging as B;

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
