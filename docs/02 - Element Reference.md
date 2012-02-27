# Element reference

## Overview

There are several standard and built-in elements supported within the smCore
template engine. Only elements within the "tpl" namespace (which is currently
mapped to "urn:toxg:template", but may change) have special functionality.

Any element in another namespace is used to access a template, if it exists.

## Element syntax

Elements can be wrapped in either angle or curly braces, as follows:

	<tpl:content />
	{tpl:content /}

To use curly braces as normal, just put any whitespace after the brace:

	var object = { tpl:content };

Or put a space after the :, which is more readable anyway:

	var object = {tpl: content};

The curly brace syntax is prefered within an existing HTML element, e.g.:

	<option{if test="$selected"} selected="selected"{/if}>

## How namespaces are recognized

ToxG ignores xmlns declarations on any node that doesn't have a colon in
its name. It also doesn't use a "default" namespace (xmlns=.)

In other words, ToxG would think this is a template (and remove it unless a
template was defined for it):

	<mml:math xmlns:mml="http://www.w3.org/1998/Math/MathML" />

However, the following elements it would leave alone:

	<math xmlns="http://www.w3.org/1998/Math/MathML" />
	<div xmlns:mml="http://www.w3.org/1998/Math/MathML"><mml:math /></div>

## Shortcuts

