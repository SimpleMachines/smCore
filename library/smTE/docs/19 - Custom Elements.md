# For Developers - Creating Custom Elements

## Extensibility is important

The template engine has been built to ensure that you can easily add extra
elements when you need to, so that the language can be customized to your needs.

To avoid conflicts, everything is namespaced. When adding custom elements, you
should use your own namespace for custom elements so you can easily upgrade
later without worry of conflicts. For example, you might use "mytpl" in your
documentation with the URI "http://www.mywebsite.com/#toxg".


## Example usage

A good example of using this is utility templates. Consider the following:

	<tpl:template name="site:about-us">
		The staff members of this site are:
		<site:username uid="123" linked="true" />
		<site:username uid="456" linked="true" />
		<site:username uid="789" linked="true" />
	</tpl:template>

You might make the `site:username` template completely handled by code.


## Monkey see, monkey do

For your own elements, you can and should use StandardElements as a template. It
registers a listener for every element it supports, which is then processed when
that element is used.

You can also use an asterisk `*` to listen to all elements or all namespaces.
This can be used to report errors when a misspelled element is used, but if you
use only that, you'll need to delegate the event to the proper code yourself.

Also note that if you register a `xyz:*` *and* `xyz:name`, both will be called. In
fact, if you register `xyz:name` twice, each event handler will be called.


## Error messages

When the template author doesn't use your elements correctly, you can throw an
exception to notify them. The easiest way is to do this via the $token:

	$token->toss('untranslated', 'Hey punk, use site:username right, or else.');

You can also toss a formatted message, if your message is semantically the same
as one the system already uses (see [smCore\TemplateEngine\Exception](../includes/Exception.php) for a list):

	$token->toss('unknown_tpl_element', 'punk-element');


## How to emit code to the Builder

Your event handler receives a Builder object, and you'll want to just call
`emitCode()` on that. So, for example:

	$builder->emitCode('some_function();', $token);

Remember to give it the token, so that it can put proper error handling code
into the template, which makes your templates way easier to debug.

Sometimes you might want to send output. So that output is chained together
efficiently, there's a separate function for this. Use:

	$builder->emitOutputString('some text', $token);

For security reasons, please keep in mind when using the above that your string
is considered "raw". If it contains angle brackets, they will be sent directly.
If you're meaning to output text, remember to escape it.

You may also want to output an expression. To do this use:

	$builder->emitOutputParam('htmlspecialchars(\'some text\')', $token);

This will also be efficiently combined with other output for you.


## Expressions

There are a few types of expressions, and you should look at ToxgExpression and
ToxgBuilder for more information. You can call them like this:

	$code = $builder->parseExpression('variable', '{$x}', $token);

For that first parameter, there are the following options:

-	**variableNotLang**

	This parses a single variable, not allowing a plain string or language
	string. Use this when you need to set it (like tpl:set or tpl:foreach.)

-	**variable**

	This parses a single variable, allowing language strings. It's not
	used often, because if you want only a variable, you probably don't want
	language references.

-	**stringWithVars**

	This is used for an attribute that might be a variable or may be a string
	with variables in it. A common example is a template call:

		<my:div class="someclass" />

	You wouldn't want to have to put single quotes around that, right?  In that
	case, you rarely need an expression, so it's just a stringWithVars.

-	**normal**

	A normal expression, where you need quotes, +, ., etc. to build it.
	Function calls, etc. can be used here.

-	**boolean**

	A boolean expression (one that returns true/false.)  Not very different
	from a normal expression.
