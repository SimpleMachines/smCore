<tpl:container>
	<tpl:alter match="site:html-head" position="after">
		<!--- Obviously, this normally should be in a .css file, this is just an example of overlays. --->
		<style type="text/css">/* <![CDATA[ */
			html
			{
				background: #8de4ba;
				background: -moz-linear-gradient(top, #8de4ba, #c9f3de);
			}

			.container
			{
				border: 1px solid #c9def3;
			}

			h2
			{
				background: #abf6dc;
				background: -moz-linear-gradient(top, #bbffec, #abf6dc);
			}

			nav
			{
				background: #2f925a;
				background: -moz-linear-gradient(top, #4bd297, #2f926a);
			}

			nav a
			{
				border-right: 1px solid #137e48;
				color: #fff;
			}

			nav a:hover
			{
				background: rgb(134, 134, 117);
				background: rgba(134, 134, 117, 0.5);
			}

			footer
			{
				background: #fff;
				background: -moz-repeating-linear-gradient(top left 45deg, #d5fff5, #d5fff5 5px, #fff 5px, #fff 10px);
			}

			footer .credit
			{
				float: left;
				font-weight: bold;
			}

			nav .about
			{
				font-weight: bold;
			}
		/* ]]> */</style>
	</tpl:alter>

	<tpl:alter match="site:footer" position="beforecontent">
		<span class="credit">Theme by <a href="http://www.example.com/">Some Guy</a>.</span>
	</tpl:alter>
</tpl:container>