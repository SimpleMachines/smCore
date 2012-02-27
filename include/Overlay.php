<?php

namespace smCore\TemplateEngine;

class Overlay
{
	const RECURSION_LIMIT = 10;

	protected $source = null;
	protected $alters = array();
	protected $parse_state = 'outside';
	protected $parse_alter = null;
	protected $match_tree = array();
	protected $match_recursion = array();

	public function __construct($file, array $called_overlays = array())
	{
		if ($file instanceof Source)
		{
			$this->source = $file;
			$this->source->initialize();
		}
		elseif ($file !== null)
			$this->source = new SourceFile($file);

		// This array is indexed by position.
		$this->alters = array(
			'before' => array(),
			'after' => array(),
			'beforecontent' => array(),
			'aftercontent' => array(),
		);

		$this->called_overlays = $called_overlays;
	}

	public function setNamespaces(array $uris)
	{
		$this->source->setNamespaces($uris);
	}

	public function setupParser(Parser $parser)
	{
		$parser->listen('parsedElement', array($this, 'parsedElement'));
	}

	public function parse()
	{
		if ($this->source === null)
			throw new Exception('overlay_no_source');

		while ($token = $this->source->readToken())
			$this->parseToken($token);

		if ($this->parse_state !== 'outside')
			throw new Exception('overlay_incomplete');
	}

	public function parseToken(Token $token)
	{
		switch ($this->parse_state)
		{
		case 'outside':
			$this->parseOutside($token);
			break;

		case 'alter':
			$this->parseInAlter($token);
			break;

		default:
			$token->toss('parsing_internal_error');
		}

		// We return telling the caller whether we need more tokens.
		return $this->parse_state !== 'outside';
	}

	protected function parseOutside(Token $token)
	{
		switch ($token->type)
		{
		case 'tag-start':
		case 'tag-end':
			// We're only interested in tpl:alter or a tpl:container.
			if ($token->nsuri != Template::TPL_NAMESPACE)
				$token->toss('overlay_element_outside_alter', $token->prettyName());

			if ($token->name === 'alter')
				$this->setupAlter($token);
			elseif ($token->name !== 'container')
				$token->toss('overlay_element_outside_alter', $token->prettyName());

			break;

		case 'comment':
		case 'comment-start':
		case 'comment-end':
			// Eat silently.  Yum, yum, comments are tasty.
			break;

		case 'content':
			if (trim($token->data) !== '')
				$token->toss('overlay_content_outside_alter');

			// Otherwise, just whitespace, ignore it.
			break;

		case 'tag-empty':
			if ($token->nsuri == Template::TPL_NAMESPACE)
			{
				if ($token->name === 'alter')
					$token->toss('overlay_alter_must_be_not_empty');
				else
					$token->toss('overlay_element_outside_alter', $token->prettyName());
			}
			else
				$token->toss('overlay_element_outside_alter', $token->prettyName());
			break;

		default:
			$token->toss('overlay_other_outside_alter', $token->type);
		}
	}

	protected function parseInAlter(Token $token)
	{
		switch ($token->type)
		{
		case 'tag-end':
			if ($token->type === 'tag-end' && $token->nsuri == Template::TPL_NAMESPACE && $token->name === 'alter')
			{
				// Okay, let's end it.
				$this->finalizeAlter();
				break;
			}

			// Intentional fallthrough, any other tag-ends should be copied.

		default:
			// We copy everything else.
			$this->parse_alter['data'][] = $token;
		}
	}

	protected function setupAlter(Token $token)
	{
		if (!isset($token->attributes['match'], $token->attributes['position']))
			$token->toss('tpl_alter_missing_match_position');
		if (!isset($this->alters[$token->attributes['position']]))
			$token->toss('tpl_alter_invalid_position');
		if (trim($token->attributes['match'], " \t\r\n") === '')
			$token->toss('tpl_alter_missing_match_position');

		$this->parse_state = 'alter';
		$this->parse_alter = &$this->alters[$token->attributes['position']][];

		$this->parse_alter['token'] = $token;
		$this->parse_alter['file'] = $token->file;
		$this->parse_alter['line'] = $token->line;
		$this->parse_alter['data'] = array();
		$this->parse_alter['match'] = $token->attributes['match'];
		$this->parse_alter['name'] = isset($token->attributes['name']) ? $token->attributes['name'] : false;
		if ($this->parse_alter['name'] !== false)
		{
			list($ns, $name) = explode(':', $this->parse_alter['name']);
			$nsuri = $token->getNamespace($ns);
			if (empty($ns) || empty($name) || empty($nsuri))
				$token->toss('Invalid name for tpl:alter');
		}
	}

