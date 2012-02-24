<?php

namespace smCore\Modules\Admin\Controllers;
use smCore\Application, smCore\Module;

class MainController extends Module\Controller
{
	public function preDispatch()
	{
		$this->_getParentModule()->loadLanguage('strings.yaml');
	}

	function mainAction()
	{
	}
}