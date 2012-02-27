<?php

class ToxgTestHarness extends smCore\TemplateEngine\Template
{
	static $test = 'pass_var';
	static $to_escape = '& < > "';
	const TEST = 'pass_const';

	protected $layers = array('output--toxg-direct');
	protected $expect_fail = false;
	protected $expect_fail_line = null;
	protected $expect_output = null;
	protected $expect_output_trim = false;
	protected $expect_output_fail_line = null;
	protected $output_params = array();

	public function __construct()
	{
		parent::__construct(null);
		$this->source_files = array();

		// Don't let previous mappings confuse us.
		smCore\TemplateEngine\Errors::reset();
	}

	public function setOutputParams(array $output_params)
	{
		$this->output_params = $output_params;
	}

	public function addData($data)
	{
		$this->source_files[] = new smCore\TemplateEngine\Source($data, 'unit-test-file');
	}

	public function addWrappedData($data)
	{
		$this->addData('<tpl:template name="my:output">' . $data . '</tpl:template>');
	}

	public function addDataForOverlay()
	{
		$this->addData('<tpl:template name="my:output"><my:example /></tpl:template>');
	}

	public function addOverlay($data)
	{
		$this->overlays[] = new smCore\TemplateEngine\Overlay(new smCore\TemplateEngine\Source($data, 'unit-test-overlay'));
	}

	public function addWrappedOverlay($data)
	{
		$this->addOverlay('<tpl:alter match="my:output my:example" position="after">' . $data . '</tpl:alter>');
	}

	public function setLayers(array $layers)
	{
		$this->layers = $layers;
	}

	public function expectFailure($line = null, $type = true)
	{
		$this->expect_fail_line = $line;
		$this->expect_fail = $type;
	}

	public function isExceptionFailure($e)
	{
		if ($this->expect_fail_line !== null && $e->tpl_line != $this->expect_fail_line)
			return 'Wrong line: ' . $e->getMessage();

		if ($this->expect_fail === false)
			return $e->getMessage();
		if ($this->expect_fail !== true && $this->expect_fail !== $e->getCode())
			return 'Wrong message: ' . $e->getMessage();

		return false;
	}

	public function expectOutput($data, $trim = true)
	{
		$this->expect_output = $data;
		$this->expect_output_trim = $trim;
	}

	public function expectOutputFailure($line)
	{
		$this->expect_output_fail_line = $line;
		$this->expect_output = '';
	}

	public function isFailure()
	{
		if ($this->expect_fail !== false)
			return 'Expected to fail with exception.';

		return false;
	}

	public function compile($my_ns)
	{
		$cache_file = dirname(__DIR__) . '/.test.output';

		parent::compile($cache_file);

		// Try to lint it, we can't eval or it will define functions.
		if (!php_validate_syntax(file_get_contents($cache_file)))
			throw new Exception('Lint failure.');

		if ($this->expect_output !== null)
		{
			require($cache_file);
			return $this->testOutput($my_ns);
		}
	}

	protected function testOutput($my_ns)
	{
		try
		{
			$actual = $this->testOutputExecute($my_ns);
			$failed = false;
		}
		catch (Exception $e)
		{
			if ($this->expect_output_fail_line === null)
				throw $e;
			elseif ($e->getFile() !== 'unit-test-file')
				throw new Exception('Error did not occur in unit-test-file.');
			elseif ($this->expect_output_fail_line != $e->getLine())
				throw new Exception('Error did not occur on the correct line: ' . $e->getLine());

			$actual = '';
			$failed = true;
		}

		if ($this->expect_output_fail_line !== null && !$failed)
			throw new Exception('Expecting a failure during output.');

		if ($this->expect_output != ($this->expect_output_trim ? trim(preg_replace('~\s+~', ' ', $actual)) : $actual))
		{
			throw new Exception('Output did not match expected. Expected: <tt>' . htmlspecialchars($this->expect_output) . '</tt>, but received: <tt>' . htmlspecialchars($actual) . '</tt>');
		}

		return $actual;
	}

	protected function testOutputExecute($my_ns)
	{
		ob_start();

		try
		{
			foreach ($this->layers as $layer)
			{
				$func_prefix = smCore\TemplateEngine\Expression::makeTemplateName($my_ns, $layer);
				call_user_func_array($func_prefix . '_above', array(&$this->output_params));
			}

			$rev = array_reverse($this->layers);
			foreach ($rev as $layer)
			{
				$func_prefix = smCore\TemplateEngine\Expression::makeTemplateName($my_ns, $layer);
				call_user_func_array($func_prefix . '_below', array(&$this->output_params));
			}

			return ob_get_clean();
		}
		catch (Exception $e)
		{
			ob_end_clean();
			throw $e;
		}
	}

	public function compileFirstPass()
	{
		$temp = array();
		foreach ($this->source_files as $file)
			$temp[] = clone $file;

		parent::compileFirstPass();

		$this->source_files = $temp;
	}

	public function __destruct()
	{
		$cache_file = dirname(__DIR__) . '/.test.output';

		if (file_exists($cache_file))
			@unlink($cache_file);
	}
}