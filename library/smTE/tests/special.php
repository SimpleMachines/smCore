<?php

// NOTE: These tests don't use much of the harness, because they're testing more specifics.

function test_special_001($harness)
{
	// Make sure the theme works in general.

	$theme = new ToxgTestTheme();
	$theme->loadTemplates('
		<tpl:template name="site:output">
			pass
		</tpl:template>', __FUNCTION__);

	$theme->checkOutput('pass');
}

function test_special_inherit_001($harness)
{
	// Make sure inheritance overrides.

	$theme = new ToxgTestTheme();
	$theme->loadTemplates('
		<tpl:template name="site:output">
			pass
		</tpl:template>', __FUNCTION__, array('
		<tpl:template name="site:output">
			fail
		</tpl:template>',
	));

	$theme->checkOutput('pass');
}

function test_special_inherit_002($harness)
{
	// Make sure inheritance still inheritance.

	$theme = new ToxgTestTheme();
	$theme->loadTemplates('
		<tpl:template name="site:output">
			<site:output2 />
		</tpl:template>', __FUNCTION__, array('
		<tpl:template name="site:output2">
			pass
		</tpl:template>',
	));

	$theme->checkOutput('pass');
}

function test_special_inherit_003($harness)
{
	// Make sure multiple levels of inheritance work.

	$theme = new ToxgTestTheme();
	$theme->loadTemplates('
		<tpl:template name="site:output">
			<site:output2 />
		</tpl:template>', __FUNCTION__, array('
		<tpl:template name="site:output2">
			pass
		</tpl:template>', '
		<tpl:template name="site:output2">
			fail
		</tpl:template>',
	));

	$theme->checkOutput('pass');
}

function test_special_overlay_001($harness)
{
	// Check that template-local alters don't apply to other templates.

	$theme = new ToxgTestTheme();

	$theme->loadTemplates('
		<tpl:template name="site:output">
			<site:output-a />
			<site:output-b />
		</tpl:template>

		<tpl:template name="site:output-a">
			<site:output-a2 />
		</tpl:template>

		<tpl:template name="site:output-a2">
			1
		</tpl:template>

		<tpl:alter match="site:output-a2 site:output-b2" position="before">
			BEFORE
		</tpl:alter>', __FUNCTION__ . 'a');

	$theme->loadTemplates('
		<tpl:template name="site:output-b">
			<site:output-b2 />
		</tpl:template>

		<tpl:template name="site:output-b2">
			2
		</tpl:template>', __FUNCTION__ . 'b');

	$theme->checkOutput('BEFORE 1 2');
}

function test_special_overlay_002($harness)
{
	// Check that template-local alters do apply to inherited templates.

	$theme = new ToxgTestTheme();

	$theme->loadTemplates('
		<tpl:template name="site:output">
			<site:output-a />
			<site:output-b />
		</tpl:template>

		<tpl:template name="site:output-a">
			<site:output-a2 />
		</tpl:template>

		<tpl:template name="site:output-a2">
			1
		</tpl:template>

		<tpl:alter match="site:output-a2 site:output-b2" position="before">
			BEFORE
		</tpl:alter>', __FUNCTION__ . 'a', array('
		<tpl:template name="site:output-b">
			<site:output-b2 />
		</tpl:template>

		<tpl:template name="site:output-b2">
			2
		</tpl:template>',
	));

	$theme->checkOutput('BEFORE 1 BEFORE 2');
}

function test_special_parser_001($harness)
{
	$filename = tempnam('/tmp', 'test');
	$data = '<tpl:template name="site:output">test</tpl:template>';
	file_put_contents($filename, $data);

	$theme = new ToxgTestTheme();
	$theme->loadTemplatesSource($filename, __FUNCTION__ . 'a');

	$theme->checkOutput('test');

	$theme = null;
	@unlink($filename);
}

function test_special_errors_001($harness)
{
	$old = set_error_handler('printf');
	restore_error_handler();

	// Make sure we're not still under some other error handler.
	if ($old != 'error_handler')
		throw new Exception('Expecting to be just one level deep in error handlers.');

	$theme = new ToxgTestTheme();
	$theme->loadTemplates('
		<tpl:template name="site:output">
			pass
		</tpl:template>', __FUNCTION__);

	$theme->checkOutput('pass');

	$old = set_error_handler('printf');
	restore_error_handler();

	if ($old != 'error_handler')
		throw new Exception('Expecting to still only be one level deep in error handlers.');
}

function test_special_errors_002($harness)
{
	$old = set_error_handler('printf');
	restore_error_handler();

	// Make sure we're not still under some other error handler.
	if ($old != 'error_handler')
		throw new Exception('Expecting to be just one level deep in error handlers.');

	$theme = new ToxgTestTheme();
	$theme->loadTemplates('
		<tpl:template name="site:output">
			{$undef}
		</tpl:template>', __FUNCTION__);

	$theme->checkOutputFailure(__FUNCTION__, 3);

	$old = set_error_handler('printf');
	restore_error_handler();

	if ($old != 'error_handler')
		throw new Exception('Expecting to still only be one level deep in error handlers.');
}

function test_special_errors_003($harness)
{
	// Just to make sure we're not under any other error handlers; that's tested separately.
	for ($i = 0; $i < 1000; $i++)
		restore_error_handler();

	// This tests the default error handler.
	try
	{
		$prev = ini_get('display_errors');
		ini_set('display_errors', '1');

		$theme = new ToxgTestTheme();
		$theme->loadTemplates('
			<tpl:template name="site:output">
				<tpl:if test="{$undef} == 1">
					blah
				</tpl:if>
			</tpl:template>', __FUNCTION__);

		$theme->checkOutputContains('~undef.*' . preg_quote(__FUNCTION__, '~') . '.*line.*3~');

		ini_set('display_errors', $prev);
		set_error_handler('error_handler');
	}
	catch (Exception $e)
	{
		// Need to set these back.
		ini_set('display_errors', $prev);
		set_error_handler('error_handler');

		throw $e;
	}
}

function test_special_errors_004($harness)
{
	// Just to make sure we're not under any other error handlers; that's tested separately.
	for ($i = 0; $i < 1000; $i++)
		restore_error_handler();

	// This tests the default error handler.
	try
	{
		$prev = ini_get('display_errors');
		ini_set('display_errors', '0');

		$theme = new ToxgTestTheme();
		$theme->loadTemplates('
			<tpl:template name="site:output">
				<tpl:if test="{$undef} == 1">
					blah
				</tpl:if>
			</tpl:template>', __FUNCTION__);

		$theme->checkOutput('');

		ini_set('display_errors', $prev);
		set_error_handler('error_handler');
	}
	catch (Exception $e)
	{
		// Need to set these back.
		ini_set('display_errors', $prev);
		set_error_handler('error_handler');

		throw $e;
	}
}