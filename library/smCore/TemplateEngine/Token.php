<?php

namespace smCore\TemplateEngine;

class Token
{
	public $data = null;
	public $type = 'tag-start';
	public $file = null;
	public $line = 0;
	public $tabs = '';

	public $ns = '';
	public $nsuri = '';
	public $name = '';
	public $attributes = array();

	protected $data_pos = 0;
	protected $data_len = 0;
	protected $source = null;

	public function __construct(array $token, Source $source)
	{
		$this->source = $source;

		$this->data = $token['data'];
		$this->data_len = strlen($this->data);
		$this->type = $token['type'];
		$this->file = $token['file'];
		$this->line = $token['line'];
		$this->tabs = str_repeat("\t", $token['tabs']);

		$this->parseData();
	}

	protected function parseData()
	{
		switch ($this->type)
		{
		case 'tag-end':
			$this->parseEnd();
			break;

		case 'tag-start':
		case 'tag-empty':
			$this->parseStart();
			break;

		default:
			// Anything else, we don't do additional work on.
		}
	}

	public function getNamespace($name)
	{
		return $this->source->getNamespace($name);
	}

	public function prettyName()
	{
		return $this->ns . ':' . $this->name;
	}

	public function toss($id_message)
	{
		// For error messages, we always really want after the newline, anyway.
		if (!empty($this->data) && $this->data[0] === "\n")
			$this->line += strspn($this->data, "\n");

		$params = func_get_args();
		$params = array_slice($params, 1);

		throw new ExceptionFile($this->file, $this->line, $id_message, $params);
	}

	protected function parseStart()
	{
		$this->data_pos = 1;
		list ($this->ns, $this->name) = $this->parseName();

		// Parse the attributes which don't have any name specified, mainly for shortcuts
		$this->parseSingleAttribute($this->data_pos);

		while ($this->parseAttribute())
			continue;

		$this->setNamespace();

		// A start tag will be 1 from end, empty tag 2 from end (/>)...
		$end_offset = $this->type == 'tag-start' ? 1 : 2;

		if ($this->data_pos < strlen($this->data) - $end_offset)
			$this->toss('syntax_invalid_tag');
	}

	protected function parseSingleAttribute($pos = 0)
	{
		$start_quote = $this->firstPosOf('"', $this->data_pos - $pos);
		if ($start_quote === false || $this->data[$start_quote - 1] == '=')
			return false;
		$start_part = substr($this->data, 0, $start_quote);
		$end_part = substr($this->data, $start_quote);

		$this->data = $start_part . 'default=' . $end_part;
		return true;
	}

	protected function parseEnd()
	{
		$this->data_pos = 2;
		list ($this->ns, $this->name) = $this->parseName();

		$this->setNamespace();

		if ($this->data_pos < strlen($this->data) - 1)
			$this->toss('syntax_invalid_tag_end');
	}

	protected function setNamespace()
	{
		if ($this->ns !== '')
			$this->nsuri = $this->source->getNamespace($this->ns);

		// If we don't have a namespace, this is XHTML.
		if ($this->nsuri === false)
			$this->type = 'content';
	}

	protected function parseName()
	{
		// None of these are valid name chars, but they all end the name.
		$after_name = $this->firstPosOf(array(' ', "\t", "\r", "\n", '=', '/', '>', '}'));
		if ($after_name === false)
			$this->toss('syntax_name_unterminated');

		$ns_mark = $this->firstPosOf(':');
		if ($ns_mark !== false && $ns_mark < $after_name)
		{
			$ns = $this->eatUntil($ns_mark);
			// Skip the : after the namespace.
			$this->data_pos++;

			if (!Source::validNCName($ns))
				$this->toss('syntax_name_ns_invalid');
		}
		else
			$ns = '';

		$name = $this->eatUntil($after_name);
		if (!Source::validNCName($name))
			$this->toss('syntax_name_invalid');

		$this->eatWhite();
		return array($ns, $name);
	}

	protected function parseAttribute()
	{
		$after_name = $this->firstPosOf('=');
		if ($after_name === false)
			return false;

		list ($ns, $name) = $this->parseName();

		if ($this->data[$this->data_pos] !== '=')
			$this->toss('syntax_attr_value_missing');
		$this->data_pos++;

		$quote_type = $this->data[$this->data_pos];
		if ($this->data[$this->data_pos] !== '\'' && $this->data[$this->data_pos] !== '"')
			$this->toss('syntax_attr_value_not_quoted');
		$this->data_pos++;

		// Look for the same quote mark at the end of the value.
		$end_quote = $this->firstPosOf($quote_type);
		if ($end_quote === false)
			$this->toss('syntax_attr_value_unterminated');

		// Grab the value, and then skip the end quote.
		$this->saveAttribute($ns, $name, $this->eatUntil($end_quote));
		$this->data_pos++;

		$this->eatWhite();
		return true;
	}

	protected function saveAttribute($ns, $name, $value)
	{
		// !!! This sets it for the rest of the document, which is wrong, but it's usually fine.
		if ($ns === 'xmlns')
			$this->source->addNamespace($name, $value);
		elseif ($ns === '')
			$this->attributes[$name] = $value;
		// Namespaced attributes get the full URI for now.  We could do an object if it becomes necessary.
		else
		{
			$nsuri = $this->getNamespace($ns);
			$this->attributes[$nsuri . ':' . $name] = $value;
		}
	}

	protected function eatWhite()
	{
		while ($this->data_pos < $this->data_len)
		{
			$c = ord($this->data[$this->data_pos]);

			// Okay, found whitespace (space, tab, CR, LF, etc.)
			if ($c != 32 && $c != 9 && $c != 10 && $c != 13)
				break;

			$this->data_pos++;
		}
	}

	protected function eatUntil($pos)
	{
		$data = substr($this->data, $this->data_pos, $pos - $this->data_pos);
		$this->data_pos = $pos;

		return $data;
	}

	protected function firstPosOf($find, $offset = 0)
	{
		$least = false;

		// Just look for each and take the lowest.
		$find = (array) $find;
		foreach ($find as $arg)
		{
			$found = strpos($this->data, $arg, $this->data_pos + $offset);
			if ($found !== false && ($least === false || $found < $least))
				$least = $found;
		}

		return $least;
	}

	public function createInject($type, $ns, $name, array $attributes = array())
	{
		$token = clone $this;
		$token->data = '{tpl:auto-token /}';

		$token->type = $type;
		$token->name = $name;
		$token->attributes = $attributes;

		if ($ns === false)
		{
			$token->ns = 'tpl';
			$token->nsuri = Template::TPL_NAMESPACE;
		}
		else
		{
			$token->ns = $ns;
			$token->setNamespace();
		}

		return $token;
	}
}