<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;

class BreakpointDebugging_ProductionSwitcherTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCase
{

    private function _stripCommentForRestoration($results, $linesForTest)
    {
        foreach ($results as $result) {
            $resultsB[] = BU::callForTest(array (
                    'objectOrClassName' => 'BreakpointDebugging_ProductionSwitcher',
                    'methodName' => '_stripCommentForRestoration',
                    'params' => array ($result)
            ));
        }

        parent::assertTrue($linesForTest === $resultsB);
    }

    /**
     * @covers \BreakpointDebugging_ProductionSwitcher<extended>
     */
    function test_commentOutAssertion()
    {
        include 'BreakpointDebugging_ProductionSwitcher.php';

        $linesForTest = array (
            '\BreakpointDebugging::assert(true);',
            "\t\\BreakpointDebugging::assert(true);\n",
            "\x20\\BreakpointDebugging::assert(true);\r\n",
            "\x20\t\\BreakpointDebugging::assert(true);",
            "\t\x20\\BreakpointDebugging::assert(true);",
            "\t\x20\\\t\x20BreakpointDebugging\t\x20::\t\x20assert\t\x20(\t\x20true\t\x20)\t\x20;\t\x20",
            '\BreakpointDebugging::assert(true); echo("abc");',
            '\BreakpointDebugging::assert(true); //',
            '\BreakpointDebugging::assert(true); // Something comment.',
            '\BreakpointDebugging::assert(true); /',
            '\BreakpointDebugging::assert(true); / Something comment.',
            '\BreakpointDebugging::assert(true); /*',
            '\BreakpointDebugging::assert(true); /* Something comment.',
        );
        $expectedLines = array (
            '// <BREAKPOINTDEBUGGING_COMMENT> \BreakpointDebugging::assert(true);',
            "\t// <BREAKPOINTDEBUGGING_COMMENT> \\BreakpointDebugging::assert(true);\n",
            "\x20// <BREAKPOINTDEBUGGING_COMMENT> \\BreakpointDebugging::assert(true);\r\n",
            "\x20\t// <BREAKPOINTDEBUGGING_COMMENT> \\BreakpointDebugging::assert(true);",
            "\t\x20// <BREAKPOINTDEBUGGING_COMMENT> \\BreakpointDebugging::assert(true);",
            "\t\x20// <BREAKPOINTDEBUGGING_COMMENT> \\\t\x20BreakpointDebugging\t\x20::\t\x20assert\t\x20(\t\x20true\t\x20)\t\x20;\t\x20",
            '// <BREAKPOINTDEBUGGING_COMMENT> \BreakpointDebugging::assert(true); echo("abc");',
            '// <BREAKPOINTDEBUGGING_COMMENT> \BreakpointDebugging::assert(true); //',
            '// <BREAKPOINTDEBUGGING_COMMENT> \BreakpointDebugging::assert(true); // Something comment.',
            '\BreakpointDebugging::assert(true); /',
            '\BreakpointDebugging::assert(true); / Something comment.',
            '\BreakpointDebugging::assert(true); /*',
            '\BreakpointDebugging::assert(true); /* Something comment.',
        );

        foreach ($linesForTest as $lineForTest) {
            $results[] = BU::callForTest(array (
                    'objectOrClassName' => 'BreakpointDebugging_ProductionSwitcher',
                    'methodName' => '_commentOutAssertion',
                    'params' => array ($lineForTest)
            ));
        }

        parent::assertTrue($expectedLines === $results);

        $this->_stripCommentForRestoration($results, $linesForTest);
    }

    /**
     * @covers \BreakpointDebugging_ProductionSwitcher<extended>
     */
    function test_changeModeConstToLiteralA()
    {
        $linesForTest = array (
            "            if (\BreakpointDebugging::isDebug()) { // If debug.\r\n",
            'if(\BreakpointDebugging::isDebug()){',
            "\tif(\\BreakpointDebugging::isDebug()){\n",
            "\x20if(\\BreakpointDebugging::isDebug()){\r\n",
            "\x20\tif(\\BreakpointDebugging::isDebug()){",
            "\t\x20if(\\BreakpointDebugging::isDebug()){",
            "\t\x20if\t\x20(\t\x20!\t\x20\\\t\x20BreakpointDebugging\t\x20::\t\x20isDebug\t\x20(\t\x20)\t\x20)\t\x20{\t\x20",
            'if(\BreakpointDebugging::isDebug()){ echo("abc");',
            'if(\BreakpointDebugging::isDebug()){ //',
            'if(\BreakpointDebugging::isDebug()){ // Something comment.',
            'if(\BreakpointDebugging::isDebug()){ /',
            'if(\BreakpointDebugging::isDebug()){ / Something comment.',
            'if(\BreakpointDebugging::isDebug()){ /*',
            'if(\BreakpointDebugging::isDebug()){ /* Something comment.',
        );
        $expectedLines = array (
            "            /* <BREAKPOINTDEBUGGING_COMMENT> */ if ( false ) { // If debug. // <BREAKPOINTDEBUGGING_COMMENT> if (\BreakpointDebugging::isDebug()) { // If debug.\r\n",
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // <BREAKPOINTDEBUGGING_COMMENT> if(\BreakpointDebugging::isDebug()){',
            "\t/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // <BREAKPOINTDEBUGGING_COMMENT> if(\\BreakpointDebugging::isDebug()){\n",
            "\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // <BREAKPOINTDEBUGGING_COMMENT> if(\\BreakpointDebugging::isDebug()){\r\n",
            "\x20\t/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // <BREAKPOINTDEBUGGING_COMMENT> if(\\BreakpointDebugging::isDebug()){",
            "\t\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // <BREAKPOINTDEBUGGING_COMMENT> if(\\BreakpointDebugging::isDebug()){",
            "\t\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if\t\x20(\t\x20!\t\x20 false \t\x20)\t\x20{\t\x20 // <BREAKPOINTDEBUGGING_COMMENT> if\t\x20(\t\x20!\t\x20\\\t\x20BreakpointDebugging\t\x20::\t\x20isDebug\t\x20(\t\x20)\t\x20)\t\x20{\t\x20",
            'if(\BreakpointDebugging::isDebug()){ echo("abc");',
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // // <BREAKPOINTDEBUGGING_COMMENT> if(\BreakpointDebugging::isDebug()){ //',
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // Something comment. // <BREAKPOINTDEBUGGING_COMMENT> if(\BreakpointDebugging::isDebug()){ // Something comment.',
            'if(\BreakpointDebugging::isDebug()){ /',
            'if(\BreakpointDebugging::isDebug()){ / Something comment.',
            'if(\BreakpointDebugging::isDebug()){ /*',
            'if(\BreakpointDebugging::isDebug()){ /* Something comment.',
        );

        foreach ($linesForTest as $lineForTest) {
            $results[] = BU::callForTest(array (
                    'objectOrClassName' => 'BreakpointDebugging_ProductionSwitcher',
                    'methodName' => '_changeModeConstToLiteral',
                    'params' => array ($lineForTest, BU::getPropertyForTest('BreakpointDebugging_ProductionSwitcher', '$_isDebugRegEx'), 'false')
            ));
        }

        for ($count = 0; $count < count($expectedLines); $count++) {
            parent::assertTrue($expectedLines[$count] === $results[$count]);
        }

        $this->_stripCommentForRestoration($results, $linesForTest);
    }

