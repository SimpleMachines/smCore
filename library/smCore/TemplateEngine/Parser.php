<?php

namespace smCore\TemplateEngine;

class Parser
{
	protected $sources = array();
	protected $primary = null;
	protected $listeners = array();
	protected $inside_cdata = false;
	protected $tree = array();
	protected $templates = array();
	protected $state = 'outside';
	protected $doctype = 'xhtml';

	public function __construct($file)
	{
		if ($file instanceof Source)
		{
			$this->primary = $file;
			$this->primary->initialize();
		}
		else
			$this->primary = new SourceFile($file);
	}

	public function setNamespaces(array $uris)
	{
		$this->primary->setNamespaces($uris);
	}

	public function listen($type, $callback)
	{
		$this->listeners[$type][] = $callback;
	}

	protected function fire($type, Token $token)
	{
		if (empty($this->listeners[$type]))
			return;

		foreach ($this->listeners[$type] as $callback)
		{
			$result = call_user_func($callback, $token, $this);
			if ($result === false)
				break;
		}
	}

	public function insertSource($source, $defer = false)
	{
		if (!($source instanceof Source) && !($source instanceof Token))
			throw new Exception('parsing_invalid_source_type');

		array_unshift($this->sources, $source);

		// To defer means we wait, it goes up the chain.
		if ($defer)
			return;

		if ($source instanceof Source)
		{
			while (!$source->isDataEOF())
				$this->parseNextSource();
		}
		// Just need to process the token once.
		else
			$this->parseNextSource();
	}

	public function parse()
	{
		$this->insertSource($this->primary, true);

		while (!empty($this->sources))
			$this->parseNextSource();

		$this->verifyClosed();
	}

	protected function verifyClosed()
	{
		if (!empty($this->tree))
		{
			$token = array_pop($this->tree);
			throw new Exception('parsing_element_incomplete', $token->prettyName(), $token->file, $token->line);
		}
	}

	protected function debugToken(Token $token)
	{
		// !!! All tokens go through here, which can help debugging overlays.
		// !!! I was just echoing them for testing, maybe build in some sort of hook or file output?
		//echo $token->data;
	}

	protected function parseNextSource()
	{
		if (empty($this->sources))
			throw new Exception('parsing_internal_error');

		$source = $this->sources[0];

		// If it was actually an Token, pull it out right away.
		if ($source instanceof Token)
			$token = array_shift($this->sources);
		else
			$token = $source->readToken();

		// Gah, we hit the end of the stream... next source.
		if ($token === false)
		{
			array_shift($this->sources);
			return;
		}

		$this->debugToken($token);
		$this->parseNextToken($token);
	}

	protected function parseNextToken(Token $token)
	{
		$this->parseFixEmptyCall($token);

		switch ($token->type)
		{
		case 'content':
			$this->parseContent($token);
			break;

		case 'var-ref':
		case 'lang-ref':
		case 'output-ref':
			$this->parseRef($token);
			break;

		case 'cdata-start':
			if ($this->doctype === 'xhtml')
				$this->parseCDATA($token, true);
			else
				$this->parseContent($token);
			break;

		case 'cdata-end':
			if ($this->doctype === 'xhtml')
				$this->parseCDATA($token, false);
			else
				$this->parseContent($token);
			break;

		case 'comment-start':
		case 'comment-end':
		case 'comment':
			$this->parseComment($token);
			break;

		case 'tag-start':
		case 'tag-empty':
			$this->parseTag($token);
			break;

		case 'tag-end':
			$this->parseTagEnd($token);
			break;
		}
	}

	protected function parseFixEmptyCall(Token &$token)
	{
		if ($token->type === 'tag-empty' && $token->nsuri !== Template::TPL_NAMESPACE)
		{
			$start = $token->createInject('tag-end', $token->ns, $token->name, $token->attributes);
			$this->insertSource($start, true);

			// We clone it incase it's owned by an overlay.
			$token = clone $token;
			$token->type = 'tag-start';
		}
	}

	protected function parseContent(Token $token)
	{
		// Shouldn't have content outside a template.
		if ($this->state === 'outside')
		{
			if (trim($token->data) !== '')
				$token->toss('parsing_content_outside_template');
		}
		else
		{
			// In HTML mode, we need to check to go in and out of CDATA.
			// Note: we depend on the tokenizer giving us each HTML element in a separate token.
			// Otherwise, we'd have to check for <script> and </script>.
			if ($this->doctype === 'html')
			{
				// !!! Avoid preg?
				if ($this->inside_cdata === false)
				{
					if (preg_match('~\<(script|style|textarea|title)[\t\r\n \>/]~', $token->data, $match) != 0)
						$this->inside_cdata = $match[1];
				}
				elseif ($this->inside_cdata !== false)
				{
					if (preg_match('~\</(' . preg_quote($this->inside_cdata, '~') . ')[\t\r\n \>/]~', $token->data, $match) != 0)
						$this->inside_cdata = false;
				}
			}

			$this->fire('parsedContent', $token);
		}
	}

