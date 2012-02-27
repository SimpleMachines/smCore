<tpl:container xmlns:site="http://www.example.com/#site">
	<tpl:template name="site:html-title">
		<title><tpl:content /></title>
	</tpl:template>

	<tpl:template name="site:nav-link">
		<a href="./{tpl:if test="!empty({$page})"}?page={$page}{/tpl:if}"><tpl:content /></a>
	</tpl:template>

	<tpl:template name="site:theme-link">
		<a href="./?{tpl:if test="!empty({$context.page})"}page={$context.page}&amp;{/tpl:if}theme={$theme}" class="theme-{$theme}"><tpl:content /></a>
	</tpl:template>
</tpl:container>