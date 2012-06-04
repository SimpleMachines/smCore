<?php

/**
 * Language Package Storage
 *
 * @package com.fustrate.admin
 * @author Steven "Fustrate" Hoffman
 * @license MPL 1.1
 * @version 1.0 Alpha 1
 */

namespace smCore\Modules\Admin\Storages;
use smCore\Application, smCore\Module, smCore\Models\Language,
	smCore\Exception;

class LangPackages extends Module\Storage
{
	public function getCount()
	{
	}

	public function getAll()
	{
		$db = Application::get('db');

		$result = $db->query("
			SELECT *
			FROM beta_lang_packages");

		return $result->fetchAll();
	}

	public function getByName($name)
	{
		$db = Application::get('db');

		$result = $db->query("
			SELECT *
			FROM beta_lang_packages
			WHERE package_name = ?",
			array(
				$name,
			)
		);

		if ($result->rowCount() < 1)
			throw new Exception('');

		return $result->fetch();
	}
}