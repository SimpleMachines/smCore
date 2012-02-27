<?php

require(dirname(__DIR__) . '/include.php');
require(__DIR__ . '/theme.php');

$theme = new MyTheme(__DIR__, __DIR__);
$theme->loadTemplates('templates');
$theme->addLayer('main');

$theme->addTemplate('home');
$theme->context['site_name'] = 'My Site';
$theme->output();