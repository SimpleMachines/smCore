<?php

function test_output_001($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output">pass</tpl:template>');
}

function test_output_002($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output1">pass</tpl:template><tpl:template name="my:output2">fail</tpl:template><tpl:template name="my:output"><my:output1 /></tpl:template>');
}

function test_output_003($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output"><tpl:if test="true">pass<tpl:else />fail</tpl:if></tpl:template>');
}

function test_output_004($harness)
{
	$harness->expectOutput('test: <li id="123">test</li>');
	$harness->addData('
<tpl:template name="my:test1"><li id="{$id}"><tpl:content /></li></tpl:template>
<tpl:template name="my:test2">{$name}: <my:test1 id="123"><tpl:content /></my:test1></tpl:template>
<tpl:template name="my:output"><my:test2 name="test">test</my:test2></tpl:template>');
}

function test_output_005($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output"><tpl:set var="{$x}" value="3" /><tpl:if test="{$x} == 1">fail1<tpl:else test="{$x} == 2" />fail2<tpl:else test="{$x} == 3" />pass<tpl:else test="{$x} == 4" />fail2</tpl:if></tpl:template>');
}

function test_output_006($harness)
{
	$harness->expectOutput('12345');
	$harness->addData('<tpl:template name="my:output"><tpl:set var="{$x}" value="array(1, 2, 3, 4, 5)" /><tpl:foreach from="{$x}" as="{$y}">{$y}</tpl:foreach></tpl:template>');
}

function test_output_007($harness)
{
	$harness->expectOutput('<>& <>&');
	$harness->addData('<tpl:template name="my:output"><tpl:set var="{$x}" value="\'<>&\'" /><tpl:output value="\'<>&\'" escape="false" /> <tpl:output value="{$x}" escape="false" /></tpl:template>');
}

function test_output_009($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output"><tpl:set var="{$x}" value="\'pass\'" /><!--- {$x} ---> {$x}</tpl:template>');
}

function test_output_010($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output"><my:underscore x_y_z="pass" /></tpl:template><tpl:template name="my:underscore">{$x_y_z}</tpl:template>');
}

function test_output_011($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:template name="my:output"><my:example /></tpl:template>
		<tpl:template name="my:example">
			<tpl:set var="{$y}" value="\'pass\'" />
			<tpl:content />
			{$y}
		</tpl:template>');
}

function test_output_012($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:template name="my:output"><my:example></my:example></tpl:template>
		<tpl:template name="my:example">
			<tpl:set var="{$y}" value="\'pass\'" />
			<tpl:content />
			{$y}
		</tpl:template>');
}

function test_output_013($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:template name="my:output"><my:example /><my:example2 /></tpl:template>
		<tpl:template name="my:example">
			pass
		</tpl:template>');
}

