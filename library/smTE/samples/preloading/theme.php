<?php

class MyTheme extends SampleTheme
{
	public function output()
	{
		$this->setListeners();
		return parent::output();
	}

	protected function setListeners()
	{
		// When compiling, ask it to tell us when it sees site:dynamic...
		$this->templates->listenEmit($this->nsuri, 'dynamic', array($this, 'site_dynamic'));
	}

	public function site_dynamic(smCore\TemplateEngine\Builder $builder, $type, array $attributes, smCore\TemplateEngine\Token $token)
	{
		// This just loads the data when/if the template is ever used.
		// Inside there, we'll load the data smartly based on what's needed.
		if ($type === 'tag-start')
		{
			$builder->emitCode('global $theme; $dynamic = $theme->loadDynamic();', $token);
			// And for illustration:
			$builder->emitCode('echo \'<em>Loaded:</em><pre>\', htmlspecialchars(print_r($dynamic, true)), \'</pre>\';', $token);
		}
	}

	public function loadDynamic()
	{
		// Pretend we're actually using the database here.

		// Do we need to spend time getting the descriptions for each item?
		$need_desc = $this->isTemplateUsed('dynamic-desc');
		// That's just a helper for the following:
		//$need_desc = smCore\TemplateEngine\Template::isTemplateUsed($this->nsuri, 'dynamic-desc');

		if ($need_desc)
			return array(
				array(
					'title' => 'Some title',
					'description' => 'Some description',
				),
			);
		else
			return array(
				array(
					'title' => 'Some title',
				),
			);
	}
}