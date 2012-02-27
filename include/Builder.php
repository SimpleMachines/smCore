<?php

namespace smCore\TemplateEngine;

class Builder
{
	protected $debugging = true;
	protected $data = null;
	protected $data_close = false;
	protected $last_file = null;
	protected $last_line = 1;
	protected $prebuilder = null;
	protected $last_template = null;
	protected $has_emitted = false;
	protected $disable_emit = false;
	protected $emit_output = array();
	protected $common_vars = array();
	protected $listeners = array();

	public function __construct()
	{
	}

	public function __destruct()
	{
		$this->abort();
	}

	public function setDebugging($enabled)
	{
		$this->debugging = (boolean) $enabled;
	}

	public function setCommonVars(array $names)
	{
		$this->common_vars = $names;
	}

	public function setCacheFile($cache_file)
	{
		if (is_resource($cache_file))
			$this->data = $cache_file;
		else
		{
			$this->data = @fopen($cache_file, 'wt');
			if (!$this->data)
				throw new Exception('builder_cannot_open', $cache_file);

			$this->data_close = true;
		}

		$this->emitCode('<?php ');
	}

	public function abort()
	{
		// Release the file so it isn't left open until the request end.
		if ($this->data !== null && $this->data_close)
			@fclose($this->data);
	}

	public function setPrebuilder($prebuilder)
	{
		$this->prebuilder = $prebuilder;
	}

	// callback(Builder $builder, $type, array $attributes, Token $token)
	public function listenEmit($nsuri, $name, $callback)
	{
		$this->listeners[$nsuri][$name][] = $callback;
	}

	protected function fireEmit(Token $token)
	{
		// This actually fires a whole mess of events, but easier to hook into.
		// In this case, it's cached, so it's fairly cheap.
		$this->fireActualEmit($token->nsuri, $token->name, $token);
		$this->fireActualEmit('*', $token->name, $token);
		$this->fireActualEmit($token->nsuri, '*', $token);
		$this->fireActualEmit('*', '*', $token);
	}

	protected function fireActualEmit($nsuri, $name, Token $token)
	{
		// If there are no listeners, nothing to do.
		if (empty($this->listeners[$nsuri]) || empty($this->listeners[$nsuri][$name]))
			return;

		$listeners = $this->listeners[$nsuri][$name];
		foreach ($listeners as $callback)
		{
			// We don't use call_user_func because we want to allow by reference passing.
			if (is_string($callback))
				$result = $callback($this, $token->type, $token->attributes, $token);
			elseif (!is_string($callback[0]))
				$result = $callback[0]->$callback[1]($this, $token->type, $token->attributes, $token);
			// !!! Breaks PHP 5.1 and 5.2 to call these directly.
			else
				$result = call_user_func($callback, $this, $token->type, $token->attributes, $token);

			if ($result === false)
				break;
		}
	}

	public function setupParser(Parser $parser)
	{
		if ($this->prebuilder !== null)
			$this->prebuilder->setupOverlayParser($parser);

		$parser->listen('parsedContent', array($this, 'parsedContent'));
		$parser->listen('parsedElement', array($this, 'parsedElement'));
	}

	public function parsedContent(Token $token, Parser $parser)
	{
		$this->emitOutputString($token->data, $token);
	}

	public function parsedElement(Token $token, Parser $parser)
	{
		if ($token->nsuri === Template::TPL_NAMESPACE)
		{
			$this->has_emitted = false;

			// Everything else is handled via a hook.
			if ($token->name === 'container')
				$this->handleTagContainer($token);
			elseif ($token->name === 'template')
				$this->handleTagTemplate($token);
			elseif ($token->name === 'content')
				$this->handleTagContent($token);
			elseif ($token->name === 'alter')
				$this->handleTagAlter($token);

			$this->fireEmit($token);

			// If there was no emitted code, it's probably an error.
			if ($this->has_emitted === false && $this->debugging)
				$token->toss('unknown_tpl_element', $token->name);
		}
		else
		{
			$this->handleTagCall($token);

			$this->fireEmit($token);
		}
	}

	protected function handleTagContainer(Token $token)
	{
		// A container is just a thing to set namespaces, it does nothing.
		// However, we have to omit something or it will think it's unrecognized.
		$this->emitCode('');
	}

	protected function handleTagAlter(Token $token)
	{
		// This was already understood by the prebuilder.
		// Let's emit nothing so it knows it was recognized.
		$this->emitCode('');

		// We never emit alters, just the alterations they make.
		if ($token->type === 'tag-start')
			$this->disable_emit = true;
		elseif ($token->type === 'tag-end')
			$this->disable_emit = false;
	}

	protected function handleTagTemplate(Token $token)
	{
		// Assumption: can't be tag-empty (verified by parser.)
		if ($token->type === 'tag-start')
		{
			$this->last_template = $this->prebuilder->getTemplateForBuild($token);

			// Template was already built, so don't emit it again.
			if ($this->last_template['should_emit'] === false)
				$this->disable_emit = true;

			$this->emitTemplateStart($this->last_template['name'] . '_above', $token);
		}
		elseif ($token->type === 'tag-end')
		{
			// If we haven't output the below, output it now.
			if ($this->last_template['stage'] == 1)
			{
				$this->emitTemplateEnd(false, $token);
				$this->emitTemplateStart($this->last_template['name'] . '_below', $token);

				$this->last_template['stage'] = 2;
			}

			$this->emitTemplateEnd(true, $token);

			// Even if it wasn't disabled before, enable it until the next template.
			$this->disable_emit = false;
			$this->last_template = null;
		}
	}

