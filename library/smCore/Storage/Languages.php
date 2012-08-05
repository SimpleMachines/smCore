<?php

/**
 * smCore Languages Storage
 *
 * @package smCore
 * @author smCore Dev Team
 * @license MPL 1.1
 * @version 1.0 Alpha
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 */

namespace smCore\Storage;

use smCore\Application, smCore\Exception, smCore\Settings, smCore\Model\Language;

class Languages
{
	protected $_languages = array();
	protected $_runtimeCache = array();

	public function __construct()
	{
		$cache = Application::get('cache');
		$this->_languages = $cache->load('core_languagestorage');

		// Load the configs
		if (!is_array($this->_languages))
		{
			$db = Application::get('db');

			$result = $db->query("
				SELECT id_language, language_code, language_name
				FROM {db_prefix}languages"
			);

			while ($row = $result->fetch())
			{
				$this->_languages[$row['language_code']] = $row;
			}

			$cache->save('core_languagestorage', $this->_languages);

			// @todo: cache tags
			// Anything that depends on this should be refreshed
			// $cache->clean('core_language');
		}
	}

	public function getByCode($code)
	{
		if (empty($code))
		{
			throw new Exception('...');
		}

		if (!empty($this->_runtimeCache[$code]))
		{
			return $this->_runtimeCache[$code];
		}

		if (!empty($this->_languages[$code]))
		{
			return $this->_runtimeCache[$code] = new Language($this->_languages[$code]['language_name'], $code, $this->_languages[$code]['id_language']);
		}

		if ($code !== Settings::DEFAULT_LANG)
		{
			return $this->getByCode(Settings::DEFAULT_LANG);
		}

		throw new Exception('There\'s been a bit of a problem loading the language strings. ('.$code.')');
	}

	public function getAll()
	{
		return $this->_languages;
	}
}