	protected function parseRef(Token $token)
	{
		if ($token->type == 'output-ref')
			$token->data = substr($token->data, 1, strlen($token->data) - 2);

		// Make the tag look like a normal tag.
		$token->type = 'tag-empty';
		$token->name = 'output';
		$token->ns = 'tpl';
		$token->nsuri = Template::TPL_NAMESPACE;
		$token->attributes['value'] = $token->data;
		$token->attributes['escape'] = $this->inside_cdata ? 'false' : 'true';

		$this->parseTag($token);
	}

	protected function parseCDATA(Token $token, $open)
	{
		$this->inside_cdata = $open;

		// Pass it through as if content (still want it outputted.)
		$this->fire('parsedContent', $token);
	}

	protected function parseComment(Token $token)
	{
		// Do nothing.
	}

	protected function parseTag(Token $token)
	{
		// For a couple of these, we do special stuff.
		if ($token->nsuri == Template::TPL_NAMESPACE)
		{
			// We only have a couple of built in constructs.
			if ($token->name === 'container')
				$this->handleTagContainer($token);
			elseif ($token->name === 'template')
				$this->handleTagTemplate($token);
			elseif ($token->name === 'content')
				$this->handleTagContent($token);
			elseif ($token->name === 'output')
				$this->handleTagOutput($token);
			elseif ($token->name === 'alter')
				$this->handleTagAlter($token);
		}
		// Before we fire the event, save the template vars (before alters insert data.)
		else
			$this->handleTagCall($token, 'before');

		if ($token->type === 'tag-start')
			array_push($this->tree, $token);

		$this->fire('parsedElement', $token);

		// After we fire the event, for empty tags, we cleanup.
		if ($token->nsuri !== Template::TPL_NAMESPACE)
			$this->handleTagCall($token, 'after');
	}

	protected function parseTagEnd(Token $token)
	{
		if (empty($this->tree))
			$token->toss('parsing_tag_already_closed', $token->prettyName());

		$close_token = array_pop($this->tree);

		// Darn, it's not the same one.
		if ($close_token->nsuri != $token->nsuri || $close_token->name !== $token->name)
			$this->wrongTagEnd($token, $close_token);

		if ($token->nsuri !== Template::TPL_NAMESPACE)
			$this->handleTagCall($token, 'before');

		// This makes it easier, since they're on the same element after all.
		$token->attributes = $close_token->attributes;
		$this->fire('parsedElement', $token);

		// We might be exiting a template.  These can't be nested.
		if ($token->nsuri == Template::TPL_NAMESPACE)
		{
			if ($token->name === 'template')
				$this->handleTagTemplateEnd($token);
			elseif ($token->name === 'alter')
				$this->handleTagAlterEnd($token);
		}

		// After we fire the event, we'll cleanup the call variables.
		if ($token->nsuri !== Template::TPL_NAMESPACE)
			$this->handleTagCall($token, 'after');
	}

	protected function wrongTagEnd(Token $token, Token $expected)
	{
		// Special case this error since it's sorta common.
		if ($expected->nsuri === Template::TPL_NAMESPACE && $expected->name === 'else')
			$expected->toss('generic_tpl_must_be_empty', $expected->prettyName());
		else
			$token->toss('parsing_tag_end_unmatched', $token->prettyName(), $expected->prettyName(), $expected->file, $expected->line);
	}

	protected function handleTagContainer(Token $token)
	{
		if (isset($token->attributes['doctype']))
		{
			if ($token->attributes['doctype'] === 'html' || $token->attributes['doctype'] === 'xhtml')
				$this->doctype = $token->attributes['doctype'];
			else
				$token->toss('tpl_container_invalid_doctype');
		}
	}

