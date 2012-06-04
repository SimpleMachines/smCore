<?php

namespace smCore\Modules\Users\Controllers;
use smCore\Application, smCore\Module;

class Admin extends Module\Controller
{
	/**
	 * Load the language strings before dispatch.
	 */
	function preDispatch()
	{
		$this->_getParentModule()->loadLangPackage();
	}

	function main()
	{
		$module = $this->_getParentModule();

		$module
			->addView('admin/main', array(
				'users' => $module->getStorage('Users')->getPage(),
			))
			->setPageTitle('User Administration');
	}

	function moreInfo()
	{
		$module = $this->_getParentModule();

		$id = (int) Application::get('router')->getMatch(1);

		if ($id < 1)
			$module->throwLangException('Invalid user ID.');





		$module
			->addView('admin/more', array(
				'user' => null,
			))
			->setPageTitle(':)');
	}

	function listUsers()
	{
		$module = $this->_getParentModule();
	}

	function permissions()
	{
	}

	function registration()
	{
	}

	function settings()
	{
	}
}