    /**
     * @covers \BreakpointDebugging_ProductionSwitcher<extended>
     */
    function test_changeModeConstToLiteralB()
    {
        $linesForTest = array (
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){',
            "\tif(BREAKPOINTDEBUGGING_IS_PRODUCTION){\n",
            "\x20if(BREAKPOINTDEBUGGING_IS_PRODUCTION){\r\n",
            "\x20\tif(BREAKPOINTDEBUGGING_IS_PRODUCTION){",
            "\t\x20if(BREAKPOINTDEBUGGING_IS_PRODUCTION){",
            "\t\x20if\t\x20(\t\x20!\t\x20BREAKPOINTDEBUGGING_IS_PRODUCTION\t\x20)\t\x20{\t\x20",
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ echo("abc");',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ //',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ // Something comment.',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ /',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ / Something comment.',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ /*',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ /* Something comment.',
        );
        $expectedLines = array (
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( true ){ // <BREAKPOINTDEBUGGING_COMMENT> if(BREAKPOINTDEBUGGING_IS_PRODUCTION){',
            "\t/* <BREAKPOINTDEBUGGING_COMMENT> */ if( true ){ // <BREAKPOINTDEBUGGING_COMMENT> if(BREAKPOINTDEBUGGING_IS_PRODUCTION){\n",
            "\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if( true ){ // <BREAKPOINTDEBUGGING_COMMENT> if(BREAKPOINTDEBUGGING_IS_PRODUCTION){\r\n",
            "\x20\t/* <BREAKPOINTDEBUGGING_COMMENT> */ if( true ){ // <BREAKPOINTDEBUGGING_COMMENT> if(BREAKPOINTDEBUGGING_IS_PRODUCTION){",
            "\t\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if( true ){ // <BREAKPOINTDEBUGGING_COMMENT> if(BREAKPOINTDEBUGGING_IS_PRODUCTION){",
            "\t\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if\t\x20(\t\x20!\t\x20 true \t\x20)\t\x20{\t\x20 // <BREAKPOINTDEBUGGING_COMMENT> if\t\x20(\t\x20!\t\x20BREAKPOINTDEBUGGING_IS_PRODUCTION\t\x20)\t\x20{\t\x20",
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ echo("abc");',
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( true ){ // // <BREAKPOINTDEBUGGING_COMMENT> if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ //',
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( true ){ // Something comment. // <BREAKPOINTDEBUGGING_COMMENT> if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ // Something comment.',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ /',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ / Something comment.',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ /*',
            'if(BREAKPOINTDEBUGGING_IS_PRODUCTION){ /* Something comment.',
        );

        foreach ($linesForTest as $lineForTest) {
            $results[] = BU::callForTest(array (
                    'objectOrClassName' => 'BreakpointDebugging_ProductionSwitcher',
                    'methodName' => '_changeModeConstToLiteral',
                    'params' => array ($lineForTest, BU::getPropertyForTest('BreakpointDebugging_ProductionSwitcher', '$_breakpointdebuggingIsProductionRegEx'), 'true')
            ));
        }

        for ($count = 0; $count < count($expectedLines); $count++) {
            parent::assertTrue($expectedLines[$count] === $results[$count]);
        }

        $this->_stripCommentForRestoration($results, $linesForTest);
    }

    /**
     * @covers \BreakpointDebugging_ProductionSwitcher<extended>
     */
    function test_changeModeConstToLiteralC()
    {
        $linesForTest = array (
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){',
            "\t/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){\n",
            "\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){\r\n",
            "\x20\t/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){",
            "\t\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){",
            "\t\x20/* <BREAKPOINTDEBUGGING_COMMENT> */ if\t\x20(\t\x20!\t\x20 false \t\x20)\t\x20{\t\x20",
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ //',
            '/* <BREAKPOINTDEBUGGING_COMMENT> */ if( false ){ // Something comment.',
        );
        $results = array (
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
        );

        $this->_stripCommentForRestoration($linesForTest, $results);
    }

}
