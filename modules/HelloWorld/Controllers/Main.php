<?php

namespace smCore\HelloWorld\Controllers;

use smCore\Module\Controller;

class Main extends Controller
{
	public function main($app)
	{
		$module = $this->_getParentModule();

		$module->display('template');
	}
}