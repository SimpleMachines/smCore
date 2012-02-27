# Quick start

## Overview

To get started fast, start in the samples directory. Look inside there and
also read the rest of this documentation.

The basic syntax is similar to XML, and described in more detail within the
[element-ref documentation](./02 - Element Reference.md). Note that because it's not XML, the HTML you use
can be built more freely.

## Namespaces

The use of XML namespaces is central to this system, even though the system
itself doesn't use XML. Namespaces are really just URIs, which you associate
with a short name for convenience.

The URI itself doesn't matter, as long as you use the exact same one when
referring to it. You can just use your site's URL.

## Writing your first template

A basic template need not have much in it. You start with defining the template
as so:

	<tpl:template name="site:home">

Next, put the content you want the template to output:

		Llamas are cool.

And then close the template:

	</tpl:template>

With this, you have your first completed template:

	<tpl:template name="site:home">
		<p>Llamas are cool.</p>
	</tpl:template>


## Adding more templates

Now we're going to wrap the whole thing in a "container":

	<tpl:container>
		(what you had before)
	</tpl:container>

Okay great. Let's add a template inside the container:

	<tpl:template name="site:about">
		<p>This is a wonderful site using the smCore Template Language for templating.</p>
	</tpl:template>

So, your completed file looks like this:

	<tpl:container>
		<tpl:template name="site:home">
			<p>Llamas are cool.</p>
		</tpl:template>

		<tpl:template name="site:about">
			<p>This is a wonderful site using the smCore Template Language for templating.</p>
		</tpl:template>
	</tpl:container>

Let's add a simple link into that site:home, shall we?  Change it to this:

		<tpl:template name="site:home">
			<p>Llamas are cool. Want to <a href="about.php">learn about this site?</a></p>
		</tpl:template>

Now that you've got that written, save it as "templates.tpl".


## Using it in some code

For now, create a new directory under samples, "quick-start". Put that file
we just created (templates.tpl) in there. Let's put a basic index.php in there
as well:

	<?php

	// Pull in the sample code.
	require(dirname(__DIR__) . '/include.php');

	$theme = new SampleTheme();
	$theme->loadTemplates('templates');

	$theme->addTemplate('home');
	$theme->output();

	?>

So now, you already have a template driven site. But we want to have another
page, don't we?  Create a copy of that index.php and save it as "about.php".

Now try it out.


## What's next?

There's lots of documentation in here, so take a gander.