	protected function handleTagTemplate(Token $token)
	{
		if ($token->type === 'tag-empty')
			$token->toss('tpl_template_must_be_not_empty');
		if (empty($token->attributes['name']))
			$token->toss('tpl_template_missing_name');

		if (strpos($token->attributes['name'], ':') === false)
			$token->toss('tpl_template_name_without_ns', $token->attributes['name']);

		// Figure out the namespace and validate it.
		list ($ns, $name) = explode(':', $token->attributes['name'], 2);

		if (empty($ns) || empty($name))
			$token->toss('generic_tpl_no_ns_or_name');

		$nsuri = $token->getNamespace($ns);
		if ($nsuri === false)
			$token->toss('tpl_template_name_unknown_ns', $ns);
		if (strlen($name) === 0)
			$token->toss('tpl_template_name_empty_name', $token->attributes['name']);

		// This is the fully-qualified name, which can/should not be duplicated.
		$fqname = $nsuri . ':' . $name;
		if (isset($this->templates[$fqname]))
			$token->toss('tpl_template_duplicate_name', $ns . ':' . $name);

		$this->templates[$fqname] = true;
		$this->state = 'template';
	}

	protected function handleTagTemplateEnd(Token $token)
	{
		$this->state = 'outside';

		// After a template, we generate a fake template for overlays to apply to.
		// Note that (in case they don't actually exist) templates don't have overlays
		// applied inside them, but instead upon call.
		if (strpos($token->attributes['name'], '--toxg-direct') === false)
		{
			list ($ns, $name) = explode(':', $token->attributes['name'], 2);

			$template_attributes = $token->attributes;
			$template_attributes['name'] .= '--toxg-direct';

			$call_attributes = array(
				Template::TPL_NAMESPACE . ':inherit' => '*',
			);

			$tokens = array(
				// <tpl:template name="ns:name--toxg-direct">
				$token->createInject('tag-start', false, 'template', $template_attributes),
				// <ns:name tpl:inherit="*">
				$token->createInject('tag-start', $ns, $name, $call_attributes),
				// <tpl:content />
				$token->createInject('tag-empty', false, 'content'),
				// </ns:name>
				$token->createInject('tag-end', $ns, $name, $call_attributes),
				// </tpl:template>
				$token->createInject('tag-end', false, 'template', $template_attributes),
			);

			// Need to reverse them because they are going to each go in first place.
			$tokens = array_reverse($tokens);
			foreach ($tokens as $new_token)
				$this->insertSource($new_token, true);
		}
	}

	protected function handleTagCall(Token $token, $pos)
	{
		// If no attributes, we don't need to push/pop, save some cycles.
		if (empty($token->attributes))
			return;
		// No reason if we're using tpl:inherit="*".
		if (count($token->attributes) === 1 && isset($token->attributes[Template::TPL_NAMESPACE . ':inherit']) && $token->attributes[Template::TPL_NAMESPACE . ':inherit'] === '*')
			return;

		// Overlays and content should be able to reference the attributes in the call.
		// Example: <some:example abc="123">{$abc}</some>
		// Would be easier if content/alters applied to templates, but then they must be defined.
		if ($token->type === 'tag-start' && $pos === 'before')
			$type = 'template-push';
		elseif ($token->type === 'tag-end' && $pos === 'after')
			$type = 'template-pop';
		// Since we convert empty calls to start/end, we don't need to worry about tag-empty.
		else
			return;

		// We want it to have the same file/line info, same attributes, etc.
		$new_token = $token->createInject('tag-empty', false, $type, $token->attributes);
		$this->insertSource($new_token, false);
	}

	protected function handleTagContent(Token $token)
	{
		// Doesn't make sense for these to have content, so warn.
		if ($token->type === 'tag-start')
			$token->toss('tpl_content_must_be_empty');

		// This can't be used in loops, ifs, or anything really except tpl:template and tpl:container.
		// Other template calls are allowed too.
		foreach ($this->tree as $tree_token)
		{
			// Template call, that's fine.
			if ($tree_token->nsuri !== Template::TPL_NAMESPACE)
				continue;

			if ($tree_token->name !== 'template' && $tree_token->name !== 'container')
				$token->toss('tpl_content_inside_invalid');
		}
	}

	protected function handleTagOutput(Token $token)
	{
		if ($token->type === 'tag-start')
			$token->toss('tpl_output_must_be_empty');
		if (!isset($token->attributes['value']))
			$token->toss('generic_tpl_missing_required', 'value', $token->prettyName(), 'value');

		// Default the escape parameter just like {$x} does.
		if (!isset($token->attributes['escape']))
			$token->attributes['escape'] = $this->inside_cdata ? 'false' : 'true';
	}

	protected function handleTagAlter(Token $token)
	{
		if ($this->state !== 'outside')
			$token->toss('tpl_alter_inside_template');

		$this->state = 'alter';
	}

	protected function handleTagAlterEnd(Token $token)
	{
		$this->state = 'outside';
	}
}