<tpl:container xmlns:site="http://www.example.com/#site">
	<tpl:template name="site:html"><!DOCTYPE html>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; encoding=utf-8" />
				<site:html-title>The Pretend Museum of Somewheresville</site:html-title>

				<site:html-head />
			</head>
			<body>
				<site:main>
					<tpl:content />
				</site:main>
			</body>
		</html>
	</tpl:template>

	<tpl:template name="site:main">
		<div class="container">
			<h2>
				{$context.page_name}
				<site:theme-selector />
			</h2>

			<site:navigation />

			<div class="content">
				<tpl:content />
			</div>

			<site:footer />
		</div>
	</tpl:template>

	<tpl:template name="site:navigation">
		<nav>
			<site:nav-link page="">Home</site:nav-link>
			<site:nav-link page="about">About</site:nav-link>
			<site:nav-link page="history">History</site:nav-link>
			<tpl:content />
		</nav>
	</tpl:template>

	<tpl:template name="site:footer">
		<footer>
			Copyright &copy; 2011 Nobody.
			<tpl:content />
		</footer>
	</tpl:template>

	<tpl:template name="site:theme-selector">
		<span class="theme-selector">
			Themes:
			<site:theme-link theme="base">Standard</site:theme-link>
			<site:theme-link theme="red">Red</site:theme-link>
			<site:theme-link theme="blue">Blue</site:theme-link>
			<site:theme-link theme="green">Green</site:theme-link>
		</span>
	</tpl:template>

	<tpl:alter match="site:html-head" position="before">
		<!--- Obviously, this normally should be in a .css file, this is just an example of overlays. --->
		<style type="text/css">/* <![CDATA[ */
			html
			{
				background: #8dbae4;
				background: -moz-linear-gradient(top, #8dbae4, #c9def3);
				height: 100%;
			}

			html, body
			{
				color: #333;
				font-family: sans-serif;
				font-size: 10pt;
				margin: 0;
				padding: 0;
			}

			.container
			{
				background: #fff;
				background: -moz-linear-gradient(top, #fff, #f8f8f8);
				border: 1px solid #c9def3;
				margin: 2ex auto;
				max-width: 940px;
				min-width: 800px;
				width: 80%;
			}

			h2
			{
				background: #f6dcab;
				background: -moz-linear-gradient(top, #ffecbb, #f6dcab);
				margin: 0;
				font-family: "Trebuchet MS", sans-serif;
				font-weight: normal;
				padding: 1ex;
			}

			nav
			{
				background: #2f5a92;
				background: -moz-linear-gradient(top, #4b97d2, #2f6a92);
				display: block;
			}

			nav a
			{
				border-right: 1px solid #13487e;
				color: #fff;
				display: inline-block;
				float: left;
				padding: 0.5ex 2ex;
				text-decoration: none;
			}

			nav a:hover
			{
				background: rgb(60, 134, 117);
				background: rgba(60, 134, 117, 0.3);
			}

			nav a:last-child
			{
				border-right-width: 0px !important;
				float: none;
			}

			.content
			{
				padding: 1ex;
			}

			p:first-child
			{
				margin-top: 0;
			}

			p:last-child
			{
				margin-bottom: 0;
			}

			h3
			{
				font-family: "Trebuchet MS", sans-serif;
				margin: 0;
				padding: 1ex 0;
			}

			footer
			{
				background: #fff;
				background: -moz-repeating-linear-gradient(top left 45deg, #fff5d5, #fff5d5 5px, #fff 5px, #fff 10px);
				display: block;
				font-size: smaller;
				margin-top: 10px;
				padding: 0.5ex 2ex;
				text-align: right;
			}

			.theme-selector
			{
				cursor: default;
				float: right;
				font-size: 8pt;
				line-height: 1.4em;
			}

			.theme-selector a
			{
				border: 1px solid #ccc;
				color: transparent;
				display: inline-block;
				height: 2ex;
				overflow: hidden;
				vertical-align: top;
				width: 2ex;
			}

			.theme-selector a:hover
			{
				border-color: white;
			}

			.theme-selector a.theme-red
			{
				background: red;
			}

			.theme-selector a.theme-green
			{
				background: green;
			}

			.theme-selector a.theme-blue
			{
				background: blue;
			}
		/* ]]> */</style>
	</tpl:alter>
</tpl:container>