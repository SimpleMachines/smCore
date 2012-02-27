<?php

function test_invalid_alter_001($harness)
{
	$harness->expectFailure(1, 'tpl_alter_missing_match_position');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example">test</tpl:alter>');
}

function test_invalid_alter_002($harness)
{
	$harness->expectFailure(1, 'tpl_alter_missing_match_position');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="" position="before">test</tpl:alter>');
}

function test_invalid_alter_003($harness)
{
	$harness->expectFailure(1, 'tpl_alter_missing_match_position');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="">test</tpl:alter>');
}

function test_invalid_alter_004($harness)
{
	$harness->expectFailure(1, 'tpl_alter_missing_match_position');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter>test</tpl:alter>');
}

function test_invalid_alter_005($harness)
{
	$harness->expectFailure(1, 'tpl_alter_invalid_position');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="blah">test</tpl:alter>');
}

function test_invalid_alter_006($harness)
{
	$harness->expectFailure(1, 'overlay_content_outside_alter');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="before">test</tpl:alter> test');
}

function test_invalid_alter_007($harness)
{
	$harness->expectFailure(1, 'tpl_alter_missing_match_position');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="before"><tpl:alter match="my:output my:example" position="before"></tpl:alter></tpl:alter> test');
}

function test_invalid_alter_008($harness)
{
	$harness->expectFailure(1, 'overlay_alter_must_be_not_empty');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="before"/>');
}

function test_invalid_alter_009($harness)
{
	$harness->expectFailure(1, 'generic_tpl_no_ns_or_name');
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:" position="before">test</tpl:alter>');
}

function test_alter_001($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output" position="before">test</tpl:alter>');
}

function test_alter_002($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="before">test</tpl:alter>');
}

function test_alter_003($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="beforecontent">test</tpl:alter>');
}

function test_alter_004($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="after">test</tpl:alter>');
}

function test_alter_005($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="aftercontent">test</tpl:alter>');
}

function test_alter_006($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="aftercontent">test1</tpl:alter>');
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="aftercontent">test2</tpl:alter>');
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="aftercontent">test3</tpl:alter>');
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="aftercontent">test4</tpl:alter>');
}

function test_alter_007($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="beforecontent">test1</tpl:alter>');
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="beforecontent">test2</tpl:alter>');
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="beforecontent">test3</tpl:alter>');
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="beforecontent">test4</tpl:alter>');
}

function test_alter_008($harness)
{
	$harness->addDataForOverlay();
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="before"><tpl:if test="true"></tpl:alter>');
	$harness->addOverlay('<tpl:alter match="my:output my:example" position="beforecontent"></tpl:if></tpl:alter>');
}

function test_alter_009($harness)
{
	$harness->addData('
		<tpl:template name="my:output">Stuff.</tpl:template>
		<tpl:alter match="my:output" position="before">Hello.</tpl:alter>
		<tpl:alter match="my:output" position="after">Goodbye.</tpl:alter>');
}

function test_alter_010($harness)
{
	$harness->addData('<tpl:template name="my:stuff"></tpl:template>');
	$harness->addOverlay('<tpl:alter match="my:stuff" position="after" xmlns:blah="http://www.example.com/#blah0"><blah:checkme /></tpl:alter>');
}