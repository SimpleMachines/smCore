<?php

namespace smCore\TemplateEngine;

class Expression
{
	protected $data = null;
	protected $data_len = 0;
	protected $data_pos = 0;
	protected $token = null;

	// Our built var/lang/etc. expression string
	protected $expr = '';

	// If it's not raw, we'll htmlspecialchars it
	protected $escape = false;
	protected $filters = array();

	protected static $lang_function = 'lang';
	protected static $filter_function = 'smCore\TemplateEngine\Filters::filter';

	public static function setLangFunction($func)
	{
		self::$lang_function = $func;
	}

	public static function setFilterFunction($func)
	{
		self::$filter_function = $func;
	}

	public function __construct($data, Token $token, $escape = false)
	{
		// Filters can have whitespace before them, so remove it. Makes reading easier when they're not there.
		$this->data = preg_replace('~\s+\|~', '|', $data);
		$this->data_len = mb_strlen($this->data);
		$this->token = $token;
		$this->escape = $escape !== false;
	}

	public function parseInterpolated()
	{
		// An empty string, let's short-circuit this common case.
		if ($this->data_len === 0)
			return '\'\'';

		while ($this->data_pos < $this->data_len)
		{
			if (!empty($this->expr))
				$this->expr .= ' . ';

			switch ($this->data[$this->data_pos])
			{
				case '{':
					$this->expr .= $this->readReference();
					break;

				default:
					$this->expr .= $this->readStringInterpolated();
			}
		}

		$this->validate();

		return $this->getCode();
	}

	public function parseVariable($allow_lang = true)
	{
		$this->eatWhite();

		if ($this->data_len === 0 || $this->data[$this->data_pos] !== '{')
			$this->toss('expression_expected_var');

		$this->expr = $this->readReference($allow_lang);

		$this->eatWhite();

		if ($this->data_pos < $this->data_len)
			$this->toss('expression_expected_var_only');

		$this->validate();

		return $this->getCode();
	}

	public function parseNormal($escape = false)
	{
		// An empty string, let's short-circuit this common case.
		if ($this->data_len === 0)
			$this->toss('expression_empty');

		$this->escape = (boolean) $escape;

		while ($this->data_pos < $this->data_len)
		{
			switch ($this->data[$this->data_pos])
			{
				case '{':
					$this->expr .= $this->readReference();
					break;

				default:
					// !!! Maybe do more here?
					$this->expr .= $this->readRaw();
			}
		}

		$this->validate();

		return $this->getCode();
	}

	public function validate()
	{
		// We'll get a "[] can't be used for reading" fatal error.
		if (preg_match('~\[\s+\]$~', $this->expr))
			$this->toss('expression_empty_brackets');

		// A dead code sandbox prevents this from causing any trouble.
		$attempt = @eval('if(0){return (' . $this->expr . ');}');

		if ($attempt === false)
			$this->toss('expression_validation_error', array($this->expr));
	}

	protected function getCode()
	{
		// The raw and escape filters are special cases.
		if (array_key_exists('raw', $this->filters))
		{
			$this->escape = false;
			unset($this->filters['raw']);
		}
		else if (array_key_exists('escape', $this->filters))
		{
			$this->escape = true;
			unset($this->filters['escape']);
		}

		// Add the filters, if there are any
		if (!empty($this->filters))
		{
			$filters = array();

			foreach ($this->filters as $name => $params)
				$filters[] = '\'' . $name . '\' => array(' . implode(',', $params) . ')';

			$this->expr = self::$filter_function . '(' . $this->expr . ', array(' . implode(',', $filters) . '))';
		}

		if ($this->escape)
			return 'htmlspecialchars(' . $this->expr . ', ENT_COMPAT, "UTF-8")';
		else
			return $this->expr;
	}

	protected function readStringInterpolated()
	{
		$pos = $this->firstPosOf('{');

		if ($pos === false)
			$pos = $this->data_len;

		// Should never happen, unless we were called wrong.
		if ($pos === $this->data_pos)
			$this->toss('expression_unknown_error');

		return $this->readString($pos);
	}

