<?php

namespace smCore\Modules\Admin\Controllers;
use smCore\Application, smCore\Module;

class Main extends Module\Controller
{
	public function preDispatch()
	{
//		$this->_getParentModule()->loadLangPackage();
		Application::get('lang')->loadPackageByName('smcore.admin');
		Application::get('menu')->setActive('admin');
	}

	function main()
	{
		$module = $this->_getParentModule();

		$modules = array();
		$storage = Application::get('modules');

		foreach ($storage->getIdentifiers() as $identifier)
		{
			$modules[] = $storage->getModuleConfig($identifier);
		}

		$module
			->addView('main', array(
				'modules' => $modules,
			))
			->setPageTitle(Application::get('lang')->get('smcore.admin.titles.main'));
	}
}