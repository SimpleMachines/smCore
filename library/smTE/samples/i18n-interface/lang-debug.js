jQuery(function ()
{
	var PROMPT_MESSAGE = "New translation for #%s with %s parameter(s):";

	var getParamRE = function ()
	{
		return new RegExp('<span class="lang-debug-param">([^<]+)</span>', 'g');
	}

	jQuery(".lang-debug").click(function ()
	{
		var element = jQuery(this);

		var id = element.attr("data-lang");
		var params = element.attr("data-lang-params");

		// This is a simple example.  Really, we want to handle the escaping of &, etc. better.
		var existing = element.html();
		var new_text = prompt(PROMPT_MESSAGE.replace("%s", id).replace("%s", params), existing.replace(getParamRE(), "%s"));
		if (new_text == null)
			return;

		// This would send the new_text to the server to store for later via XMLHttpRequest.

		var re = getParamRE();
		var match;
		while ((match = re.exec(existing)) != null)
			new_text = new_text.replace("%s", match[0]);

		element.html(new_text);
	});
});