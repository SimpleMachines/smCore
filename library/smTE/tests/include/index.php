<?php

require(__DIR__ . '/validate.php');
require(__DIR__ . '/harness.php');
require(__DIR__ . '/list.php');
require(__DIR__ . '/specialtheme.php');

error_reporting(E_ALL);
set_error_handler('error_handler');

function error_handler($type, $string, $file, $line)
{
	if (error_reporting() & $type)
	{
		$pretty_file = str_replace(dirname(dirname(__DIR__)), '...', $file);
		throw new ErrorException($pretty_file . '[' . $line . ']: ' . $string, 0, $type, $file, $line);
	}

	return false;
}