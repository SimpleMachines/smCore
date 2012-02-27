<?php

class ToxgTestList
{
	protected $files = array();

	public function __construct()
	{
	}

	public function loadFrom($path)
	{
		$this->getTestFiles('', realpath($path));

		foreach ($this->files as $file)
			include($file);
	}

	public function getTestFuncs()
	{
		$funcs = get_defined_functions();

		$list = array();
		foreach ($funcs['user'] as $func)
		{
			if (strpos($func, 'test_') === 0)
				$list[] = $func;
		}

		return $list;
	}

	public function executeTest($func, &$reason, &$time)
	{
		// I know, yuck, this is rudimentry.  I am not worrying about it now, want to write the tests.
		$st = microtime(true);

		try
		{
			$harness = new ToxgTestHarness();
			smCore\TemplateEngine\StandardElements::useIn($harness);
			$harness->setNamespaces(array('tpl' => smCore\TemplateEngine\Template::TPL_NAMESPACE, 'my' => 'dummy' . $func));
			$func($harness);
			$code = $harness->compile('dummy' . $func);

			$failure = $harness->isFailure();
			$reason = $code;
		}
		catch (smCore\TemplateEngine\Exception $e)
		{
			$failure = $harness->isExceptionFailure($e);
			$reason = '';
		}
		catch (Exception $e)
		{
			$failure = $e->getMessage();
			$reason = '';
		}

		$et = microtime(true);
		$time = $et - $st;

		if ($failure !== false)
			$reason = $failure;

		return $failure === false;
	}

	protected function getTestFiles($path, $full_prefix)
	{
		$full = realpath($full_prefix . '/' . $path);
		$path_slash = $path == '' ? '' : $path . '/';

		$dir = dir($full);

		while ($entry = $dir->read())
		{
			if ($entry[0] === '.' || $path . $entry === 'index.php' || $path . $entry === 'include' || end(explode('.', $entry)) !== 'php')
				continue;
			elseif (is_dir($full . '/' . $entry))
				$this->getTestFiles($path_slash . $entry, $full_prefix);
			else
				$this->files[] = $full . '/' . $entry;
			
		}

		$dir->close();
	}
}