<?php

namespace smCore\TemplateEngine;

class Template
{
	const VERSION = '0.1-alpha6';
	// !!! Need a domain name/final name/etc.?
	const TPL_NAMESPACE = 'urn:toxg:template';

	protected $source_files = array();
	protected $builder = null;
	protected $prebuilder = null;
	protected $namespaces = array();
	protected $overlays = array();
	protected $common_vars = array();
	protected $debugging = true;
	protected $overlayCalls = array();

	protected static $usage = array();

	public function __construct($source_file, $builder = null)
	{
		$this->builder = $builder !== null ? $builder : new Builder();
		$this->source_files[] = $source_file;
	}

	public function addInheritedFile($source_file)
	{
		$this->source_files[] = $source_file;
	}

	public function callOverlays(array $overlays)
	{
		foreach ($overlays as $overlay)
			$this->overlayCalls[] = $overlay;
	}

	public function addOverlays(array $files)
	{
		foreach ($files as $file)
			$this->overlays[] = new Overlay($file, $this->overlayCalls);
	}

	public function setNamespaces(array $uris)
	{
		$this->namespaces = $uris;
	}

	public function setCommonVars(array $names)
	{
		$this->common_vars = $names;
	}

	public function setDebugging($enabled = true)
	{
		$this->debugging = (boolean) $enabled;
	}

	public function listenEmit($nsuri, $name, $callback)
	{
		$this->builder->listenEmit($nsuri, $name, $callback);
	}

	public function listenEmitBasic($name, $callback)
	{
		return $this->listenEmit(self::TPL_NAMESPACE, $name, $callback);
	}

	public function setPrebuilder($prebuilder)
	{
		$this->prebuilder = $prebuilder;
	}

	public function compile($cache_file)
	{
		$this->prepareCompile();
		$this->compileFirstPass();
		$this->compileSecondPass($cache_file);
	}

	public function prepareCompile()
	{
		// Get the overlays parsed now so they can interfere with parsing later.
		foreach ($this->overlays as $overlay)
		{
			$overlay->setNamespaces($this->namespaces);
			$overlay->parse();
		}
	}

	protected function setPrebuilderCurrentTemplate()
	{
		$source_files = array();
		foreach ($this->source_files as $source_file)
			$source_files[] = is_object($source_file) ? $source_file->__toString() : (string) $source_file;
		$this->prebuilder->setCurrentTemplate(implode(PATH_SEPARATOR, $source_files));
	}

	public function compileFirstPass()
	{
		if ($this->prebuilder === null)
			$this->prebuilder = new Prebuilder();

		// Tell the prebuilder who we are so it can apply template-local stuff properly.
		$this->setPrebuilderCurrentTemplate();

		// We actually parse through each file twice: first time is for optimization.
		foreach ($this->source_files as $source_file)
		{
			// Each parser will check for duplicates, so we use a new one for each file.
			$parser = $this->createParser($source_file);
			$parser->setNamespaces($this->namespaces);

			// These both install hooks into the parser, which calls them as necessary.
			foreach ($this->overlays as $overlay)
				$overlay->setupParser($parser);
			$this->prebuilder->setupParser($parser);

			// And this is the crux of the whole operation.
			$parser->parse();
		}

		// Preparse done, give all the useful details to the builder.
		$this->builder->setPrebuilder($this->prebuilder);
	}

	public function compileSecondPass($cache_file)
	{
		// Now set up the builder (which will be eventually called by the parser.)
		$this->builder->setDebugging($this->debugging);
		$this->builder->setCommonVars($this->common_vars);
		$this->builder->setCacheFile($cache_file);

		// Tell the prebuilder who we are so it can apply template-local stuff properly.
		$this->setPrebuilderCurrentTemplate();

		try
		{
			// Each source file is processed one at a time, the builder omits duplicates.
			foreach ($this->source_files as $source_file)
			{
				// Each parser will check for duplicates, so we use a new one for each file.
				$parser = $this->createParser($source_file);
				$parser->setNamespaces($this->namespaces);

				// These both install hooks into the parser, which calls them as necessary.
				foreach ($this->overlays as $overlay)
					$overlay->setupParser($parser);
				$this->builder->setupParser($parser);

				// And this is the crux of the whole operation.
				$parser->parse();
			}

			$this->builder->finalize();
		}
		// Anything goes wrong, we kill the cache file.
		catch (Exception $e)
		{
			$this->builder->abort();
			@unlink($cache_file);

			throw $e;
		}
	}

	protected function createParser($source_file)
	{
		return new Parser($source_file);
	}

	public static function callTemplate($nsuri, $name, $params, $side = 'both')
	{
		$prefix = Expression::makeTemplateName($nsuri, $name . '--toxg-direct');
		$above = $prefix . '_above';
		$below = $prefix . '_below';

		if (!function_exists($above))
			throw new Exception('template_not_found', $name, $nsuri);

		if ($side === 'both' || $side == 'above')
			$above($params);
		if ($side === 'both' || $side == 'below')
			$below($params);
	}

	public static function markUsage($usage_info)
	{
		foreach ($usage_info as $nsuri => $names)
		{
			if (!isset(self::$usage[$nsuri]))
				self::$usage[$nsuri] = array();

			self::$usage[$nsuri] += $names;
		}
	}

	public static function isTemplateUsed($nsuri, $name)
	{
		return isset(self::$usage[$nsuri][$name]);
	}
}