<?php

// Add our library directory to the include path, to make life easier
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/library');
ini_set('display_errors', 1);

include_once __DIR__ . '/library/smCore/Application.php';
$application = new smCore\Application(__DIR__ . '/other/settings.php');
$application->run();