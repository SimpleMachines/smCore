<tpl:container>
	<tpl:alter match="site:html-head" position="after">
		<!--- Obviously, this normally should be in a .css file, this is just an example of overlays. --->
		<style type="text/css">/* <![CDATA[ */
			html
			{
				background: #e48dba;
				background: -moz-linear-gradient(top, #e48dba, #f3c9de);
			}

			.container
			{
				border: 1px solid #f3c9de;
			}

			h2
			{
				background: #f6abdc;
				background: -moz-linear-gradient(top, #ffbbec, #f6abdc);
			}

			nav
			{
				background: #925a2f;
				background: -moz-linear-gradient(top, #d24b97, #922f6a);
			}

			nav a
			{
				border-right: 1px solid #7e1348;
				color: #fff;
			}

			nav a:hover
			{
				background: rgb(60, 134, 117);
				background: rgba(60, 134, 117, 0.3);
			}

			footer
			{
				background: #fff;
				background: -moz-repeating-linear-gradient(top left 45deg, #ffd5f5, #ffd5f5 5px, #fff 5px, #fff 10px);
			}

			footer .credit
			{
				float: left;
			}
		/* ]]> */</style>
	</tpl:alter>

	<tpl:alter match="site:navigation" position="beforecontent">
		<a href="javascript://" onclick="alert('This theme is really cool.'); return false;">Theme</a>
	</tpl:alter>
</tpl:container>