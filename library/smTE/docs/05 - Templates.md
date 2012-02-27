# Templates

## Overview

Templates are a basic design element in ToxG. They are used for many things,
such as:

- integration with the code (e.g. which templates are shown.)
- convenience "macros" (common tedious HTML.)
- hooks for common changes and additions.
- overriding default or base themes.


## Integrating with the code

On a basic level, the code will probably start off by loading a template, and
having it produce the content of the page. It might also use an "html" template
or one for the header/footer.

It's also possible that using a template might trigger the code to do something.

## For convenience and ease

Sometimes there's some HTML that you just have to write out a lot. For example,
consider the case of rounded corners compatible with older browsers... you might
find yourself writing:

	<div class="rounded">
		<div class="top"><div></div></div>
		<div class="content">
			stuff
		</div>
		<div class="bottom"><div></div></div>
	</div>

Instead, you might use a template:

	<site:rounded>
		stuff
	</site:rounded>

	<tpl:template name="site:rounded">
		<div class="rounded">
			<div class="top"><div></div></div>
			<div class="content">
				<tpl:content />
			</div>
			<div class="bottom"><div></div></div>
		</div>
	</tpl:template>

and then use `<site:rounded>` every time you want to display a rounded box.

## What about putting a variable in?

You can even use variables in templates, which is what really makes them useful.
Let's take for example, outputting an image button:

	<site:image-button name="save" alt="{#save}" />

	<tpl:template name="site:image-button">
		<input type="image" name="{$name}" src="/images/buttons/{$name}.png" alt="{$alt}" value="{$alt}" class="button-{$name}" />
	</tpl:template>

See those {$name} and {$alt} parts?  They refer to the attributes you put on
site:image-button. Another very common use for this is passing along classes:

	<div class="standard-class {$class}" />

However, note that you'll get an error if you forget to (or don't need to) put
a class on the template call. In this case, you can use tpl:default:

	<div class="standard-class {tpl:default var="{$class}"}">

## Hooks for common alterations

Templates are also a way to integrate with overlays, which alter templates.
See the [overlays documentation](./overlays.md) for more information.

Just remember you don't have to define a template to use it, so you can always
call a template so that other things can add things in that area.

## Overriding base templates

You can't define the same template twice in the same file, because that's just
strange and confusing (you ought to only have one.)  However, if you are
extending a theme, you might want to override, or replace, one of its templates.

To do this, you just define the same template in your file, and it will replace
the one from the base file. This provides an easy way for you to alter just
part of a theme without having to override every part of a file you just wanted
to change one thing in.