	protected function handleTagContent(Token $token)
	{
		// Already hit one, can't have two.
		if ($this->last_template['stage'] == 2)
			$token->toss('tpl_content_twice');

		// Assumption: must be tag-empty (verified by parser.)
		$this->emitTemplateEnd(false, $token);
		$this->emitTemplateStart($this->last_template['name'] . '_below', $token);

		// Mark that we've output the above AND below.
		$this->last_template['stage'] = 2;
	}

	protected function handleTagCall(Token $token)
	{
		$template = $this->prebuilder->getTemplateForCall($token);
		$name = addcslashes($template['name'], '\\\'');

		if (isset($token->attributes[Template::TPL_NAMESPACE . ':inherit']))
			$inherit = preg_split('~[ \t\r\n]+~', $token->attributes[Template::TPL_NAMESPACE . ':inherit']);
		else
			$inherit = array();

		$args_escaped = array();
		$arg_names = array_merge($inherit, $this->common_vars);

		// When calling, we pass along the common vars.
		foreach ($this->common_vars as $var_name)
		{
			$k = '\'' . addcslashes(Expression::makeVarName($var_name), '\\\'') . '\'';
			$args_escaped[] = $k . ' => ' . $this->parseExpression('variable', '{$' . $var_name . '}', $token);
		}

		// Pass any attributes along.
		foreach ($token->attributes as $k => $v)
		{
			// Don't send this one.
			if ($k === Template::TPL_NAMESPACE . ':inherit')
				continue;

			$arg_names[] = Expression::makeVarName($k);

			$k = '\'' . addcslashes(Expression::makeVarName($k), '\\\'') . '\'';

			// The string passed to templates will get double-escaped unless we unescape it here.
			// We don't do this for tpl: things, though, just for calls.
			$v = html_entity_decode($v);

			$args_escaped[] = $k . ' => ' . $this->parseExpression('stringWithVars', $v, $token);
		}

		// This checks the requires parameter to make sure they passed everything necessary.
		$required = array_diff($template['requires'], $arg_names, $inherit);
		// If they used inherit="*", we can't really tell...
		if (!empty($required) && !in_array('*', $inherit))
			$token->toss('template_missing_required', $token->prettyName(), implode(', ', $required));

		if ($token->type == 'tag-start' || $token->type == 'tag-empty')
			$this->emitTagCall($name . '_above', $args_escaped, $inherit, true, $template, $token);
		if ($token->type == 'tag-end' || $token->type == 'tag-empty')
			$this->emitTagCall($name . '_below', $args_escaped, $inherit, false, $template, $token);
	}

	protected function emitTagCall($escaped_name, array $args_escaped, array $args_inherit, $first, $template, Token $token)
	{
		// Do we know for sure that it is defined?  If so, we can skip an if.
		if (!$template['defined'])
			$this->emitCode('if (function_exists(\'' . $escaped_name . '\')) {', $token);

		if ($first)
		{
			$this->emitCode('global $__toxg_argstack; if (!isset($__toxg_argstack)) $__toxg_argstack = array();', $token);

			if (in_array('*', $args_inherit))
				$this->emitCode('$__toxg_args = array(' . implode(', ', $args_escaped) . ') + $__toxg_params;', $token);
			elseif (!empty($args_inherit))
				$this->emitCode('$__toxg_args = array(' . implode(', ', $args_escaped) . ') + array_intersect_key($__toxg_params, array_flip(' . var_export($args_inherit, true) . '));', $token);
			else
				$this->emitCode('$__toxg_args = array(' . implode(', ', $args_escaped) . ');', $token);

			$this->emitCode('$__toxg_argstack[] = &$__toxg_args;', $token);
		}
		else
			$this->emitCode('global $__toxg_argstack; $__toxg_args = array_pop($__toxg_argstack);', $token);

		// Better to use a de-refenced call than call_user_func/_array, because of reference issue.
		$this->emitCode('$__toxg_func = \'' . $escaped_name . '\'; $__toxg_func($__toxg_args);', $token);

		if (!$template['defined'])
			$this->emitCode('}', $token);
	}

	protected function emitTemplateStart($escaped_name, Token $token)
	{
		$this->emitCode('function ' . $escaped_name . '(&$__toxg_params = array()) {');
		$this->emitCode('extract($__toxg_params, EXTR_SKIP);', $token);

		if ($this->debugging)
		{
			$this->emitCode('$__toxg_error_handler = new ' . $this->getErrorClassName() . '();');
			$this->emitDebugPos($token, 'code', true);
		}
	}

