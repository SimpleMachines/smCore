<?php

/**
 * Language String Storage
 *
 * @package com.fustrate.admin
 * @author Steven "Fustrate" Hoffman
 * @license MPL 1.1
 * @version 1.0 Alpha 1
 */

namespace smCore\Modules\Admin\Storages;
use smCore\Application, smCore\Module, smCore\Models\Language,
	smCore\Exception;

class LangStrings extends Module\Storage
{
	public function getByPackageId($id)
	{
		$db = Application::get('db');

		$result = $db->query("
			SELECT *
			FROM beta_lang_strings
			WHERE string_package = ?",
			array(
				(int) $id,
			)
		);

		return $result->fetchAll();
	}
}