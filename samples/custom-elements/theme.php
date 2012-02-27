<?php

class MyTheme extends SampleTheme
{
	protected $nsuri = 'http://www.example.com/#site';
	protected $last_foreach_stack = array();

	public function output()
	{
		$this->setListeners();
		return parent::output();
	}

	protected function setListeners()
	{
		// When compiling, ask it to tell us when it sees templates...
		$this->templates->listenEmitBasic('foreach', array($this, 'tpl_foreach'));
		$this->templates->listenEmitBasic('not-last', array($this, 'tpl_not_last'));
	}

	public function hookDynamic(smCore\TemplateEngine\Builder $builder, $type, array $attributes, smCore\TemplateEngine\Token $token)
	{
		list ($ns, $name) = explode(':', $attributes['name'], 2);
		$nsuri = $token->getNamespace($ns);

		if ($nsuri == $this->nsuri && $name === 'dynamic')
			$builder->emitCode('global $theme; $dynamic = $theme->loadDynamic();', $token);
	}

	private function getUniqueKey()
	{
		return sha1(microtime(true) . rand());
	}

	private function getForeachTrackingVar($key)
	{
		// Just so we use the same name everywhere.  Starts with __toxg_ to avoid collisions.
		return '__toxg_foreach_left_' . $key;
	}

	public function tpl_foreach(smCore\TemplateEngine\Builder $builder, $type, array $attributes, smCore\TemplateEngine\Token $token)
	{
		// No from?  Assume smCore\TemplateEngine\StandardElements will handle the error?
		if (isset($attributes['from']))
		{
			$from = $builder->parseExpression('normal', $attributes['from'], $token);

			// There's two places we'll handle:
			// 1. Before the foreach, we'll figure out its length.
			// 2. At the end, inside the foreach, we'll decrement the "how many left" counter.
			//
			// Then, inside, we'll see if the counter is almost up, and know that we're at the end.
			if ($type === 'tag-start')
			{
				// Make a new key for this new foreach, and push it onto the stack.
				$key = $this->getUniqueKey();
				array_push($this->last_foreach_stack, $key);

				$var_name = $this->getForeachTrackingVar($key);

				// In case it's an object or something, we might need to calculate its size.
				$builder->emitCode('
					if (is_array(' . $from . '))
						$' . $var_name . ' = count(' . $from . ') - 1;
					else
						$' . $var_name . ' = -1;', $token);

				// Note: the newlines and whitespace here helps readability, but can make
				// error messages in templates (such as undefined index) a little less accurate.
				// It's best (but ugly) to put it all on one line.
				$builder->emitCode('
					if (!is_array(' . $from . '))
					{
						foreach (' . $from . ' as $__toxg_foreach_dummy)
							$' . $var_name . '++;
					}', $token);
			}
			elseif ($type === 'tag-end')
			{
				$key = array_pop($this->last_foreach_stack);

				$var_name = $this->getForeachTrackingVar($key);

				// And it's easy to decrement the counter, hurray.
				$builder->emitCode('
					$' . $var_name . '--;', $token);
			}
		}
	}

	public function tpl_not_last(smCore\TemplateEngine\Builder $builder, $type, array $attributes, smCore\TemplateEngine\Token $token)
	{
		if ($token->type === 'tag-empty')
			$token->toss('generic_tpl_must_be_not_empty', $token->prettyName());

		// Just peek at what's last on the stack right now (what we're in.)
		$key = end($this->last_foreach_stack);

		$var_name = $this->getForeachTrackingVar($key);

		// So now we just ask the simple question - is the current foreach at the end or not?
		if ($type == 'tag-start')
			$builder->emitCode('if ($' . $var_name . ' > 0) {');
		else
			$builder->emitCode('}');
	}
}