	protected function readReference($allow_lang = true)
	{
		// Expect to be on a {.
		$this->data_pos++;

		$pos = $this->firstPosOf('}');

		if ($pos === false)
			$this->toss('expression_braces_unmatched');

		$data = '';

		$c = $this->data[$this->data_pos];

		if ($c === '$')
		{
			$data = $this->readVarRef();

			if ($this->data_pos >= $this->data_len || $this->data[$this->data_pos] !== '}')
			{
				if ($this->data[$this->data_pos] === ']')
					$this->toss('expression_brackets_unmatched');
				else
					$this->toss('expression_unknown_error');
			}
		}
		else if ($c === '#')
		{
			if ($allow_lang)
			{
				$data = $this->readLangRef();

				if ($this->data_pos >= $this->data_len || $this->data[$this->data_pos] !== '}')
					$this->toss('expression_unknown_error');
			}
			else
				$this->toss('expression_expected_ref_nolang');
		}
		else
		{
			// This could be a static.  If it is, we have a :: later on.
			$next = $this->firstPosOf('::');

			if ($next !== false && $next < $pos)
			{
				$data = $this->eatUntil($next) . $this->eatUntil($next + 2) . $this->readVarRef();

				if ($this->data_pos >= $this->data_len || $this->data[$this->data_pos] !== '}')
				{
					if ($this->data[$this->data_pos] === ']')
						$this->toss('expression_brackets_unmatched');
					else
						$this->toss('expression_unknown_error');
				}
			}
			else
			{
				if ($allow_lang)
					$this->toss('expression_expected_ref');
				else
					$this->toss('expression_expected_ref_nolang');
			}
		}

		// Skip over the }.
		$this->data_pos++;

		return $data;
	}

	protected function readVarRef()
	{
		/*	It looks like this: {$xyz.abc[$mno][nilla].$rpg |filter |filter($param)}
			Which means:
				x.y.z = x [ y ] [ z ]
				x[y.z] = x [ y [ z ] ] 
				x[y][z] = x [ y ] [ z ]
				x[y[z]] = x [ y [ z ] ]
		
			When we hit a ., the next item is surrounded by brackets.
			When we hit a [, the next item has a [ before it.
			When we hit a ], there is no item, but just a ].
			When we hit a |, we're looking at a filter.
		*/

		$built = '';

		$brackets = 0;

		while ($this->data_pos < $this->data_len)
		{
			$next = $this->firstPosOf(array('[', '.', ']', '->', '}', '|'), 1);

			if ($next === false)
				$next = $this->data_len;

			$c = $this->data[$this->data_pos++];

			if ($c === '$')
			{
				$name = $this->eatUntil($next);

				if ($name === '')
					$this->toss('expression_var_name_empty');

				$built .= '$' . self::makeVarName($name);
			}
			else if ($c === '.')
			{
				$built .= '[';
				$built .= $this->readVarPart($next, true);
				$built .= ']';
			}
			else if ($c === '[')
			{
				$built .= '[';
				$this->eatWhite();
				$built .= $this->readVarPart($next, false);
				$this->eatWhite();

				$brackets++;
			}
			else if ($c === ']')
			{
				// Ah, hit the end, jump out. Must be a nested one.
				if ($brackets <= 0)
				{
					$this->data_pos--;
					break;
				}

				$built .= ']';

				$brackets--;
			}
			else if ($c === '-')
			{
				// When we hit a ->, we increase the data pointer, then find the property.
				$built .= '->';
				$this->data_pos++;
				$built .= $this->eatUntil($next);
			}
			else if ($c === '}')
			{
				// All done - but don't skip it, our caller doesn't expect that.
				$this->data_pos--;
				break;
			}
			else if ($c === '|')
			{
				$this->readFilter();
			}
			else
			{
				// A constant, like a class constant: {Class::CONST}.
				// We want to grab the "C", so we take a step back and eat.
				$this->data_pos--;
				$built .= $this->eatUntil($next);
			}
		}

		if ($brackets != 0)
			$this->toss('expression_brackets_unmatched');

		return $built;
	}

