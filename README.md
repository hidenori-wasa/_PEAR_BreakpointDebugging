BreakpointDebugging
===================

The basic concept
-----------------

This is package for breakpoint debugging.
Also, you can use as basics of other PHP program or PEAR package if you want
because this package has necessary minimum features.

The features list
-----------------

* "php.ini"-file setting fixation feature.
* PHP code synchronization feature.
* Error log feature by global handler.
* The override class feature.
* Execution mode.

As for procedure, please, refer to `PEAR/BreakpointDebugging/docs/BREAKPOINTDEBUGGING_MANUAL.html`.

The dependences
---------------

* OS requires "Linux" or "Windows".
* PHP version = 5.3.2-5.4.x
* Requires "Mozilla Firefox" web browser, and "Windows" must be in "C:/Program Files/Mozilla Firefox/firefox.exe".
* Requires "shmop extension" if you use on local server.
* Requires "Xdebug extension" in case of local host. "Xdebug" extension is required because (uses breakpoint, displays for fatal error and detects infinity recursive function call).
* Requires "Xdebug extension" if you want features of above on remote server. (optional).
* Requires environment which `flock()` enables if you use `\BreakpointDebugging_LockByFlock` class. (optional).

How to sniff my code.
---------------------

Please, execute command like following.

    phpcs -n <full file path>

Or, execute command like following with short cut file.

    %SystemRoot%\system32\cmd.exe /k phpcs -v -n "<full file path>"

Notice
------

* Same as prev upload part of "CakePHPSamples".
