<?php

class SampleTheme extends smCore\TemplateEngine\Theme
{
	public $context = array();
	protected $needs_compile = true;

	public function isTemplateUsed($name)
	{
		return smCore\TemplateEngine\Template::isTemplateUsed($this->nsuri, $name);
	}

	public function output()
	{
		$this->setTemplateParam('context', $this->context);
		$this->addCommonVars(array('context'));
		parent::output();
	}
}