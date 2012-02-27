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
		<p>Hello, this is the home page.  Isn't it pretty?</p>

		<site:dynamic />
	</tpl:template>

	<tpl:template name="site:dynamic">
		<ul>
			<tpl:foreach from="{$dynamic}" as="{$item}">
				<li>
					<strong>{$item.title}</strong>
					<div>{$item.description}</div>
				</li>
			</tpl:foreach>
		</ul>
	</tpl:template>
</tpl:container>