function BreakpointDebugging_windowVertualOpen($windowName, $htmlFileContent)
{
    open("", $windowName, "").close();
    var $newDocument = open("", $windowName, "").document;
    $newDocument.write($htmlFileContent);
    $newDocument.close();
}

function BreakpointDebugging_windowFront($windowName)
{
    // Gets content of HTML tag.
    var $html = open("", $windowName, "").document.getElementsByTagName('html')[0].innerHTML;
    // Closes the window.
    open("", $windowName, "").close();
    // Opens the window. Then, gets its document object.
    var $newDocument = open("", $windowName, "").document;
    // Starts document to write.
    $newDocument.open();
    // Writes HTML template.
    $newDocument.write('<!DOCTYPE html><html></html>');
    // Writes content of HTML tag.
    $newDocument.getElementsByTagName('html')[0].innerHTML = $html;
    // Ends document writing.
    $newDocument.close();
}
