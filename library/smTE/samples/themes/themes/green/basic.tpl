<tpl:container xmlns:site="http://www.example.com/#site">
	<tpl:template name="site:html-title">
		<title>GREEN: <tpl:content /></title>
	</tpl:template>

	<tpl:template name="site:nav-link">
		<a href="./{tpl:if test="!empty({$page})"}?page={$page}{/tpl:if}" class="{$page}"><tpl:content /></a>
	</tpl:template>
</tpl:container>