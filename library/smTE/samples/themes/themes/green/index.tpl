<tpl:container xmlns:site="http://www.example.com/#site" xmlns:green="http://www.example.com/#green">
	<tpl:template name="site:main">
		<div class="container">
			<site:navigation />

			<green:heading>
				{$context.page_name}
				<site:theme-selector />
			</green:heading>

			<div class="content">
				<tpl:content />
			</div>

			<site:footer />
		</div>
	</tpl:template>

	<!--- Using green namespace to avoid conflicts. --->
	<tpl:template name="green:heading">
		<h2>
			GREEN: <tpl:content />
		</h2>
	</tpl:template>
</tpl:container>