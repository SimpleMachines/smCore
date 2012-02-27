<?php

function test_invalid_template_001($harness)
{
	$harness->expectFailure(1, 'tpl_template_must_be_not_empty');
	$harness->addData('<tpl:template name="my:asdf" />');
}

function test_invalid_template_002($harness)
{
	$harness->expectFailure(1, 'tpl_content_twice');
	$harness->addData('<tpl:template name="my:asdf"><tpl:content /><tpl:content /></tpl:template>');
}

function test_invalid_template_003($harness)
{
	$harness->expectFailure(1, 'tpl_template_duplicate_name');
	$harness->addData('<tpl:template name="my:asdf"></tpl:template><tpl:template name="my:asdf"></tpl:template>');
}

function test_invalid_template_004($harness)
{
	$harness->expectFailure(1, 'parsing_content_outside_template');
	$harness->addData('<tpl:template name="my:asdf"></tpl:template> content <tpl:template name="my:asdf2"></tpl:template>');
}

function test_invalid_template_005($harness)
{
	$harness->expectFailure(1, 'tpl_template_name_unknown_ns');
	$harness->addData('<tpl:template name="undef:asdf"></tpl:template>');
}

function test_invalid_template_006($harness)
{
	$harness->expectFailure(1, 'tpl_template_missing_name');
	$harness->addData('<tpl:template name=""></tpl:template>');
}

function test_invalid_template_007($harness)
{
	$harness->expectFailure(1, 'tpl_template_missing_name');
	$harness->addData('<tpl:template></tpl:template>');
}

function test_invalid_template_008($harness)
{
	$harness->expectFailure(1, 'parsing_content_outside_template');
	$harness->addData('<undef:asdf />');
}

function test_invalid_template_009($harness)
{
	$harness->expectFailure(1, 'builder_element_outside_template');
	$harness->addData('<my:asdf />');
}

function test_invalid_template_010($harness)
{
	$harness->expectFailure(1, 'generic_tpl_no_ns_or_name');
	$harness->addData('<tpl:template name="my:"></tpl:template>');
}

function test_template_001($harness)
{
	$harness->addData('<tpl:template name="my:asdf"></tpl:template>');
}

function test_template_002($harness)
{
	$harness->addData('<tpl:template name="my:asdf"><tpl:content /></tpl:template>');
}

function test_template_003($harness)
{
	$harness->addData('<tpl:template name="my:asdf"><![CDATA[ <tpl:content /> ]]></tpl:template>');
}

function test_template_004($harness)
{
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2 /></tpl:template>');
}

function test_template_005($harness)
{
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2></my:asdf2></tpl:template>');
}

function test_template_006($harness)
{
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2> test </my:asdf2></tpl:template>');
}

function test_template_007($harness)
{
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2 var="blah"> test </my:asdf2></tpl:template>');
}

function test_template_008($harness)
{
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2 var="{$asdf}"> test </my:asdf2></tpl:template>');
}

function test_template_009($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2 var="{$}"> test </my:asdf2></tpl:template>');
}

function test_template_010($harness)
{
	// Defaulting (across templates) is allowed.
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2> test </my:asdf2></tpl:template>');
	$harness->addData('<tpl:template name="my:asdf"><my:asdf2> test </my:asdf2></tpl:template>');
}

function test_template_011($harness)
{
	$harness->addData('{tpl:template name="my:asdf"}{my:asdf2 var="{$asdf}"} test {/my:asdf2}{/tpl:template}');
}

function test_template_012($harness)
{
	$harness->addData('{tpl:template name="my:asdf" requires="asdf qwer"}{my:asdf2 var="{$asdf}"} test {/my:asdf2}{/tpl:template}');
}

function test_template_013($harness)
{
	$harness->expectFailure(1, 'tpl_template_inside_template');
	$harness->addWrappedData('<tpl:template name="my:asdf"></tpl:template>');
}