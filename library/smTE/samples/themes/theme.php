<?php

class MyTheme extends SampleTheme
{
	protected $nsuri = 'http://www.example.com/#site';
	protected $theme = null;

	public function __construct($name)
	{
		$this->theme = $name;
		$this->template_dir = __DIR__ . '/themes/' . $name;
		$this->compile_dir = __DIR__ . '/themes/' . $name;
		$this->inherited_dirs[] = $name === 'base' ? array() : (__DIR__ . '/themes/base');

		parent::__construct($this->template_dir, $this->compile_dir, $this->inherited_dirs);

		if (file_exists($this->template_dir . '/overlay.tpl'))
			$this->loadOverlay('overlay');
	}
}