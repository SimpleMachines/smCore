<?php

require(dirname(__DIR__) . '/include.php');
require(__DIR__ . '/theme.php');

$theme = new MyTheme(__DIR__, __DIR__);
$theme->enableLangDebugging();
$theme->loadTemplates('templates');
$theme->addLayer('main');

$theme->addTemplate('home');
$theme->context['page_name'] = my_lang_formatter('page_name_home');
$theme->output();

function my_lang_string($id)
{
	static $strings = array(
		'home_info' => 'This example just shows how you might make it easy to manage language strings.',
		'home_info2' => 'It\'s very simplistic, there\'s a lot better that could be done.',
		'home_info3' => 'Try clicking on one of these outlined sentences.',
		'home_something' => 'This uses a parameter which is %s.',
		'site_name' => 'My Site: %s',
		'page_name_home' => 'Home',
	);

	// This might return another language that's being used as a template, etc.
	if (!isset($strings[$id]))
		return '(unknown translation)';
	else
		return $strings[$id];
}

function my_lang_formatter($key, $params = array())
{
	return vsprintf(my_lang_string($key[0]), $params);
}