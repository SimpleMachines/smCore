<?php

namespace smCore\TemplateEngine;

class Theme
{
	protected $nsuri = 'urn:site:template';
	protected $extension = 'tpl';
	protected $template_dir = '.';
	protected $inherited_dirs = array();
	protected $compile_dir = '.';
	protected $mtime_check = true;
	protected $needs_compile = false;

	protected $overlays = array();
	protected $mtime = 0;

	protected static $namespaces = array();

	protected $templates = null;
	protected $layers = array();
	protected $inside = array();
	protected $template_params = array();
	protected $compiled_list = array();
	protected $_templates = array();
	protected $_overlays = array();
	protected $overlayCalls = array();
	protected $common_vars = array();

	public function __construct($template_dir, $compile_dir, array $inherited_dirs = array(), $needs_compile = null)
	{
		if (!is_null($needs_compile) && is_bool($needs_compile))
			$this->needs_compile = $needs_compile;

		$this->template_dir = $template_dir;
		$this->compile_dir = $compile_dir;
		$this->inherited_dirs = $inherited_dirs;

		$this->templates = new TemplateList();

		self::$namespaces = array(
			'site' => $this->nsuri,
			'tpl' => Template::TPL_NAMESPACE,
		);
	}

	public function setTemplateParam($key, $value)
	{
		$this->template_params[$key] = $value;
	}

	public function listenEmit($nsuri, $name, $callback)
	{
		return $this->templates->listenEmit($nsuri, $name, $callback);
	}

	public function listenEmitBasic($name, $callback)
	{
		return $this->templates->listenEmitBasic($name, $callback);
	}

	public function callOverlays(array $name, array $ns)
	{
		$this->overlayCalls[] = is_array($name) ? implode('', $name) : $name . (is_array($ns) ? implode('', $ns) : $ns);

		return $this->templates->callOverlays($name, $ns);
	}

	public function loadOverlay($filename)
	{
		$this->_overlays[] = $filename;
	}

	public function loadTemplates($filename)
	{
		$this->_templates[] = $filename;
	}

	public function addLayer($name, $namespace = 'site')
	{
		$this->layers[] = array($name, $namespace);
	}

	public function resetLayers()
	{
		$this->layers = array();
	}

	public function recompile()
	{
		$this->needs_compile = true;
	}

	public function addTemplate($name, $namespace = 'site')
	{
		$this->inside[] = array($name, $namespace);
	}

	public function resetTemplates()
	{
		$this->inside = array();
	}

	public function addCommonVars(array $vars)
	{
		$this->common_vars = array_merge((array) $vars, $this->common_vars);
	}

	public function addNamespace($name, $nsuri = null)
	{
		$nsuri = is_null($nsuri) ? 'urn:ns:' . $name : $nsuri;

		self::$namespaces[$name] = $nsuri;
	}

	public function output()
	{
		$this->templates->setCommonVars($this->common_vars);

        $template_list = array();
        foreach ($this->inside as $inside)
            $template_list[] = $inside[1] . $inside[0];
        foreach ($this->layers as $layer)
            $template_list[] = $layer[1] . $layer[0];

		$overlay_hash = substr(md5('dummy' . implode('', $this->_overlays) . implode('', $this->overlayCalls) . implode('', $template_list)), 0, 15);

		foreach ($this->_templates as $filename)
		{
			$source = $filename;

			$inherited = array();

			// Look for it in the inherited dirs
			foreach ($this->inherited_dirs as $dir)
				if (file_exists($dir . '/' . $filename . '.' . $this->extension))
					$inherited[] = $dir . '/' . $filename . '.' . $this->extension;

			// Did they give us the full path to a file? This way, we support both a common template directory and modular template directories.
			if (file_exists($filename))
				$source = $filename;
			else if (file_exists($this->template_dir . '/' . $filename . '.' . $this->extension))
				$source = $this->template_dir . '/' . $filename . '.' . $this->extension;
			else if (!empty($inherited))
				// Take the first inherited template and use it as our main one.
				$source = array_pop($inherited);

			$compiled = $this->compile_dir . '/.toxg.' . preg_replace('~[^a-zA-Z0-9_-]~', '_', $filename) . '.' . $overlay_hash . '.php';

			// Note: if overlays change, this won't work unless the overlay was touched.
			// Normally, you'd flush the system when it needs a recompile.
			if ($this->mtime_check && !$this->needs_compile)
			{
				$this->mtime = max($this->mtime, filemtime($source));

				foreach ($inherited as $file)
					$this->mtime = max($this->mtime, filemtime($file));

				$this->needs_compile = !file_exists($compiled) || filemtime($compiled) <= $this->mtime;
			}

			$this->templates->addTemplate($source, $compiled, $inherited);
		}

		foreach ($this->_overlays as $filename)
		{
			$full = $filename;

			if (file_exists($filename))
				$full = $filename;
			else if (file_exists($this->template_dir . '/' . $filename . '.' . $this->extension))
				$full = $this->template_dir . '/' . $filename . '.' . $this->extension;

			if ($this->mtime_check)
				$this->mtime = max($this->mtime, filemtime($full));
			$this->templates->addOverlays(array($full));
		}

		if ($this->needs_compile)
		{
			StandardElements::useIn($this->templates);
			$this->templates->setNamespaces(self::$namespaces);
			$this->templates->compileAll();
		}

		$this->templates->loadAll();

		foreach ($this->layers as $layer)
			$this->callTemplate($layer[0], 'above', $layer[1]);

		foreach ($this->inside as $inside)
		{
			$this->callTemplate($inside[0], 'above', $inside[1]);
			$this->callTemplate($inside[0], 'below', $inside[1]);
		}

		$reversed = array_reverse($this->layers);
		foreach ($reversed as $layer)
			$this->callTemplate($layer[0], 'below', $layer[1]);
	}

	protected function callTemplate($name, $side, $nsuri = 'site')
	{
		$func = Expression::makeTemplateName(self::$namespaces[$nsuri], $name . '--toxg-direct') . '_' . $side;
		$func($this->template_params);
	}

	public static function getNamespace($name)
	{
		return self::$namespaces[$name];
	}
}