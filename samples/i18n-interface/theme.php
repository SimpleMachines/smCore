<?php

class MyTheme extends SampleTheme
{
	protected $nsuri = 'http://www.example.com/#site';
	protected $lang_debugging = false;

	public function enableLangDebugging()
	{
		$this->lang_debugging = true;
	}

	public function output()
	{
		$this->setListeners();
		return parent::output();
	}

	protected function setListeners()
	{
		// We're going to replace the default implementation.
		if ($this->lang_debugging)
		{
			$this->templates->listenEmit(smCore\TemplateEngine\Template::TPL_NAMESPACE, 'output', array($this, 'tpl_output'));
			smCore\TemplateEngine\Expression::setLangFunction('MyTheme::debuggingLangString');
		}
		else
			smCore\TemplateEngine\Expression::setLangFunction('my_lang_formatter');
	}

	public function tpl_output(smCore\TemplateEngine\Builder $builder, $type, array $attributes, smCore\TemplateEngine\Token $token)
	{
		$this->requireEmpty($token);
		$this->requireAttributes(array('value'), $token);

		$escape = empty($attributes['escape']) || $attributes['escape'] !== 'false';

		$expr = $builder->parseExpression('normal', $attributes['value'], $token);
		$debug = isset($attributes['debug']) && $attributes['debug'] === 'false' ? 'false' : 'true';

		if ($escape)
			$builder->emitOutputParam(__CLASS__ . '::escapeDebuggingHTML(' . $expr . ', ' . $debug . ')', $token);
		else
			$builder->emitOutputParam('(' . $expr . ')', $token);

		// False means: don't process any other events for this.
		return false;
	}

	protected function requireEmpty(smCore\TemplateEngine\Token $token)
	{
		if ($token->type !== 'tag-empty')
			$token->toss('generic_tpl_must_be_empty', $token->prettyName());
	}

	protected function requireNotEmpty(smCore\TemplateEngine\Token $token)
	{
		if ($token->type === 'tag-empty')
			$token->toss('generic_tpl_must_be_not_empty', $token->prettyName());
	}

	protected function requireAttributes(array $reqs, smCore\TemplateEngine\Token $token)
	{
		if ($token->type === 'tag-end')
			return;

		foreach ($reqs as $req)
		{
			if (!isset($token->attributes[$req]))
				$token->toss('generic_tpl_missing_required', $req, $token->prettyName(), implode(', ', $reqs));
		}
	}

	static function escapeDebuggingHTML($string, $debug)
	{
		// We still need to escape for XSS reasons, but we want to markup the language strings.
		$string = htmlspecialchars($string);

		$replacements = array(
			'~&lt;&lt;&lt;lang:([^&:]+):(\d+)&gt;&gt;&gt;~' => '<span class="lang-debug" data-lang="$1" data-lang-params="$2">',
			'~&lt;&lt;&lt;/lang&gt;&gt;&gt;~' => '</span>',
			'~&lt;&lt;&lt;langparam&gt;&gt;&gt;~' => '<span class="lang-debug-param">',
			'~&lt;&lt;&lt;/langparam&gt;&gt;&gt;~' => '</span>',
		);

		if ($debug)
			return preg_replace(array_keys($replacements), array_values($replacements), $string);
		else
			return preg_replace(array_keys($replacements), array_pad(array(), count($replacements), ''), $string);
	}

	static function debuggingLangString($key, $params = array())
	{
		// This is kinda a cheap way to format it so we can find it later.
		foreach ($params as $id => $param)
			$params[$id] = '<<<langparam>>>' . $param . '<<</langparam>>>';

		$text = call_user_func('my_lang_formatter', $key, $params);

		// This is kinda a cheap way to format it so we can find it later.
		return '<<<lang:' . $key[0] . ':' . count($params) . '>>>' . $text . '<<</lang>>>';
	}
}