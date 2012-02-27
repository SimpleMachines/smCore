# smCore Template Engine: Major Changes

## Version 1.0 Alpha 1

	* Minimum PHP version bumped to 5.3.0 because of namespacing and anonymous functions.
	+ Multiple filters (formerly "formats") can be applied to one expression.
	+ Changed filter syntax from {%name:$var:params} to {$var %name:params}
	+ Default filter class/function now included with generic filters.
	- Removed tpl:json and tpl:raw, use filters instead.
	- Removed "as" attribute on tpl:output.

## Version 0.1-alpha6

	+ Using HTML5, rather than XHTML, escaping rules is now possible with the doctype attribute on tpl:container.
	* Template attributes are now unescaped and re-escaped rather than double escaped.
	* Template local tpl:alter elements are no longer applied to other templates.

## Version 0.1-alpha4

	+ Alterations can now be included in the same file as the templates.
	+ You can now detect the usage of templates for preloading data.
	+ Added new element tpl:element, which allows variable HTML elements to be conveniently created with passed-through attributes.
	+ Added support for tpl:inherit on template calls and tpl:element, to specify attributes for pass-through from the calling template.
	* Expressions are now parsed with a slightly different interface. See the dev-custom-elements documentation for the changes.

## Version post-0.1-alpha3

	+ Added <tpl:for init="" while="" modify=""> element
	+ Added support for key=>value pairs in <tpl:foreach>
	+ Added custom namespace support


## Version 0.1-alpha1

	+ Added common variables (which are used throughout all templates.)
	+ Added the "requires" attribute to tpl:template so templates may explicitly require parameters.
	* Templates are now compiled together and optimized based on a pre-build phase. This changes the exposed API a bit.
	* There are now wrapper templates to ensure that overlays are applied to top level template calls.
