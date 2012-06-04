<?php

namespace smCore\Modules\Admin\Controllers;
use smCore\Application, smCore\Module, smCore\Storage\Factory as StorageFactory;

class Languages extends Module\Controller
{
	public function preDispatch()
	{
		Application::get('menu')->setActive('admin', 'languages');
	}

	public function main()
	{
		$module = $this->_getParentModule();

		$module
			->addView('languages_main', array(
				'languages' => StorageFactory::getStorage('Languages')->getAll(),
				'packages' => $module->getStorage('LangPackages')->getAll(),
			))
			->setPageTitle('Languages');
	}

	public function viewPackage()
	{
		$module = $this->_getParentModule();
		$name = Application::get('router')->getMatch('slug');

		$package = $module->getStorage('LangPackages')->getByName($name);
		$strings = $module->getStorage('LangStrings')->getByPackageId($package->id_package);

		$module
			->addView('languages_view_package', array(
				'package' => $package,
				'strings' => $strings,
			))
			->setPageTitle('Viewing Package: ');
	}

	public function stringEdit()
	{
		die('got here');
		$post = Application::get('input')->post;

		$id = $post->getInt('id_string');
		$key = $post->textRegex('/[a-z0-9_\-\.]$/iAD');
		$package = $post->getInt('string_package');
		$language = $post->getInt('string_language');

		
	}
}