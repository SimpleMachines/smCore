<tpl:container>
	<tpl:template name="site:main"><!DOCTYPE html>
		<html>
			<head>
				<title>{$context.site_name}</title>
				<style type="text/css">
					body
					{
						font: 10pt sans-serif;
					}
				</style>
			</head>
			<body>
				<tpl:content />
			</body>
		</html>
	</tpl:template>

	<tpl:template name="site:home">
		<p>This example implements a simple &lt;tpl:not-last&gt; element, that only shows its contents when not on the last iteration of the foreach.</p>

		<p>Just a simple example of a custom implemented element.</p>

		<ul>
			<tpl:foreach from="{$context.list}" as="{$item}">
				<li>
					{$item.name}:
					<tpl:foreach from="{$item.letters}" as="{$letter}"><!---
						--->{$letter}<tpl:not-last>-</tpl:not-last><!---
					---></tpl:foreach><!---
					---><tpl:not-last>,</tpl:not-last>
				</li>
			</tpl:foreach>
		</ul>
	</tpl:template>
</tpl:container>