<?php

namespace smCore\TemplateEngine;

class Prebuilder
{
	protected $templates = array();
	protected $template_usage = array();
	protected $parse_state = 'outside';
	protected $overlays = array();
	protected $current_template = null;

	public function __construct()
	{
	}

	public function getTemplateForBuild($token)
	{
		$name = self::makeTemplateName($token, 'name-attr');
		if (!isset($this->templates[$name]))
			$token->toss('builder_unexpected_template');
		$template = $this->templates[$name];

		if ($template['file'] == $token->file && $template['line'] == $token->line)
			$template['should_emit'] = true;
		else
			$template['should_emit'] = false;
		$template['stage'] = 1;

		return $template;
	}

	public function getTemplateForCall($token)
	{
		$name = self::makeTemplateName($token);

		// It's okay if it's not defined yet.
		if (!isset($this->templates[$name]))
			return array(
				'name' => $name,
				'defined' => false,
				'requires' => array(),
			);
		else
		{
			$template = $this->templates[$name];
			$template['defined'] = true;
		}

		return $template;
	}

	public function getTemplateUsage()
	{
		return $this->template_usage;
	}

	public function setCurrentTemplate($template_id)
	{
		$this->current_template = $template_id;
	}

	public function setupParser(Parser $parser)
	{
		$parser->listen('parsedContent', array($this, 'parsedContent'));
		$parser->listen('parsedElement', array($this, 'parsedElement'));
	}

	public function setupOverlayParser(Parser $parser)
	{
		$this->getCurrentOverlay()->setupParser($parser);
	}

	public function parsedContent(Token $token, Parser $parser)
	{
		if ($this->parse_state === 'alter')
			$this->handleAlterToken($token);
		else
			$this->requireTemplate($token);
	}

	public function parsedElement(Token $token, Parser $parser)
	{
		if ($this->parse_state === 'alter')
		{
			$this->handleAlterToken($token);
			$this->trackUsage($token);
		}
		elseif ($token->nsuri === Template::TPL_NAMESPACE)
		{
			if ($token->name === 'template')
				$this->handleTagTemplate($token);
			elseif ($token->name === 'alter')
				$this->handleTagAlter($token);

			$okay_outside_template = array('container', 'template', 'alter');
			if (!in_array($token->name, $okay_outside_template))
				$this->requireTemplate($token);

			$this->trackUsage($token);
		}
		else
		{
			$this->requireTemplate($token);
			$this->trackUsage($token);
		}
	}

	protected function trackUsage(Token $token)
	{
		// We won't count auto-tokens because they're for direct calls.
		// !!! Better way?
		if ($token->data !== '{tpl:auto-token /}')
			$this->template_usage[$token->nsuri][$token->name] = true;
	}

	protected function handleTagTemplate(Token $token)
	{
		// We only care about start tags.
		if ($token->type === 'tag-start')
		{
			if ($this->parse_state === 'template')
				$token->toss('tpl_template_inside_template');
			elseif ($this->parse_state !== 'outside')
				$token->toss('tpl_template_inside_alter');

			$name = self::makeTemplateName($token, 'name-attr');

			// Doesn't exist yet.
			if (empty($this->templates[$name]))
			{
				$this->templates[$name] = array(
					'name' => $name,
					'file' => $token->file,
					'line' => $token->line,
					'requires' => array(),
				);

				// The (optional) requires attribute lists required attributes.
				if (!empty($token->attributes['requires']))
				{
					// It can be comma separated or space separated.
					$requires = array_filter(array_map('trim', preg_split('~[\s,]+~', $token->attributes['requires'])));

					foreach ($requires as $required)
						$this->templates[$name]['requires'][] = Expression::makeVarName($required);
				}
			}

			$this->parse_state = 'template';
		}
		elseif ($token->type === 'tag-end')
			$this->parse_state = 'outside';
	}

	protected function handleTagAlter(Token $token)
	{
		// Start tags are where the action happens.
		if ($token->type === 'tag-start')
		{
			if ($this->parse_state !== 'outside')
				$token->toss('tpl_alter_inside_template');

			$this->parse_state = 'alter';

			// We just pass it on.  The overlay will handle it.
			$this->handleAlterToken($token);
		}
		elseif ($token->type === 'tag-end')
			$this->parse_state = 'outside';
	}

	protected function requireTemplate(Token $token)
	{
		if ($this->parse_state !== 'template')
		{
			// Okay, make it pretty for the user.
			if ($token->type === 'tag-start' || $token->type === 'tag-empty' || $token->type === 'tag-end')
				$token->toss('builder_element_outside_template', $token->prettyName());
			else
				$token->toss('builder_stuff_outside_template');
		}
	}

	protected function handleAlterToken(Token $token)
	{
		if (!$this->getCurrentOverlay()->parseToken($token))
			$this->parse_state = 'outside';
	}

	protected function getCurrentOverlay()
	{
		$overlay = &$this->overlays[$this->current_template];
		if (!isset($overlay))
			$overlay = new Overlay(null);

		return $overlay;
	}

	public static function makeTemplateName($token, $type = 'token')
	{
		// Pull the nsuri and name from the name attribute?
		if ($type === 'name-attr')
		{
			list ($ns, $name) = explode(':', $token->attributes['name'], 2);
			$nsuri = $token->getNamespace($ns);
		}
		// Or from the token itself?
		elseif ($type === 'token')
		{
			$nsuri = $token->nsuri;
			$name = $token->name;
		}

		return Expression::makeTemplateName($nsuri, $name);
	}
}