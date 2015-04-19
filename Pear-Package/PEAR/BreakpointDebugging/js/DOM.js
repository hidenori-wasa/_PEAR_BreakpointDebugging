/**
 * LICENSE:
 * Copyright (c) 2014, Hidenori Wasa
 * All rights reserved.
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 */

/**
 * Opens virtual window or tab.
 *
 * @param {string} $windowName      Window name.
 * @param {string} $htmlFileContent HTML file content.
 *
 * @returns void
 */
function BreakpointDebugging_windowVirtualOpen($windowName, $htmlFileContent)
{
    open("", $windowName, "").close();
    var $newDocument = open("", $windowName, "").document;
    $newDocument.write($htmlFileContent);
    $newDocument.close();
}

/**
 * Displays window to front.
 *
 * @param {string} $windowName Window name.
 *
 * @returns void
 */
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
