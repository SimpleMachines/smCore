<?php

namespace smCore\HelloWorld\Controllers;

use smCore\Module\Controller;

class Main extends Controller
{
	public function myMainMethod()
	{
		$module = $this->_getParentModule();

		$dir = new \smCore\Filesystem\Directory(__DIR__ . '/doesntexist/');

		return $module->render('hello_world');
	}
}