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
		$this->_getParentModule()->loadLanguage('strings.yaml');
	}

	function mainAction()
	{
		$module = $this->_getParentModule();
	}

	function listAction()
	{
		$module = $this->_getParentModule();
	}

	function permissionsAction()
	{
	}

	function registrationAction()
	{
	}

	function settingsAction()
	{
	}
}