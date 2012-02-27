<?php

class ToxgTestTheme
{
	protected $nsuri = 'http://www.example.com/#site';
	protected $template_dir = '.';
	protected $inherited_dirs = array();

	protected $templates = null;
	protected $layers = array();
	protected $inside = array('output');
	protected $compiled = array();
	public $context = array();

	public static $id = 0;

	public function __construct()
	{
		$this->nsuri .= ':' . self::$id++;

		$this->templates = new smCore\TemplateEngine\TemplateList();
		$this->templates->setNamespaces(array('site' => $this->nsuri, 'tpl' => smCore\TemplateEngine\Template::TPL_NAMESPACE));
		$this->templates->setCommonVars(array('context'));
	}

	public function loadOverlay($string, $name)
	{
		$source = new smCore\TemplateEngine\Source($string, $name);
		$this->templates->addOverlays(array($source));
	}

	public function loadTemplates($string, $name, $inherited = array())
	{
		$source = new smCore\TemplateEngine\Source($string, $name);

		$inherited_sources = array();
		foreach ($inherited as $sub_name => $string)
			$inherited_sources[] = new smCore\TemplateEngine\Source($string, $name . $sub_name);

		$this->loadTemplatesSource($source, $name, $inherited_sources);
	}

	public function loadTemplatesSource($source, $name, $inherited_sources = array())
	{
		$compiled = dirname(__DIR__) . '/.test.output.' . (self::$id++);

		$this->templates->addTemplate($source, $compiled, $inherited_sources);
		$this->compiled[] = $compiled;
	}

	public function addLayer($name)
	{
		$this->layers[] = $name;
	}

	public function removeLayer($name)
	{
		$this->layers = array_diff($this->layers, (array) $name);
	}

	public function resetLayers()
	{
		$this->layers = array();
	}

	public function addTemplate($name)
	{
		$this->inside[] = $name;
	}

	public function removeTemplate($name)
	{
		$this->inside = array_diff($this->inside, (array) $name);
	}

	public function resetTemplates($name)
	{
		$this->inside = array();
	}

	public function checkOutput($expected)
	{
		$actual = trim(preg_replace('~\s+~', ' ', $this->output()));

		if ($actual != $expected)
			throw new Exception('Output did not match expected.');
	}

	public function checkOutputContains($expected)
	{
		$actual = trim(preg_replace('~\s+~', ' ', $this->output()));

		if (preg_match($expected, $actual) == 0)
			throw new Exception('Output did not contain what it was expected to.');
	}

	public function checkOutputFailure($file, $line)
	{
		try
		{
			$this->output();
			throw new Exception('Expecting template to fail, but it didn\'t.');
		}
		catch (Exception $e)
		{
			if ($file != $e->getFile() || $line != $e->getLine())
				throw $e;
		}
	}

	public function output()
	{
		if (!empty($this->compiled))
			$this->compile();

		ob_start();

		try
		{
			foreach ($this->layers as $layer)
				$this->callTemplate($layer, 'above');

			foreach ($this->inside as $inside)
				$this->callTemplate($inside, 'both');

			$reversed = array_reverse($this->layers);
			foreach ($reversed as $layer)
				$this->callTemplate($layer, 'below');

			return ob_get_clean();
		}
		catch (Exception $e)
		{
			ob_end_clean();
			throw $e;
		}
	}

	public function compile()
	{
		smCore\TemplateEngine\StandardElements::useIn($this->templates);
		$this->templates->compileAll();
		$this->templates->loadAll();

		foreach ($this->compiled as $compiled)
			@unlink($compiled);
		$this->compiled = array();
	}

	public function isTemplateUsed($name)
	{
		return smCore\TemplateEngine\Template::isTemplateUsed($this->nsuri, $name);
	}

	protected function callTemplate($name, $side)
	{
		smCore\TemplateEngine\Template::callTemplate($this->nsuri, $name, array('context' => $this->context), $side);
	}
}