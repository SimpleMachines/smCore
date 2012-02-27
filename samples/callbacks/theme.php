<?php

class MyTheme extends SampleTheme
{
	protected $nsuri = 'http://www.example.com/#site';

	public function output()
	{
		$this->setListeners();
		return parent::output();
	}

	protected function setListeners()
	{
		// When compiling, ask it to tell us when it sees templates...
		$this->templates->listenEmitBasic('template', array($this, 'hookDynamic'));
	}

	public function hookDynamic(smCore\TemplateEngine\Builder $builder, $type, array $attributes, smCore\TemplateEngine\Token $token)
	{
		list ($ns, $name) = explode(':', $attributes['name'], 2);
		$nsuri = $token->getNamespace($ns);

		if ($nsuri == $this->nsuri && $name === 'dynamic')
			$builder->emitCode('global $theme; $dynamic = $theme->loadDynamic();', $token);
	}

	public function loadDynamic()
	{
		// Pretend we're actually using the database here.

		return array(
			array(
				'title' => 'Some title',
				'description' => 'Some description',
			),
			array(
				'title' => 'Another title',
				'description' => 'And another description',
			),
		);
	}
}