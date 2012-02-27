<?php

require(dirname(__DIR__) . '/include.php');
require(__DIR__ . '/theme.php');

session_start();

if (isset($_GET['theme']) && in_array($_GET['theme'], array('base', 'red', 'blue', 'green')))
{
	session_theme_var($_GET['theme']);

	$uri = preg_replace('~&?\??theme=([a-z]+)~', '', $_SERVER['REQUEST_URI']);
	header('Location: ' . $uri, true, 302);
	exit;
}

if (session_theme_var() === null)
	session_theme_var('base');

$theme = new MyTheme(session_theme_var());
$theme->loadTemplates('index');
$theme->loadTemplates('basic');
$theme->loadTemplates('pages');
$theme->addLayer('html');

if (isset($_GET['page']) && in_array($_GET['page'], array('about', 'history')))
	$page = $_GET['page'];
else
	$page = 'home';

$theme->context['page_name'] = ucfirst($page);
$theme->context['page'] = $page;
$theme->addTemplate($page);
$theme->output();

function session_theme_var($value = null)
{
	$var = &$_SESSION['samples/themes_theme'];

	if ($value !== null)
		$var = $value;

	return $var;
}