# For Developers - Creating Template Callbacks

## Why use callbacks?

A callback is a good way to load data for a template to use, and only load it
if the template needs it.

You could use custom elements for this, and basically are. But the idea of
using callbacks (or in other words hooks) is to do things at runtime which are
necessary.


## Example scenario

For the purposes of example, let's suppose you have the following template code:

	<tpl:template name="site:about-us">
		We are a zoo. These are the animals we have:
		<site:animals />
	</tpl:template>

	<tpl:template name="site:animals">
		<ul>
			<tpl:foreach from="{$animals}" as="{$animal}">
				<li>{$animal.name}</li>
			</tpl:foreach>
		</ul>
	</tpl:template>


## Loading only what you need

In the above template, suppose you only want to load the list of animals in the
case that they are going to be displayed, but want to leave their display up to
the template author.

In this case, you would "hook" the `<tpl:template name="site:animals">` element,
and load the appropriate information there. For example:

	function maybeHookTemplate(ToxgBuilder $builder, $type, array $attributes, ToxgToken $token)
	{
		list ($ns, $name) = explode(':', $attributes['name'], 2);
		$nsuri = $token->getNamespace($ns);

		if ($nsuri == 'http://www.example.com/' && $name === 'animals')
			$builder->emitCode('$animals = MySystem::getAnimalList();', $token);
	}

	$template->listenEmitBasic('template', 'maybeHookTemplate');


## Best practices

Doing a bunch of ifs for each template does not make for very easily extended
code. Instead, you might want to add a generic hook, e.g. into your event
system (based on the name.)

For this purpose, within the templates, it's best to use a certain namespace
for things that might be hooked, and another for convenience templates.

For example, it could be a performance problem if you hooked every instance of:

	<my:rounded-corners>
		Blah blah.
	</my:rounded-corners>

If that was a commonly used template/widget interface. A good choice might be
"my" for internal template stuff, and "site" for stuff that hooks into things.
