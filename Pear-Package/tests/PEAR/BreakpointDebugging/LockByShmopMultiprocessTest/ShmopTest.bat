@echo off
"C:\Program Files\Internet Explorer\iexplore.exe" localhost/Pear-Package/tests/PEAR/BreakpointDebugging/LockByShmopMultiprocessTest/Initialization.php
%SystemRoot%\system32\cmd.exe < ShmopCommands.txt
cls
echo Expectation of max sum count is 10000.
PAUSE
