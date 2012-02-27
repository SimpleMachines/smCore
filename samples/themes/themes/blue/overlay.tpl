<tpl:container>
	<tpl:alter match="site:html-head" position="after">
		<!--- Obviously, this normally should be in a .css file, this is just an example of overlays. --->
		<style type="text/css">/* <![CDATA[ */
			html
			{
				background: #8dbae4;
				background: -moz-linear-gradient(top, #8dbae4, #c9def3);
			}

			.container
			{
				border: 1px solid #c9def3;
			}

			h2
			{
				background: #abdcf6;
				background: -moz-linear-gradient(top, #bbecff, #abdcf6);
			}

			nav
			{
				background: #2f5a92;
				background: -moz-linear-gradient(top, #4b97d2, #2f6a92);
			}

			nav a
			{
				border-right: 1px solid #13487e;
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
				background: -moz-repeating-linear-gradient(top left 45deg, #d5f5ff, #d5f5ff 5px, #fff 5px, #fff 10px);
			}

			footer .credit
			{
				float: left;
			}
		/* ]]> */</style>
	</tpl:alter>

	<tpl:alter match="site:footer" position="beforecontent">
		<span class="credit">Theme by Some Guy.</span>
	</tpl:alter>
</tpl:container>