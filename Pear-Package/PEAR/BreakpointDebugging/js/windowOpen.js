/**
 * Opens a initialized window.
 *
 * @param string $windowName      Window name which opens.
 * @param string $htmlFileContent HTML file content to initialize.
 *
 * @author Hidenori Wasa <public@hidenori-wasa.com>
 * @return void
 */
function BreakpointDebugging_windowOpen($windowName, $htmlFileContent)
{
    openedWindow = open("", $windowName, "");
    openedWindow.close();
    newDocument = open("", $windowName, "").document;
    newDocument.write($htmlFileContent);
    newDocument.close();
}