function test_output_014($harness)
{
	$harness->expectOutputFailure(4);
	$harness->addData('
		<tpl:template name="my:output"><my:example /><my:example2 /></tpl:template>
		<tpl:template name="my:example">
			{$undef}
		</tpl:template>');
}

function test_output_015($harness)
{
	$harness->expectOutputFailure(4);
	$harness->addData('
		<tpl:template name="my:output"><my:example /><my:example2 /></tpl:template>
		<tpl:template name="my:example">
			<tpl:if test="{$undef}"></tpl:if>
		</tpl:template>');
}

function test_output_016($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output"><my:example>pass</my:example></tpl:template>');
}

function test_output_017($harness)
{
	$harness->expectOutput('pass');
	$harness->addWrappedData('');
	$harness->addWrappedOverlay('pass');
}

function test_output_018($harness)
{
	$harness->expectOutput('pass pass pass');
	$harness->addData('<tpl:template name="my:output"><my:example /> <my:example /> <my:example /></tpl:template>');
	$harness->addOverlay('<tpl:alter match="my:example" position="after">pass</tpl:alter>');
}

function test_output_019($harness)
{
	$harness->expectOutput('Aalter(B)C');
	$harness->addData('
		<tpl:template name="my:output">A<my:example>B</my:example>C</tpl:template>
		<tpl:template name="my:example">(<tpl:content />)</tpl:template>');
	$harness->addOverlay('<tpl:alter match="my:example" position="before">alter</tpl:alter>');
}

function test_output_020($harness)
{
	$harness->expectOutput('A(alterB)C');
	$harness->addData('
		<tpl:template name="my:output">A<my:example>B</my:example>C</tpl:template>
		<tpl:template name="my:example">(<tpl:content />)</tpl:template>');
	$harness->addOverlay('<tpl:alter match="my:example" position="beforecontent">alter</tpl:alter>');
}

function test_output_021($harness)
{
	$harness->expectOutput('A(Balter)C');
	$harness->addData('
		<tpl:template name="my:output">A<my:example>B</my:example>C</tpl:template>
		<tpl:template name="my:example">(<tpl:content />)</tpl:template>');
	$harness->addOverlay('<tpl:alter match="my:example" position="aftercontent">alter</tpl:alter>');
}

function test_output_022($harness)
{
	$harness->expectOutput('A(B)alterC');
	$harness->addData('
		<tpl:template name="my:output">A<my:example>B</my:example>C</tpl:template>
		<tpl:template name="my:example">(<tpl:content />)</tpl:template>');
	$harness->addOverlay('<tpl:alter match="my:example" position="after">alter</tpl:alter>');
}

function test_output_023($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('<tpl:template name="my:output"><my:example x="pass">{$x}</my:example></tpl:template>');
}

function test_output_024($harness)
{
	$harness->setLayers(array('output--toxg-direct', 'layer--toxg-direct'));
	$harness->expectOutput('beforebeforeafterafter');
	$harness->addData('
		<tpl:template name="my:output">before<tpl:content />after</tpl:template>
		<tpl:template name="my:layer">before<tpl:content />after</tpl:template>');
}

function test_output_025($harness)
{
	$harness->setLayers(array('output--toxg-direct', 'layer'));
	$harness->expectOutput('beforebeforeafterafter');
	$harness->addData('
		<tpl:template name="my:output">before<tpl:content />after</tpl:template>
		<tpl:template name="my:layer">before<tpl:content />after</tpl:template>');
}

function test_output_026($harness)
{
	$harness->expectOutput(ToxgTestHarness::$test);
	$harness->addWrappedData('{ToxgTestHarness::$test}');
}

function test_output_027($harness)
{
	$harness->expectOutput(ToxgTestHarness::TEST);
	$harness->addWrappedData('{ToxgTestHarness::TEST}');
}

function test_output_028($harness)
{
	$harness->setCommonVars(array('common'));
	$harness->setOutputParams(array('common' => 'pass'));
	$harness->expectOutput('pass pass');
	$harness->addWrappedData('{$common} <tpl:content />{$common}');
}

function test_output_029($harness)
{
	// !!! Known bug: we want to know if this changes.
	$harness->expectOutput('fail');
	$harness->addData('
		<tpl:container xmlns:blah="urn:toxg-example:1">
			<tpl:template name="blah:name">pass</tpl:template>
			<tpl:template name="blah:name" xmlns:blah="urn:toxg-example:2">fail</tpl:template>
			<tpl:template name="my:output"><blah:name /></tpl:template>
		</tpl:container>');
}

function test_output_030($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:template name="my:output">as</tpl:template>
		<tpl:alter match="my:output" position="before">p</tpl:alter>
		<tpl:alter match="my:output" position="after">s</tpl:alter>');
}

function test_output_031($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:template name="my:output"><my:stuff /></tpl:template>
		<tpl:template name="my:stuff">p<tpl:content />s</tpl:template>
		<tpl:alter match="my:stuff" position="beforecontent">a</tpl:alter>
		<tpl:alter match="my:stuff" position="aftercontent">s</tpl:alter>');
}

function test_output_032($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:container xmlns:blah="http://www.example.com/#blah1">
			<tpl:template name="my:output"><tpl:if test="smCore\TemplateEngine\Template::isTemplateUsed(\'http://www.example.com/#blah1\', \'checkme\')">pass<tpl:else />fail</tpl:if></tpl:template>
			<tpl:template name="my:stuff"><blah:checkme /></tpl:template>
		</tpl:container>');
}

function test_output_033($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:container xmlns:blah="http://www.example.com/#blah2">
			<tpl:template name="my:output"><tpl:if test="smCore\TemplateEngine\Template::isTemplateUsed(\'http://www.example.com/#blah2\', \'checkme\')">pass<tpl:else />fail</tpl:if></tpl:template>
			<tpl:template name="my:stuff"></tpl:template>
		</tpl:container>');
	$harness->addOverlay('<tpl:alter match="my:stuff" position="after" xmlns:blah="http://www.example.com/#blah2"><blah:checkme /></tpl:alter>');
}

function test_output_034($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:container xmlns:blah="http://www.example.com/#blah3">
			<tpl:template name="my:output"><tpl:if test="smCore\TemplateEngine\Template::isTemplateUsed(\'http://www.example.com/#blah3\', \'checkme\')">fail<tpl:else />pass</tpl:if></tpl:template>
			<tpl:template name="blah:checkme"></tpl:template>
		</tpl:container>');
}

function test_output_035($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:container xmlns:blah="http://www.example.com/#blah4">
			<tpl:template name="my:output"><tpl:if test="smCore\TemplateEngine\Template::isTemplateUsed(\'http://www.example.com/#blah4\', \'checkme\')">fail<tpl:else />pass</tpl:if></tpl:template>
		</tpl:container>');
	$harness->addOverlay('<tpl:alter match="my:stuff" position="after" xmlns:blah="http://www.example.com/#blah4"><blah:checkme /></tpl:alter>');
}

function test_output_036($harness)
{
	$harness->expectOutput('<input />');
	$harness->addWrappedData('<tpl:element tpl:name="input" />');
}

function test_output_037($harness)
{
	$harness->expectOutput('<input type="text" name="test" />');
	$harness->addWrappedData('<tpl:element tpl:name="input" type="text" name="test" />');
}

function test_output_038($harness)
{
	$harness->expectOutput('<input type="text" name="pass" />');
	$harness->addData('
		<tpl:template name="my:output"><my:fake-input type="text" name="pass" /></tpl:template>
		<tpl:template name="my:fake-input"><tpl:element tpl:name="input" tpl:inherit="*" /></tpl:template>');
}

function test_output_039($harness)
{
	$harness->expectOutput('<input value="hello" type="text" name="pass" />');
	$harness->addData('
		<tpl:template name="my:output"><my:fake-input type="text" name="pass" /></tpl:template>
		<tpl:template name="my:fake-input"><tpl:element tpl:name="input" tpl:inherit="*" value="hello" /></tpl:template>');
}

function test_output_040($harness)
{
	$harness->expectOutput('<input value="hello" type="text" name="pass" />');
	$harness->addData('
		<tpl:template name="my:output"><my:fake-input type="text" name="pass" value="goodbye" /></tpl:template>
		<tpl:template name="my:fake-input"><tpl:element tpl:name="input" tpl:inherit="*" value="hello" /></tpl:template>');
}

function test_output_041($harness)
{
	$harness->expectOutput('<select name="pass" />');
	$harness->addData('
		<tpl:template name="my:output"><my:fake-input tag="select" name="pass" /></tpl:template>
		<tpl:template name="my:fake-input"><tpl:element tpl:name="{$tag}" name="{$name}" /></tpl:template>');
}

function test_output_042($harness)
{
	$harness->expectOutput('<select name="pass" />');
	$harness->addData('
		<tpl:template name="my:output"><my:fake-input tag="select" name="pass" fail="fail" /></tpl:template>
		<tpl:template name="my:fake-input"><tpl:element tpl:name="{$tag}" tpl:inherit="name" /></tpl:template>');
}

function test_output_043($harness)
{
	$harness->expectOutput('pass');
	$harness->addData('
		<tpl:template name="my:output"><my:fake-input tag="select" name="pass" fail="fail" /></tpl:template>
		<tpl:template name="my:fake-input"><my:fake-input2 tpl:inherit="name" /></tpl:template>
		<tpl:template name="my:fake-input2">{$name}</tpl:template>');
}

function test_output_044($harness)
{
	$harness->expectOutput('1 &amp; 2');
	$harness->addData('
		<tpl:template name="my:output"><my:output2 param="1 & 2" /></tpl:template>
		<tpl:template name="my:output2">{$param}</tpl:template>');
}










function test_output_045($harness)
{
	$harness->expectOutputFailure(5);
	$harness->addData('
		<tpl:template name="my:notcalled"></tpl:template>
		<tpl:template name="my:output"><my:example /><my:example2 /></tpl:template>
		<tpl:template name="my:example">
			{$undef}
		</tpl:template>');
}

function test_output_escape_001($harness)
{
	$harness->expectOutput(ToxgTestHarness::$to_escape);
	$harness->addWrappedData(ToxgTestHarness::$to_escape);
}

function test_output_escape_002($harness)
{
	$harness->expectOutput(htmlspecialchars(ToxgTestHarness::$to_escape));
	$harness->addWrappedData('{ToxgTestHarness::$to_escape}');
}

function test_output_escape_003($harness)
{
	$harness->expectOutput('<![CDATA[ <>& <>& ]]>');
	$harness->addWrappedData('<tpl:set var="{$x}" value="\'<>&\'" /><![CDATA[ <tpl:output value="\'<>&\'" /> {$x} ]]>');
}

function test_output_escape_004($harness)
{
	$harness->expectOutput('<![CDATA[ ' . ToxgTestHarness::$to_escape . ' ]]>');
	$harness->addWrappedData('<![CDATA[ {ToxgTestHarness::$to_escape} ]]>');
}

function test_output_escape_005($harness)
{
	$harness->expectOutput(htmlspecialchars(ToxgTestHarness::$to_escape, ENT_COMPAT, "UTF-8"));
	$harness->addData('<tpl:container doctype="xhtml"><tpl:template name="my:output">{ToxgTestHarness::$to_escape}</tpl:template></tpl:container>');
}

function test_output_escape_006($harness)
{
	$harness->expectOutput(htmlspecialchars(ToxgTestHarness::$to_escape, ENT_COMPAT, "UTF-8"));
	$harness->addData('<tpl:container doctype="html"><tpl:template name="my:output">{ToxgTestHarness::$to_escape}</tpl:template></tpl:container>');
}

function test_output_escape_007($harness)
{
	$harness->expectOutput('<script>' . htmlspecialchars(ToxgTestHarness::$to_escape, ENT_COMPAT, "UTF-8") . '</script>');
	$harness->addData('<tpl:container doctype="xhtml"><tpl:template name="my:output"><script>{ToxgTestHarness::$to_escape}</script></tpl:template></tpl:container>');
}

function test_output_escape_008($harness)
{
	$harness->expectOutput('<script>' . ToxgTestHarness::$to_escape . '</script>');
	$harness->addData('<tpl:container doctype="html"><tpl:template name="my:output"><script>{ToxgTestHarness::$to_escape}</script></tpl:template></tpl:container>');
}

function test_output_escape_009($harness)
{
	$harness->expectFailure(1, 'tpl_container_invalid_doctype');
	$harness->addData('<tpl:container doctype="xyz_invalid"><tpl:template name="my:output"><script>{ToxgTestHarness::$to_escape}</script></tpl:template></tpl:container>');
}

function test_output_escape_010($harness)
{
	$harness->expectOutput('<script></script>' . htmlspecialchars(ToxgTestHarness::$to_escape, ENT_COMPAT, "UTF-8") . '<script></script>');
	$harness->addData('<tpl:container doctype="html"><tpl:template name="my:output"><script></script>{ToxgTestHarness::$to_escape}<script></script></tpl:template></tpl:container>');
}