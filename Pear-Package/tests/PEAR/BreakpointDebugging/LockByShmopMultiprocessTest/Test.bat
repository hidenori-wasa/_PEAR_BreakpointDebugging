@echo off
"C:\Program Files\Internet Explorer\iexplore.exe" localhost/Pear-Package/tests/PEAR/BreakpointDebugging/LockByShmopMultiprocessTest/Initialization.php
%SystemRoot%\system32\cmd.exe < Commands.txt
cls
echo In case of 8 processes, expectation of max sum count is 1000.
PAUSE
