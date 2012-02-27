<?php

// !!! Would be nice to expectFailure the inner exception code...

function test_invalid_expression_001($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{$x" />');
}

function test_invalid_expression_002($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{$}" />');
}

function test_invalid_expression_003($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{$x[}" />');
}

function test_invalid_expression_004($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{$x[abc}" />');
}

function test_invalid_expression_005($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{$x.}" />');
}

function test_invalid_expression_006($harness)
{
	// Just to make sure this isn't considered invalid too, which means the past ones are broken.
	$harness->addWrappedData('<tpl:output value="{$x.a}" />');
}

function test_invalid_expression_007($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{" />');
}

function test_invalid_expression_008($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{#}" />');
}

function test_invalid_expression_009($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang}" />');
}

function test_invalid_expression_010($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{#lang:$}" />');
}

function test_invalid_expression_011($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{#:}" />');
}

function test_invalid_expression_012($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{$x.y[$a.b.c.d].z]}" />');
}

function test_expression_001($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x}" />');
}

function test_expression_002($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.1}" />');
}

function test_expression_003($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.y}" />');
}

function test_expression_004($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.z.1}" />');
}

function test_expression_005($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.1.z}" />');
}

function test_expression_006($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x[1]}" />');
}

function test_expression_007($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x[1].z}" />');
}

function test_expression_008($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.1[3]}" />');
}

function test_expression_009($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.1[3].z}" />');
}

function test_expression_010($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang}" />');
}

function test_expression_011($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang:$x.1[3].z}" />');
}

function test_expression_012($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang:$x.1[3].z:$x.1[3].z:$x.1[3].z:$x.1[3].z}" />');
}

function test_expression_013($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.1[3].$z}" />');
}

function test_expression_014($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang:$x.1[3].$z}" />');
}

function test_expression_015($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{test}" />');
}

function test_expression_016($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="{tpl:output /}" />');
}

function test_expression_017($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:output value="xyz();" />');
}

function test_expression_018($harness)
{
	$harness->addWrappedData('<tpl:output value="xyz()" />');
}

function test_expression_019($harness)
{
	$harness->addWrappedData('<my:test x="asdf{$x}ysdf" />');
}

function test_expression_020($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<my:test x="asdf{$x.}ysdf" />');
}

function test_expression_021($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:foreach from="{#lang}" as="{#as}"></tpl:foreach>');
}

function test_expression_022($harness)
{
	$harness->addWrappedData('<my:test x="asdf{ToxgTestHarness::TEST}ysdf" />');
}

function test_expression_023($harness)
{
	$harness->expectFailure(1, 'expression_invalid_meta');
	$harness->addWrappedData('<tpl:set var="{$x}" value="{ToxgTestHarness} . ToxgTestHarness::TEST " />');
}

function test_expression_024($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang:{#lang2:1:3:4:5}}" />');
}

function test_expression_025($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang:{#lang2:1:3:4:5}:6:7}" />');
}

function test_expression_026($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang1:{#lang2:param1:{$obj->prop}:param3}:{$var1}:{$var2}:param}" />');
}

function test_expression_027($harness)
{
	$harness->addWrappedData('<tpl:output value="{MyClass::func()}" />');
}

function test_expression_028($harness)
{
	$harness->addWrappedData('<tpl:output value="{MyClass::$prop}" />');
}

function test_expression_029($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang1:{#lang2:param1:{MyClass::$prop}:param3}:{$var1}:{$var2}:param}" />');
}

function test_expression_030($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang1:{#lang2:param1:$var0:param3}:{$var1}:{$var2}:param}" />');
}

function test_expression_031($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.y[$a.b.c.d].z}" />');
}

function test_expression_032($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.y.#z}" />');
}

function test_expression_033($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x.y[#z]}" />');
}

function test_expression_034($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x |date}" />');
}

function test_expression_035($harness)
{
	$harness->addWrappedData('<tpl:output value="{$x |date(\'g:i A\')}" />');
}

function test_expression_036($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang:\'String: now with multiple :s!\'}" />');
}

function test_expression_037($harness)
{
	$harness->addWrappedData('<tpl:output value="{#lang:{$x |date}}" />');
}