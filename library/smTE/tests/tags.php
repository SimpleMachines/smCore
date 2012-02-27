<?php

function test_tags_output_001($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x}" />');
}

function test_tags_output_002($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x}" escape="false" />');
}

function test_tags_output_003($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:output value="{$x}" />');
}

function test_tags_output_004($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:output value="{$x}" escape="false" />');
}

function test_tags_output_005($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="bad syn tax" escape="false" />');
}

function test_tags_output_006($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x} + 1" escape="false" />');
}

function test_tags_output_007($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x}" />');
}

function test_tags_output_008($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:output value="{$x}" />');
}

function test_tags_output_009($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="bad syn tax" />');
}

function test_tags_output_010($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x} + 1" />');
}

function test_tags_output_011($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x %json}" />');
}

function test_tags_output_012($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:output value="{$x %json}" />');
}

function test_tags_output_013($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x %add:1 %json}" />');
}

function test_tags_for_001($harness)
{
	$harness->addWrappedData('<tpl:for init="true" />');
}

function test_tags_for_002($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:for while="mary has a little lamb" />');
}

function test_tags_for_003($harness)
{
	$harness->expectFailure(1, 'tpl_for_no_params');
	$harness->addWrappedData('<tpl:for></tpl:for>');
}

function test_tags_foreach_001($harness)
{
	$harness->expectFailure(1, 'generic_tpl_must_be_not_empty');
	$harness->addWrappedData('<tpl:foreach from="{$x}" as="{$y}" />');
}

function test_tags_foreach_002($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:foreach from="{$x}" as="failure"></tpl:foreach>');
}

function test_tags_foreach_003($harness)
{
	$harness->expectFailure(1, 'tpl_foreach_invalid_from');
	$harness->addWrappedData('<tpl:foreach from="failure" as="{$x}"></tpl:foreach>');
}

function test_tags_foreach_004($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:foreach from="failure" as="failure"></tpl:foreach>');
}

function test_tags_foreach_005($harness)
{
	$harness->addWrappedData('<tpl:foreach from="{$x}" as="{$y}"></tpl:foreach>');
}

function test_tags_foreach_006($harness)
{
	$harness->addWrappedData('<tpl:foreach from="{$x}" as="{$y}"><tpl:foreach from="{$y}" as="{$z}">{$z}</tpl:foreach></tpl:foreach>');
}

function test_tags_foreach_007($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:foreach from="{$x}" as="{$y}"><tpl:foreach from="{$y}" as="{$z}">{$z}</tpl:foreach></tpl:foreach>');
}

function test_tags_foreach_008($harness)
{
	$harness->addWrappedData('<tpl:foreach from="{$x}" as="{$y} => {$z}">{$y}: {$z}</tpl:foreach>');
}

function test_tags_if_001($harness)
{
	$harness->addWrappedData('<tpl:if test="{$x} == 1">test</tpl:if>');
}

function test_tags_if_002($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:if test="">test</tpl:if>');
}

function test_tags_if_003($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:if test="I am a goat.">test</tpl:if>');
}

function test_tags_if_004($harness)
{
	$harness->expectFailure(1, 'generic_tpl_must_be_empty');
	$harness->addWrappedData('<tpl:if test="1">test<tpl:else>2</tpl:else></tpl:if>');
}

function test_tags_if_005($harness)
{
	$harness->addWrappedData('<tpl:if test="1">test<tpl:else />2</tpl:if>');
}

function test_tags_if_006($harness)
{
	$harness->addWrappedData('<tpl:if test="1">test<tpl:else test="0" />2</tpl:if>');
}

function test_tags_if_007($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:if test="1">test<tpl:else test="0" />2</tpl:if>');
}

function test_tags_if_008($harness)
{
	$harness->expectFailure(1, 'generic_tpl_must_be_empty');
	$harness->addWrappedData('<tpl:if test="1">test<tpl:else>2</tpl:if>');
}

function test_tags_flush_001($harness)
{
	$harness->addWrappedData('<tpl:flush />');
}

function test_tags_flush_002($harness)
{
	$harness->expectFailure(1, 'generic_tpl_must_be_empty');
	$harness->addWrappedData('<tpl:flush></tpl:flush>');
}

function test_tags_flush_003($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:flush />');
}

function test_tags_set_001($harness)
{
	$harness->expectFailure(1, 'generic_tpl_missing_required');
	$harness->addWrappedData('<tpl:set></tpl:set>');
}

function test_tags_set_002($harness)
{
	$harness->expectFailure(1, 'tpl_set_invalid_meta');
	$harness->addWrappedData('<tpl:set var="{$x}" value="{$y}"></tpl:set>');
}

function test_tags_set_003($harness)
{
	$harness->expectFailure(1, 'tpl_set_invalid_meta');
	$harness->addWrappedData('<tpl:set var="{$x}" value="1"></tpl:set>');
}

function test_tags_set_004($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:set var="2" value="1" />');
}

function test_tags_set_005($harness)
{
	$harness->expectFailure(1, 'generic_tpl_missing_required');
	$harness->addWrappedData('<tpl:set var="{$x}" />');
}

function test_tags_set_006($harness)
{
	$harness->expectFailure(1, 'generic_tpl_missing_required');
	$harness->addWrappedData('<tpl:set value="{$x}" />');
}

function test_tags_set_007($harness)
{
	$harness->addWrappedData('<tpl:set var="{$x}" value="{$y}" />');
}

function test_tags_set_008($harness)
{
	$harness->addWrappedData('<tpl:set var="{$x}" value="1" />');
}

function test_tags_set_009($harness)
{
	$harness->addDataForOverlay();
	$harness->addWrappedOverlay('<tpl:set var="{$x}" value="1" />');
}

function test_tags_container_001($harness)
{
	$harness->addWrappedData('<tpl:container />');
}

function test_tags_container_002($harness)
{
	$harness->addWrappedData('<tpl:container></tpl:container>');
}

function test_tags_container_003($harness)
{
	$harness->addWrappedData('<tpl:container xmlns:x="y"></tpl:container>');
}

function test_tags_element_001($harness)
{
	$harness->addWrappedData('<tpl:element tpl:name="div" />');
}

function test_tags_element_002($harness)
{
	$harness->addWrappedData('<tpl:element tpl:name="div" tpl:inherit="*" />');
}

function test_tags_element_003($harness)
{
	$harness->addWrappedData('<tpl:element tpl:name="div" value="1" />');
}

function test_tags_element_004($harness)
{
	$harness->addWrappedData('<tpl:element tpl:name="div" tpl:inherit="*" value="1" />');
}

function test_tags_element_005($harness)
{
	$harness->expectFailure(1, 'generic_tpl_empty_attr');
	$harness->addWrappedData('<tpl:element />');
}

function test_tags_element_006($harness)
{
	$harness->expectFailure(1, 'generic_tpl_empty_attr');
	$harness->addWrappedData('<tpl:element tpl:name="" />');
}

function test_tags_element_007($harness)
{
	$harness->addWrappedData('<tpl:element tpl:name="div" tpl:inherit="*" value="1">test</tpl:element>');
}