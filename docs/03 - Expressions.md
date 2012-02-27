# Element reference

## Overview

In general, the expressions used in the smCore template language are just PHP
expressions with special syntax for variables. So you can use normal operators
like `&&`/`and`, `||`/`or`, `+`, `-`, `*`, `/`, etc.

In some contexts (such as setting a variable with tpl:set) it's not possible
to use an equation. The documentation for it will mention what sort of
expression the element/attribute supports.

## Variable syntax

Variables in general look like `{$x}`. However, these are commonly associative
arrays. You can use any of the following examples:

	{$x.y.z}
		The same as $x['y']['z'] in PHP.

	{$x.$y.1}
		The same as $x[$y][1] in PHP.

	{$x[$y.z].blah}
		The same as $x[$y['z']]['blah'] in PHP.

Most of the time, your expressions will be simple, and the dot syntax is the
preferred syntax (except when you need to do something like the last example
above, which should be rare/never ideally.)


## Language string syntax

Language strings are also easy to use, since they will be very common in your
templates. The basic format is `{#abc}`. However, these will often have some
sort of parameters which are put into the language string, like `{#abc:$name}`.

Here are a few examples:

	{#dogs_are_cool}
		Shows a normal language string.

	{#have_x_dogs:3}
		Shows a language string with "3" formatted into it.

	{#someone_is_cool:$person.name}
		Formats $person.name into it.

	{#foo_bar:$x:1:$someone.else:ORANGE:3}
		Formats lots of things into it.

	{#thing_is_height:{#person_named:$person.name}:{$person.height}}
		Complicated example of using a language string as a parameter.

	{#myModule.errors.missing_value}
		Language strings can come from arrays, using the same syntax as variables.