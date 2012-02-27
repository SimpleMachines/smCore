# Overlays

## Overview

Overlays are used to apply changes to templates. An overlay consists of one or
many alterations (tpl:alter elements) which get applied to templates.

An alteration is, in other words, putting more things into the template.

A basic example of this is:

	<tpl:template name="my:name">
		Unknown W. Brackets
	</tpl:template>

	<tpl:alter match="my:name" position="before">
		Mr.
	</tpl:alter>

Which would produce is output:

	Mr. Unknown W. Brackets

## Matching a template

The match attribute specifies a list of templates that the alteration might
match. All of these have to have namespace prefixes, so that they apply to the
correct template no matter how it is referenced.

Usually, you'll put your namespace definitions on a tpl:container around any
tpl:alters you might use, instead of on each tpl:alter, though.

The list is space separated, and will be applied to all of the matching
templates.

	<!--- Matches a bunch of pets. --->
	<tpl:alter match="my:dogs my:puppies my:cats my:kittens my:birds" position="before">
		blah
	</tpl:alter>


## Position of the alteration

The position attribute tells it where the alteration will apply. It has four
possible values, each of which is useful in its own respect.

In the examples, please consider the following template and call:

	<tpl:template name="my:about">
		Unknown W. Brackets
		<tpl:content />
		That's all you can say.
	</tpl:template>

	<my:about>is cool.</my:about>

### before

This will place the alteration before what the template normally outputs.

	<tpl:alter match="my:about" position="before">
		Mr.
	</tpl:alter>

Mr. Unknown W. Brackets is cool. That's all you can say.

### after

This will place the alteration after what the template normally outputs.

	<tpl:alter match="my:about" position="after">
		Also, llamas rock.
	</tpl:alter>

Unknown W. Brackets is cool. That's all you can say. Also, llamas rock.

### beforecontent

If the template doesn't have a tpl:content element, this is the same as
"after"... but if it has one, the alteration will be made before the
tpl:content element.

	<tpl:alter match="my:about" position="beforecontent">
		is a jerk, but that
	</tpl:alter>

Unknown W. Brackets is a jerk, but that is cool. That's all you can say.

### aftercontent

If there's no tpl:content element in the template, this will be the same as
"after". When there is one, the alteration will be made immediately after
it.

	<tpl:alter match="my:about" position="beforecontent">
		I guess.
	</tpl:alter>

Unknown W. Brackets is cool. I guess. That's all you can say.

## Nesting

It's important to note that elements don't need to be properly nested within
alternations, as long as the resulting template would be properly nested.

As an example, take the following:

	<tpl:template name="my:panel">
		<h2>{$title}</h2>
		<tpl:content />
	</tpl:template>

	<my:panel title="Stuff">
		This is some stuff.
	</my:panel>

Now, let's create an alternation (actually two) to simply not show that title
at all. We do this using tpl:if, like so:

	<tpl:alter match="my:panel" position="before">
		<tpl:if test="false">
	</tpl:alter>

	<tpl:alter match="my:panel" position="beforecontent">
		</tpl:if>
	</tpl:alter>

Presto, it's gone. The important thing is that the template, after being
altered, had correct nesting. It would've looked like this:

	<tpl:template name="my:panel">
		<tpl:if test="false">
			<h2>{$title}</h2>
		</tpl:if>
		<tpl:content />
	</tpl:template>


## Variables

When variables are given to a template, you can use them in alterations too.

For example:

	<tpl:alter match="my:something" position="before">
		<tpl:if test="{$class} == 'llama'">
			This is the llama one I want to alter.
		</tpl:if>
	</tpl:alter>

	<my:something class="llama" />
