<?php

namespace smCore\TemplateEngine;

class Exception extends \Exception
{
	protected static $messages = array(
		// This is to simplify life for custom stuff.
		'untranslated' => '%s',

		// *** Template parsing:

		'parsing_invalid_source_type' => 'The inserted source must be either a Source or Token.',
		'parsing_element_incomplete' => 'Unclosed element %1$s started at %2$s, line %3$s.',
		'parsing_internal_error' => 'Internal parsing error.',
		'parsing_content_outside_template' => 'Unexpected content outside any template definition.',
		'parsing_unmatched_tags' => 'Expecting the close tag from %1$s in %2$s, line %3$s.',
		'parsing_tag_already_closed' => 'Unmatched element %s, you closed it already or never opened it.',
		'parsing_tag_end_unmatched' => 'End tag for %1$s found instead of %2$s, started at %3$s, line %4$s.',
		'template_missing_required' => 'Template %1$s is missing the following attributes: %2$s',
		'template_not_found' => 'Unable to find template named %1$s in namespace %2$s',

		// *** File handling:

		'parsing_cannot_open' => 'Unable to open template file.',
		'parsing_cannot_seek' => 'The resource must be a seekable stream.',
		'parsing_cannot_read' => 'Unable to read template file.',
		'parsing_not_supported' => 'Unexpected source type: %s',
		'builder_cannot_open' => 'Unable to open output file: %s',
		'builder_cannot_write' => 'Unable to write to template cache file.',

		// *** Overlays:

		'overlay_no_source' => 'No overlay source specified.',
		'overlay_incomplete' => 'Unexpected end of file before a tpl:alter was finished.',
		'overlay_element_outside_alter' => 'Unexpected element %s while looking for <tpl:container>s and <tpl:alter>s.',
		'overlay_content_outside_alter' => 'Unexpected content when looking for <tpl:container>s and <tpl:alter>s.  Put it in a comment, perhaps?',
		'overlay_other_outside_alter' => 'Unexpected %s when looking for <tpl:container>s and <tpl:alter>s.',
		'overlay_alter_must_be_not_empty' => 'Please always use an start tag like <tpl:alter match="x:y" position="before">, it should have content inside it.',

		// *** Syntax problems:

		'syntax_invalid_tag' => 'Malformed attributes or unclosed tag.',
		'syntax_invalid_tag_end' => 'Malformed attributes or unclosed end tag.',
		'syntax_name_unterminated' => 'Malformed tag or attribute: name isn\'t terminated correctly.',
		'syntax_name_ns_invalid' => 'Malformed tag or attribute: invalid namespace.',
		'syntax_name_invalid' => 'Malformed tag or attribute: invalid name.',
		'syntax_attr_value_missing' => 'Malformed tag or missing attribute value.',
		'syntax_attr_value_not_quoted' => 'Malformed tag or unquoted attribute value.',
		'syntax_attr_value_unterminated' => 'Malformed tag or missing end quote in attribute value.',
		'syntax_comment_unterminated' => 'Unterminated comment started on line %s.',
		'syntax_tag_buffer_unmatched_quotes' => 'Unclosed quote or unexpectedly long tag.',

		// *** Building errors:

		'builder_unexpected_template' => 'New template found by builder, not found by prebuilder.',
		'builder_unclosed_template' => 'Finished template cache file with an open template.',
		'builder_element_outside_template' => 'Element %s found outside template.',
		'builder_stuff_outside_template' => 'Text or code found outside template.',

		// *** Elements in tpl:

		'unknown_tpl_element' => 'Unrecognized or misspelled element tpl:%s (or it didn\'t generate code.)',
		'generic_tpl_must_be_empty' => 'All %s must be empty.',
		'generic_tpl_must_be_not_empty' => 'All %s cannot be empty.',
		'generic_tpl_missing_required' => 'Missing attribute %s for %s (required: %s.)',
		'generic_tpl_no_ns_or_name' => 'All template and alter names must have both a namespace and a name.',
		'generic_tpl_empty_attr' => 'Missing or empty attribute %s for %s.',
		'tpl_container_invalid_doctype' => 'Unsupported doctype: only html and xhtml are supported.',
		'tpl_content_must_be_empty' => 'Please always use an empty tag like <tpl:content />, it cannot have content inside it.',
		'tpl_content_inside_invalid' => 'You cannot use tpl:content within tpl:if, tpl:foreach, etc.  It must be inside a tpl:template.',
		'tpl_content_twice' => 'Only one tpl:content is allowed per template.',
		'tpl_cycle_values_empty' => 'Cannot cycle through an empty array.',
		'tpl_output_must_be_empty' => 'Please always use an empty tag like <tpl:output />, it cannot have content inside it.',
		'tpl_for_no_params' => 'Any tpl:for element must use at least one parameter (init, while, modify).',
		'tpl_foreach_invalid_from' => 'Cannot foreach over a string, you probably want a variable.',
		'tpl_set_invalid_meta' => 'Invalid meta data for <tpl:set>. Either a value attribute or content can be used, not both.',
		'tpl_template_pop_without_push' => 'Please always use a template-push before a template-pop.',
		'tpl_template_inside_template' => 'Templates cannot contain other templates.  Forget a closing tpl:template?',
		'tpl_template_inside_alter' => 'Templates must be outside alters, templates, and other elements.',
		'tpl_template_must_be_not_empty' => 'Please always use an start tag like <tpl:template name="x:y">, it should have content inside it.',
		'tpl_template_missing_name' => 'Undefined or empty name attribute for tpl:template.',
		'tpl_template_name_without_ns' => 'Every template should have a namespace, %s didn\'t have one.',
		'tpl_template_name_unknown_ns' => 'You need to declare namespaces even for templates (%s was undeclared.)',
		'tpl_template_name_empty_name' => 'You need both a namespace and name for every template (%s didn\'t have a name.)',
		'tpl_template_duplicate_name' => 'Duplicate tpl:template named %s.',
		'tpl_alter_inside_template' => 'Any tpl:alter must be outside other alters, templates, etc.',
		'tpl_alter_missing_match_position' => 'Element tpl:alter must have match="ns:template" and position="before" or similar.',
		'tpl_alter_invalid_position' => 'Unsupported position for tpl:alter.',
		'tpl_alter_match_without_ns' => 'Every matched element should have a namespace, %s didn\'t have one.',
		'tpl_alter_match_unknown_ns' => 'You need to declare namespaces even for matched elements (%s was undeclared.)',
		'tpl_alter_match_empty_name' => 'Every matched element should have a namespace and name, %s didn\'t have one.',
		'tpl_alter_recursion' => 'Potential alter recursion detected on %s.',

		// *** Expression parsing:

		// The error messages below are formatted into this one.
		'expression_invalid_meta' => 'Invalid expression %s, %s',
		'expression_expected_var' => 'expected variable reference.',
		'expression_expected_var_only' => 'expecting only a variable reference.',
		'expression_empty' => 'expected expression, found empty.',
		'expression_incomplete' => 'incomplete expression.',
		'expression_braces_unmatched' => 'unmatched braces.',
		'expression_brackets_unmatched' => 'unmatched square brackets in expression.',
		'expression_expected_ref' => 'expecting reference like {$name} or {#name}.',
		'expression_expected_ref_nolang' => 'expecting variable like {$name} inside braces.',
		'expression_var_name_empty' => 'expecting a variable name, but just got {$}.',
		'expression_lang_name_empty' => 'empty language reference.',
		'expression_format_type_empty' => 'empty format type.',
		'expression_validation_error' => 'could not be validated. Built code: <tt>%s</tt>',
		'expression_empty_brackets' => 'empty square brackets.',
		'expression_filter_no_name' => 'expected a filter name.',
		'expression_unexpected_semicolon' => 'unexpected semicolon in variable reference.',
		'expression_unknown_error' => 'unable to parse properly.',
	);

	public $tpl_file = null;
	public $tpl_line = 0;
	protected $code = null;

	public function __construct($id_message)
	{
		$params = func_get_args();
		$params = array_slice($params, 1);

		// So that the fourth param can just be an array.
		while (count($params) === 1 && is_array($params[0]))
			$params = $params[0];

		$message = self::format($id_message, $params);

		if ($this->tpl_file === null && $this->tpl_line === 0)
			parent::__construct($message);
		else
			parent::__construct($this->tpl_file . '[' . $this->tpl_line . '] ' . $message);

		$this->code = $id_message;
	}

	public static function format($id_message, $params)
	{
		if (!isset(self::$messages[$id_message]))
			return 'UNTRANSLATED: ' . $id_message . ' (' . implode(', ', $params) . ')';
		else
			return vsprintf(self::$messages[$id_message], $params);
	}
}