	protected function finalizeAlter()
	{
		$this->parse_state = 'outside';

		if (!empty($this->parse_alter['name']) && !in_array($this->parse_alter['name'], $this->called_overlays))
		{
			$this->parse_alter = false;
			return true;
		}

		// Load the matches now, we don't do it previously anymore because an alter not called may not have any alters
		$matches = preg_split('~[ \t\r\n]+~', $this->parse_alter['match']);
		$this->parse_alter['match'] = array();
		foreach ($matches as $match)
		{
			if (strpos($match, ':') === false)
				$this->parse_alter['token']->toss('tpl_alter_match_without_ns', $match);

			list ($ns, $name) = explode(':', $match, 2);

			if (empty($ns) || empty($name))
				$this->parse_alter['token']->toss('generic_tpl_no_ns_or_name');

			$nsuri = $this->parse_alter['token']->getNamespace($ns);

			if ($nsuri === false)
				$this->parse_alter['token']->toss('tpl_alter_match_unknown_ns', $ns);
			if (strlen($name) === 0)
				$token->toss('tpl_alter_match_empty_name', $match);

			// Just store it "fully qualified"...
			$this->parse_alter['match'][] = $nsuri . ':' . $name;
		}

		$this->parse_alter['source'] = new Source($this->parse_alter['data'], $this->parse_alter['file'], $this->parse_alter['line']);
		if ($this->source !== null)
			$this->parse_alter['source']->copyNamespaces($this->source);
	}

	public function parsedElement(Token $token, Parser $parser)
	{
		// This is where we hook into the parser.  It's sorta complicated, because of positions.
		// When you use a template or something, we modify its usage inline.
		// For "before": BEFORE template start/empty tag.
		// For "beforecontent": AFTER template start tag, or BEFORE template empty tag.
		// For "aftercontent": BEFORE template end tag, or AFTER template empty tag.
		// For "after": AFTER template end tag.

		// We don't care about instructions, just templates.
		if ($token->nsuri == Template::TPL_NAMESPACE)
			return;

		$fqname = $token->nsuri . ':' . $token->name;

		if ($token->type === 'tag-start')
		{
			if (!isset($this->match_recursion[$fqname]))
				$this->match_recursion[$fqname] = 0;
			$this->match_recursion[$fqname]++;
			array_push($this->match_tree, $token);

			// Maybe this is dumb, I can't really think of when recursing once will even be okay?
			if ($this->match_recursion[$fqname] > self::RECURSION_LIMIT)
				$token->toss('tpl_alter_recursion', $token->prettyName());

			$this->insertMatchedAlters('before', 'normal', $token, $parser);
			$this->insertMatchedAlters('beforecontent', 'defer', $token, $parser);
		}
		elseif ($token->type === 'tag-end')
		{
			if (empty($this->match_tree))
				$token->toss('parsing_tag_already_closed', $token->prettyName());

			$this->match_recursion[$fqname]--;
			$close_token = array_pop($this->match_tree);

			if ($close_token->nsuri != $token->nsuri || $close_token->name != $token->name)
				$token->toss('parsing_tag_end_unmatched', $token->prettyName(), $close_token->prettyName(), $close_token->file, $close_token->line);

			$this->insertMatchedAlters('aftercontent', 'normal', $close_token, $parser);
			$this->insertMatchedAlters('after', 'defer', $close_token, $parser);
		}
		// Since we convert elements to pairs from empty, we don't care about tag-empty.
		// !!! Should this be a Exception?
		else
			throw new Exception('Unexpected token type: ' . $token->type);
	}

	protected function insertMatchedAlters($position, $defer, Token $token, Parser $parser)
	{
		// We need the fully-qualified name to do matching.
		$fqname = $token->nsuri . ':' . $token->name;

		$alters = $this->alters[$position];
		foreach ($alters as $alter)
		{
			if (!$alter)
				continue;

			if (in_array($fqname, $alter['match']))
				$parser->insertSource(clone $alter['source'], $defer === 'defer');
		}
	}
}