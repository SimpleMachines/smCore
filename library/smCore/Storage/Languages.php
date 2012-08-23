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

use smCore\Exception, smCore\Model\Language;

class Languages extends AbstractStorage
{
	protected $_languages = array();
	protected $_runtimeCache = array();

	public function __construct($app)
	{
		parent::__construct($app);

		$cache = $this->_app['cache'];

		// Load the configs
		if (false === $this->_languages = $cache->load('core_languagestorage'))
		{
			$db = $this->_app['db'];

			$result = $db->query("
				SELECT id_language, language_code, language_name
				FROM {db_prefix}languages"
			);

			while ($row = $result->fetch())
			{
				$this->_languages[$row['language_code']] = $row;
			}

			$cache->save('core_languagestorage', $this->_languages);
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
			return $this->_runtimeCache[$code] = new Language($this->_app, $this->_languages[$code]['language_name'], $code, $this->_languages[$code]['id_language']);
		}

		$settings = $this->_app['settings'];

		if ($code !== $settings['default_lang'])
		{
			return $this->getByCode($settings['default_lang']);
		}

		throw new Exception(sprintf('There\'s been a bit of a problem loading the language "%s".', $code));
	}

	public function getAll()
	{
		return $this->_languages;
	}
}