Within normal HTML, there are two shortcuts to use common elements. They are
as follows:

	{$variable}
	Shortcut for <tpl:output value="{$variable}" />, and works the same.

	{#lang_string}
	Shortcut for <tpl:output value="{#lang_string}" />, and works the same.

For more information on how to use variables and language strings, see the
separate "expressions" documentation.

## Comments

To comment out code, use a standard HTML/XML comment, except with three dashes,
like the following:

	<!--- <tpl:this-is-not-parsed /> --->


## CDATA

Within your source code, you can use CDATA sections to affect escaping. Unlike
in XML, CDATA affects only output and does not prevent tag parsing.

	<![CDATA[ {$not_escaped} ]]>

Keep in mind, the CDATA will also be output in the HTML (which indicates to
take the text as is, which is why it need not be escaped.)  This is mainly
used in `<script>` elements.

You can also use doctype="html" on a tpl:container element. This will make it
parse `<script>` and similar elements as CDATA.


## Template calls

When "calling" (using) a template, there's a special attribute that can be used
named tpl:inherit. This is also available for the tpl:element element.

It contains a space separated list of attribute names, or an asterisk.

The asterisk means to pass along any and all attributes passed into the
template. Otherwise, the list gives the names of attributes to pass along.

This is beneficial when writing convenience templates.


## Attributes

Within elements, attributes are handled specially. For template calls, they
are decoded so they work the same way normal elements will. This means:

	<my:title name="This &amp; That" />

Might produce:

	<div class="title" title="This &amp; That">This &amp; That</div>

However, you don't need to escape the values, and for expressions you
shouldn't. For example, this is correct:

	<tpl:if test="{$first} && {$last}">There's only one.</tpl:if>


## Supported elements

Below is a list of standard elements in this release of TOX-G. Other elements
may be supported via custom hooks.

### tpl:container

Similar to a `<div>` in HTML, this is a do-nothing element which allows you
to define namespaces or separate code for whatever reasons you might have.

Often used to "contain" templates. Can also be used inside a template.

No required attributes.

May have an attribute "doctype", with the value "html" or "xhtml". The
default is "xhtml". When set to "html", `<script>` and `<style>` will trigger
CDATA behavior (no HTML escaping.)

	<tpl:container xmlns:my="http://www.unknownbrackets.org/example/">
		<!--- Put some tpl:template elements here --->
	</tpl:container>

### tpl:template

Used to define a template, which can be used or altered later. Please note,
you do not have to define a template to call one (it will just do nothing.)
Altering undefined templates is also possible (see tpl:alter.)

Can only be used inside a <tpl:container> or outside everything. Cannot be
used inside itself.

The required name attribute specifies what template, and must contain a
namespace prefix (which must have been defined.)

The optional (despite its name) "requires" attribute lists variables that
the template requires to be passed to it (space separated.)

	<tpl:container xmlns:my="http://www.unknownbrackets.org/example/">
		<tpl:template name="my:name">
			Unknown W. Brackets
		</tpl:template>

		<tpl:template name="my:full-name" requires="{$first} {$mi} {$last}">
			{$first} <em>{$mi}.</em> <strong>{$last}</strong>
		</tpl:template>
	</tpl:container>

### tpl:content

Used inside a template to show where the content of the call will be shown.
This is easier to understand by looking at an example.

Can only be used inside tpl:template or a tpl:container.

Requires no attributes.

	<tpl:template name="my:div">
		<div class="mine"><span><tpl:content /></span></div>
	</tpl:template>

	<my:div>Hello.</my:div>

### tpl:alter

Used to "alter" the use of templates, as a hook system. Please see the
separate "overlays" documentation for more information.

Can only be used inside a tpl:container or outside everything in an overlay.

Requires a "match" attribute, which is a space separated list of templates
to apply the alteration to. They must include namespace prefixes, and
those must be defined.

The "position" attribute is also required, and allows the values "before",
"after", "beforecontent", and "aftercontent". Please see the separate
"overlays" documentation for more information.

	<tpl:template name="my:name">
		Unknown W. Brackets
	</tpl:template>

	<tpl:alter match="my:name" position="before">
		Mr.
	</tpl:alter>

### tpl:output

This is used to output the content of a variable or language string. You
may also use equations. Exactly the same as using the short hand (which is
described above under Shortcuts.)

Can be used inside templates and alterations (anywhere content can be.)

Requires a "value" attribute (specifying the variable or equation.)

The "escape" attribute is used to stop ToxG from using htmlspecialchars on
the value. It is optional, and it defaults to "true". Use "false" to prevent
escaping.

	<tpl:output value="{$var}" />
	<tpl:output value="{$var} * 2" />
	<tpl:output value="{$var} * 2" escape="false" />

### tpl:foreach

Used to repeat an operation for every item in a data set. For example,
this would commonly be used for a list of items that will be formatted the
same.

Can be used inside templates and alterations (anywhere content can be.)

Requires a "from" attribute that defines the list or array of items.

Requires an "as" attribute which is the variable to use for the value.
It can also be a key value pair, separated by =>.

	<tpl:foreach from="{$animals}" as="{$animal}">
		<li>{$animal.name} goes {$animal.sound}.</li>
	</tpl:foreach>

	<tpl:foreach from="{$tasks}" as="{$id} => {$task}">
		<a href="?delete={$id}">Delete task #{$id} - {$task.name}.</a>
	</tpl:foreach>

### tpl:for

Used to loop through code multiple times, based on certain inputs.

Requires at least one attribute from the following:

init, sets the initial variables.
while, the true/false condition to check before every run. Stops after a false.
modify, what to do to the data after every run.

	<tpl:for init="{$x} = 0" while="{$x} < 10" modify="{$x}++">
		Counting to 10... {$x}<br />
	</tpl:for>

### tpl:if

Used to do something only sometimes ("if" something is true).

Can be used inside templates and alterations (anywhere content can be.)

Requires a "test" attribute, which is the equation to check. See the
separate "expressions" documentation for more information about this.

	<tpl:if test="{$animal.is_mammal}">
		{$animal.name} is a mammal.
	</tpl:if>

	<tpl:if test="{$user.type} == 'jerk'">
		You are a jerk.
	</tpl:if>

### tpl:else

Used inside a tpl:if to do something otherwise, or to specify another
possibility.

Can be used inside templates and alterations (anywhere content can be.)

To specify another condition, use a "test" attribute, which is the equation
to check. See the separate "expressions" documentation for more information
about this.

	<tpl:if test="{$animal.is_mammal}">
		{$animal.name} is a mammal.
	<tpl:else test="{$animal.is_bird}" />
		{$animal.name} is a bird.
	<tpl:else />
		{$animal.name} is not a mammal or a bird.
	</tpl:if>

### tpl:flush

Used to forcibly send output to the browser. Should be used VERY sparingly.
Most often, this might be used immediately after the </head> element. It
might also be used in a very long list of data to make sure the user sees
it more quickly.

Can be used inside templates and alterations (anywhere content can be.)

Requires no attributes.

	<tpl:flush />

### tpl:set

Set a variable manually (e.g. for later use). This shouldn't be very
commonly used. Equations can be used.

Can be used inside templates and alterations (anywhere content can be.)

Requires a "var" attribute to specify the variable to set, and a "value"
attribute to specify what to set it to, like with tpl:output.

	At first, {$x} was 1....
	<tpl:set var="{$x}" value="{$x} + 1" />
	But now {$x} is 2.

### tpl:element

Used to dynamically output an element into the HTML. This can make it
really easy to create convenience templates.

Can be used inside templates and alterations (anywhere content can be.)

Requires a "tpl:name" attribute. Note that this attribute is in the tpl
namespace. If you use "name", it will be added to the outputted element.

Optionally uses a "tpl:inherit" attribute which means "inherit attributes
this template was used with." If you use "*" or "class", and your template
was called with a class attribute, that attribute will be added.

Any other attributes are passed along, unless you override them.

	<tpl:element tpl:name="input" type="text" class="text" tpl:inherit="*" />

### template-push

Used internally when making a template call. Saves variables in a stack.

Can be used inside templates and alterations (anywhere content can be.)

!!! Might get replaced with a start/end tag method.

	<tpl:template-push var_name="new value" />

### template-pop

Used internally when making a template call. Brings the old variables back.

Can be used inside templates and alterations (anywhere content can be.)

!!! Might get replaced with a start/end tag method.

	<tpl:template-pop />
