<?php

require(dirname(__DIR__) . '/include.php');

// The default is simply "lang".
smCore\TemplateEngine\Expression::setLangFunction('my_lang_formatter');

$theme = new SampleTheme(__DIR__, __DIR__);
$theme->loadTemplates('templates');
$theme->addLayer('main');

$theme->addTemplate('home');
$theme->context['page_name'] = my_lang_formatter(array('page_name_home'));
$theme->output();

function my_lang_formatter($key, $params = array())
{
	// This could use gettext, or logic based on the number of parameters.
	// It could also use a different string based on numeric parameters.
	static $strings = array(
		'home_hello' => 'Hello, this is the home page.  Isn\'t it pretty?',
		'site_name' => 'My Site: %s',
		'page_name_home' => 'Home',
	);

	if (!isset($strings[$key[0]]))
		$string = '(unknown translation)';
	else
		$string = $strings[$key[0]];

	return vsprintf($string, $params);
}