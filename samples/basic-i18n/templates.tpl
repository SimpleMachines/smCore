<tpl:container>
	<tpl:template name="site:main"><!DOCTYPE html>
		<html>
			<head>
				<title>{#site_name:$context.page_name}</title>
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
		<p>{#home_hello}</p>
	</tpl:template>
</tpl:container>