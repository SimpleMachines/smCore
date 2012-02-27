<tpl:container>
	<tpl:template name="site:main"><!DOCTYPE html>
		<html>
			<head>
				<title><tpl:output value="{#site_name:$context.page_name}" debug="false" /></title>
				<style type="text/css">
					body
					{
						font: 10pt sans-serif;
					}

					.lang-debug
					{
						outline: 1px dashed orange;
					}

					.lang-debug:hover
					{
						outline: 2px dotted orange;
					}
				</style>

				<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
				<script type="text/javascript" src="lang-debug.js"></script>
			</head>
			<body>
				<tpl:content />
			</body>
		</html>
	</tpl:template>

	<tpl:template name="site:home">
		<p>{#home_info} {#home_info2} {#home_info3}</p>
		<p>{#home_something:text}</p>
	</tpl:template>
</tpl:container>