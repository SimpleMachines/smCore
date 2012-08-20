<?php

namespace smCore\HelloWorld\Controllers;

use smCore\Module\Controller;

class Main extends Controller
{
	public function myMainMethod()
	{
		$dir = new \smCore\Filesystem\Directory(__DIR__ . '/doesntexist/');

		return $this->module->render('hello_world');
	}
}