	protected function emitTemplateEnd($last, $token)
	{
		// This updates the parameters for the _below function.
		if (!$last)
		{
			$omit = array('\'__toxg_args\'', '\'__toxg_argstack\'', '\'__toxg_stack\'', '\'__toxg_params\'', '\'__toxg_func\'', '\'__toxg_error_handler\'');
			$this->emitCode('$__toxg_params = compact(array_diff(array_keys(get_defined_vars()), array(' . implode(', ', $omit) . ')));', $token);
		}

		$this->emitCode('}', $token);
	}

	public function finalize()
	{
		// We embed usage data for preloading/efficiency purposes.
		$usage = $this->prebuilder->getTemplateUsage();
		$this->emitCode($this->getUsageClassName() . '::markUsage(' . var_export($usage, true) . ');');

		// !!! Emit something here for hooks?

		// Just end the file now.
		$this->emitCode(' ?>');
		if ($this->data_close)
			fclose($this->data);
		$this->data = null;

		if ($this->last_template !== null)
			throw new Exception('builder_unclosed_template');
	}

	public function emitCode($code, Token $token = null)
	{
		$this->has_emitted = true;
		if ($this->disable_emit)
			return;

		$this->flushOutputCode();

		if ($this->debugging && $token !== null)
			$this->emitDebugPos($token);

		$this->emitCodeInternal($code);
	}

	public function emitOutputString($data, Token $token = null)
	{
		$this->has_emitted = true;
		if ($this->disable_emit)
			return;

		$this->emit_output[] = array(
			'type' => 'string',
			'data' => $data,
			'token' => $token,
		);
	}

	public function emitOutputParam($expr, Token $token = null)
	{
		$this->has_emitted = true;
		if ($this->disable_emit)
			return;

		$this->emit_output[] = array(
			'type' => 'param',
			'data' => $expr,
			'token' => $token,
		);
	}

	protected function flushOutputCode()
	{
		if (empty($this->emit_output))
			return;

		// We're going to enter and exit strings.
		$in_string = false;
		$first = true;

		foreach ($this->emit_output as $node)
		{
			if ($node['type'] === 'string')
			{
				// If we're not inside a string already, start one with debug info.
				if (!$in_string)
				{
					if ($node['token'] !== null && $this->emitDebugPos($node['token'], 'echo'))
						$first = true;
					$this->emitCodeInternal(($first ? 'echo ' : ', ') . '\'');
					$in_string = true;
				}

				$this->emitCodeInternal(addcslashes($node['data'], "'\\"));
			}
			elseif ($node['type'] === 'param')
			{
				if ($in_string)
				{
					$this->emitCodeInternal('\'');
					$in_string = false;
				}

				// Just in case the position has changed for some reason (overlay, etc.)
				if ($node['token'] !== null && $this->emitDebugPos($node['token'], 'echo'))
					$first = true;

				$this->emitCodeInternal(($first ? 'echo ' : ', ') . $node['data']);
			}

			$first = false;
		}

		if ($in_string)
			$this->emitCodeInternal('\'');
		$this->emitCodeInternal(';');

		$this->emit_output = array();
	}

	protected function emitCodeInternal($code)
	{
		// Don't output any \r's, we use 't' mode, so those are automatic.
		// !!!SLOW Can we remove this str_replace?  Just need to test line numbers matching on mac/linux/windows with several line ending types.
		$this->fwrite(str_replace("\r", '', $code));
		$this->last_line += substr_count($code, "\n");
	}

	protected function emitDebugPos(Token $token, $type = 'code', $force = false)
	{
		// Okay, maybe we don't need to bulk up the template.  Let's see how we can get out of updating the pos.

		// If the file is the same, we have a chance.
		if ($token->file === $this->last_file && !$force)
		{
			// If the line is the same as it should be, we're good.
			if ($token->line == $this->last_line)
				return false;
			// If we just need a higher line number, then just print some newlines (cheaper for PHP to cache.)
			elseif ($token->line > $this->last_line)
			{
				$this->emitCodeInternal(str_repeat("\n", $token->line - $this->last_line));
				return false;
			}

			// Okay, this means the line number was lower (template?) so let's go.
		}

		// In case this is actually a database "filename" or something, don't wipe it out.
		$file = $token->file;
		if (realpath($file) != false)
			$file = realpath($file);

		if ($type === 'echo')
			$this->fwrite(';');
		// This triggers the error system to remap the caller's file/line with the specified.
		if (!$force)
			$this->fwrite("\n");
		$this->fwrite($this->getErrorClassName() . '::remap(\'' . addcslashes($file, '\\\'') . '\', ' . (int) $token->line . ');');

		$this->last_file = $token->file;
		$this->last_line = $token->line;
		return true;
	}

	protected function fwrite($string)
	{
		if ($string === '')
			return;

		if (@fwrite($this->data, $string) === false)
			throw new Exception('builder_cannot_write');
	}

	public function parseExpression($type, $expression, Token $token, $escape = false)
	{
		return Expression::$type($expression, $token, $escape);
	}

	protected function getErrorClassName()
	{
		return 'smCore\TemplateEngine\Errors';
	}

	protected function getUsageClassName()
	{
		return 'smCore\TemplateEngine\Template';
	}
}