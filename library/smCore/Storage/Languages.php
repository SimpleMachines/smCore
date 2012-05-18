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
use smCore\Application, smCore\Exception, smCore\Settings, smCore\Model\Language,
	Zend_Cache;

class Languages
{
	protected $_languages = array();
	protected $_runtimeCache = array();

	public function __construct()
	{
		$cache = Application::get('cache');

		// Load the configs
		if (false === $this->_languages = $cache->load('core_languagestorage'))
		{
			$db = Application::get('db');

			$result = $db->query("
				SELECT id_language, language_code, language_name
				FROM beta_languages"
			);

			while ($row = $result->fetch())
				$this->_languages[$row->id_language] = $row;

			$cache->save($this->_languages, 'core_languagestorage');

			// Anything that depends on this should be refreshed
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('core_language'));
		}
	}

	public function getById($id)
	{
		if (!is_int($id))
			throw new Exception('...');

		if (!empty($this->_runtimeCache[$id]))
			return $this->_runtimeCache[$id];

		if (!empty($this->_languages[$id]))
			return $this->_runtimeCache[$id] = new Language($this->_languages[$id]->language_name, $this->_languages[$id]->language_code, $id);

		if (!empty($this->_runtimeCache[Settings::DEFAULT_LANG]))
			return $this->_runtimeCache[Settings::DEFAULT_LANG];

		if (!empty($this->_languages[Settings::DEFAULT_LANG]))
			return $this->_runtimeCache[Settings::DEFAULT_LANG] = new Language($this->_languages[Settings::DEFAULT_LANG]->language_name, $this->_languages[Settings::DEFAULT_LANG]->language_code, Settings::DEFAULT_LANG);

		throw new Exception('There\'s been a bit of a problem loading the default language strings.');
	}

	public function getAll()
	{
		return $this->_languages;
	}
}