	protected function readLangRef()
	{
		/*	It looks like this: {#xyz.abc[$mno][nilla].$rpg |filter |filter($param)}
			Which means:
				x.y.z = x [ y ] [ z ]
				x[y.z] = x [ y [ z ] ] 
				x[y][z] = x [ y ] [ z ]
				x[y[z]] = x [ y [ z ] ]
		
			When we hit a ., the next item is surrounded by brackets.
			When we hit a [, the next item has a [ before it.
			When we hit a ], there is no item, but just a ].
			When we hit a |, we're looking at a filter.
		*/

		$key = array();
		$params = array();

		$brackets = 0;

		while ($this->data_pos < $this->data_len)
		{
			$next = $this->firstPosOf(array('[', '.', ']', '}', '|', ':'), 1);

			if ($next === false)
				$next = $this->data_len;

			$c = $this->data[$this->data_pos++];

			if ($c === '#')
			{
				$name = $this->eatUntil($next);

				if ($name === '')
					$this->toss('expression_lang_name_empty');

				$key[] = '\'' . $name . '\'';
			}
			else if ($c === '.')
			{
				$key[] = $this->readVarPart($next, false);
			}
			else if ($c === '[')
			{
				$key[] = $this->readVarPart($next, false);

				$brackets++;
			}
			else if ($c === ']')
			{
				// Ah, hit the end, jump out.  Must be a nested one.
				if ($brackets <= 0)
				{
					$this->data_pos--;
					break;
				}

				$brackets--;
			}
			else if ($c === ':')
			{
				// We're going to be greedy, now that we're pretty much starting a whole new expression.
				$params[] = $this->readVarPart($next, false, true); 
			}
			else if ($c === '}')
			{
				// All done - but don't skip it, our caller doesn't expect that.
				$this->data_pos--;
				break;
			}
			else if ($c === '|')
			{
				$this->readFilter();
			}
		}

		if ($brackets != 0)
			$this->toss('expression_brackets_unmatched');

		// Assemble and return
		$expr = self::$lang_function . '(array(' . implode(',', $key) . ')';

		if (!empty($params))
			$expr .= ', array(' . implode(',', $params) . ')';

		return $expr . ')';
	}

	protected function readFilter()
	{
		$name = '';
		$params = array();

		// Rewind so that we capture the name
		$this->data_pos--;

		while ($this->data_pos < $this->data_len)
		{
			$next = $this->firstPosOf(array('(', ')', '|', '}', ','), 1);

			if ($next === false)
				$next = $this->data_len;

			$c = $this->data[$this->data_pos++];

			if ($c === '|')
			{
				$name = $this->eatUntil($next);

				if ($name === '')
					$this->toss('expression_filter_name_empty');
			}
			else if ($c === '(')
			{
				$params[] = $this->readVarPart($next, false, true);
			}
			else if ($c === ')')
			{
				break;
			}
			else if ($c === '}')
			{
				// All done - but don't skip it, our caller doesn't expect that.
				$this->data_pos--;
				break;
			}
			else if ($c === ',')
			{
				$this->eatWhite();

				// We're going to be greedy, now that we're pretty much starting a whole new expression.
				$params[] = $this->readVarPart($next, false, true);
			}
		}

		$this->filters[$name] = $params;
	}

	protected function readVarPart($end, $require = false, $greedy = false)
	{
		// If we're being greedy, don't stop at indexes.
		if ($greedy)
			$end = $this->firstPosOf(array(',', '|', ')', '}', ':'), 1);

		$c = $this->data[$this->data_pos];

		// If a curly bracket isn't provided, get smart.
		if ($c === '$' || $c === '#')
		{
			$expr = mb_substr($this->data, $this->data_pos, $end - $this->data_pos);
			$this->data_pos += mb_strlen($expr);

			return self::variable('{' . $expr . '}', $this->token);

		}
		else if ($c === '{')
		{
			// Create a whole new expression, and make sure we grab everything.
			return self::variable($this->readInnerReference(), $this->token);
		}
		else
		{
			if ($require && $this->data_pos == $end)
				$this->toss('expression_incomplete');

			return $this->readString($end);
		}
	}

