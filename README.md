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

Please, read the file level document block of `PEAR/BreakpointDebugging_Option.php`.

The dependences
---------------

* OS requires `Linux` or `Windows`, but may be able to require `Unix`.
* Development PHP version = `XAMPP 1.7.3` ("PHP 5.3.x" Compiled with VC++6.0 in case of windows.)

                            Do not use `XAMPP 1.7.7` ("PHP 5.3.x" Compiled with VC++9.0 in case of windows.)

* Production PHP version = `5.3.x`
* Requires `Xdebug extension` in case of local host. "Xdebug" extension is required because (uses breakpoint, displays for fatal error and detects infinity recursive function call).
* Requires `Xdebug extension` if you want features of above in case of "$_BreakpointDebugging_EXE_MODE = B::REMOTE" mode. (optional).
* Requires `shmop extension` if you use "\BreakpointDebugging_LockByShmop" class. (optional).
* Requires environment which `flock()` enables if you use "\BreakpointDebugging_LockByFlock" class. (optional).

How to sniff my code.
---------------------

I customized code sniff because "which" statement indent is "PEAR" special.
Therefore, default doesn't fit "IDE".
Please, change following file.

`C:\xampp\php\PEAR\PHP\CodeSniffer\Standards\PEAR\Sniffs\WhiteSpace\ScopeIndentSniff.php`

before:

    protected $nonIndentingScopes = array(T_SWITCH);

after:

    protected $nonIndentingScopes = array();

Please, execute command like following.

    phpcs -n <full file path>

Or, execute command like following with short cut file.

    %SystemRoot%\system32\cmd.exe /k phpcs -v -n "<full file path>"

Notice
------

"Pear-Package/nbproject/" directory is project directory of "NetBeans IDE 7.1.2 for Windows".
You must change "test.src.dir" of "Pear-Package/nbproject/project.properties" file if you use on Linux.
Example:

    test.src.dir=/home/hidenori/htdocs/Pear-Package/tests

Also, "NetBeans IDE 7.4" cannot keep switchback in format of "if" statement at December, 2013.
Example:

    if ($a
        && $b           // Cannot keep switchback of this line.
        && ($c || $d)   // Cannot keep switchback of this line.
        || ($e && $f)   // Cannot keep switchback of this line.
    ) {
        return;
    }

However, "NetBeans IDE 7.4" supports "PHP5.4" and "HTML5".

I have been coding the unit tests.
