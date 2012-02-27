<?php

$time_st = microtime(true);

require(dirname(__DIR__) . '/include.php');

$theme = new SampleTheme(__DIR__, __DIR__);
$theme->loadTemplates('templates');
$theme->addLayer('main');

$theme->addTemplate('home');
$theme->context['site_name'] = 'my site';
$theme->output();

$time_et = microtime(true);

//echo 'Took: ', number_format($time_et - $time_st, 4), ' seconds.';