	protected function readInnerReference()
	{
		$start = $this->data_pos;
		$brackets = 0;

		while ($this->data_pos < $this->data_len)
		{
			if ($this->data[$this->data_pos] === '}')
				$brackets--;
			else if ($this->data[$this->data_pos] === '{')
				$brackets++;

			$this->data_pos++;

			if ($brackets === 0)
				break;
		}	

		if ($brackets === 0)
			return mb_substr($this->data, $start, $this->data_pos - $start);

		$this->toss('inner_token_unmatched_braces');
	}

	protected function readString($end)
	{
		$value = $this->eatUntil($end);

		// Short circuit this one
		if (empty($value))
			return '\'\'';

		if ($value[0] === '"' || $value[0] === '\'')
		{
			// If it's already in quotation marks, take them out
			if ($value[0] === mb_substr($value, -1))
				$value = mb_substr($value, 1, -1);

			// Did we split inside a string literal? Try to find the rest
			else if ($value[0] !== mb_substr($value, -1) || mb_strlen($value) === 1)
			{
				$next = $this->firstPosOf(array($value[0]));
				$value = mb_substr($value, 1) . $this->eatUntil($next);

				// Skip over the ending quotation mark.
				$this->data_pos++;
			}
		}

		return '\'' . addcslashes($value, '\\\'') . '\'';
	}

	protected function readRaw()
	{
		$pos = $this->firstPosOf('{');
		if ($pos === false)
			$pos = $this->data_len;

		// Should never happen, unless we were called wrong?
		if ($pos === $this->data_pos)
			$this->toss('expression_unknown_error');

		return $this->eatUntil($pos);
	}

	protected function toss($error, $params = array())
	{
		$this->token->toss('expression_invalid_meta', $this->data, Exception::format($error, $params));
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
		$data = mb_substr($this->data, $this->data_pos, $pos - $this->data_pos);
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

	public static function variable($string, Token $token)
	{
		$expr = new self($string, $token);
		return $expr->parseVariable();
	}

	public static function variableNotLang($string, Token $token)
	{
		$expr = new self($string, $token);
		return $expr->parseVariable(false);
	}

	public static function stringWithVars($string, Token $token)
	{
		$expr = new self($string, $token);
		return $expr->parseInterpolated();
	}

	public static function normal($string, Token $token, $escape = false)
	{
		return self::boolean($string, $token, $escape);
	}

	public static function boolean($string, Token $token, $escape = false)
	{
		$expr = new self($string, $token);
		return $expr->parseNormal($escape);
	}

	// Splits the $string by the $delimiter, and tries it as a $type (one of the functions above)
	public static function splitExpressions($string, $type, $delimiter, $token)
	{
		if (empty($string))
			return array();

		// We're going to split by the delimiter, and then if something is off, we'll merge some things back together
		$parts = explode($delimiter, $string);

		// Our ready-to-go expressions
		$expressions = array();

		// @todo: clean this up

		$broken = false;
		$index = 0;

		while ($index < count($parts))
		{
			if ($broken)
			{
				// Put the delimiter back and append the next part
				$built .= $delimiter . $parts[$index++];
			}
			else
				$built = $parts[$index++];

			// If it doesn't work, merge it with the next one and try again
			try
			{
				$expr = self::$type(trim($built), $token);

				// Exception would be thrown above, so if we get here we're good to go.
				$expressions[] = $expr;

				$broken = false;
				$built = '';
			}
			catch (Exception $e)
			{
				// Do nothing with the exception, we'll try again.
				$broken = true;
			}
		}

		// If we were still trying to build something at the end, something's wrong
		if ($broken)
		{
			die('Error');
		}
	}

	public static function makeVarName($name)
	{
		return preg_replace('~[^a-zA-Z0-9_]~', '_', $name);
	}

	public static function makeTemplateName($nsuri, $name)
	{
		return 'tpl_' . md5($nsuri) . '_' . self::makeVarName($name);
	}
}