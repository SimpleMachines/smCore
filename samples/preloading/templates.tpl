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

		<site:dynamic>
			<ul>
			<tpl:foreach from="{$dynamic}" as="{$item}">
				<li>
					<strong>{$item.title}</strong>
					<!--- Try commenting out this next line (use three dashes like this comment.) --->
					<site:dynamic-desc>{$item.description}</site:dynamic-desc>
				</li>
			</tpl:foreach>
			</ul>

			Try commenting out the part in the template that loads the description.
		</site:dynamic>
	</tpl:template>

	<tpl:template name="site:dynamic-desc">
		<div><tpl:content /></div>
	</tpl:template>
</tpl:container>