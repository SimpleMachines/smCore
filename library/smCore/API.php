<?php

/**
 * smCore API Class
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

// @todo: should this be part of Response instead?

namespace smCore;

use Symfony\Component\Yaml\Yaml;

class API
{
	protected static $data = array();
	public static $format = 'json';

	// public static void main(String[] args) :D
	public static function run()
	{
		$db = Application::get('db');

		$namespace = '???';
		$name = '???';

		// There's no default API route - you need to tell me what you want!
		if ($namespace === null || $name === null)
		{
			throw new Exception('exceptions.api.invalid_request');
		}

		$result = $db->query("
			SELECT *
			FROM {db_prefix}api_hooks
			WHERE api_hook_namespace = {string:namespace}
				AND api_hook_name = {string:name}
				AND api_hook_enabled = 1",
			array(
				'namespace' => $namespace,
				'name' => $name,
			)
		);

		if ($result->rowCount() > 0)
		{
			$hook = $result->fetch();

			$callback = explode(',', $hook->api_hook_callback);

			if (is_callable($callback))
			{
				self::$data = call_user_func($callback);
			}
		}
		else
		{
			self::$data = array('error' => Application::get('lang')->get('exceptions.api.invalid_route'));
		}

		self::output();
	}

	protected static function output()
	{
		switch (self::$format)
		{
			case 'json':
				self::$data = json_encode(self::$data);
				//header('Content-Type: application/json');
				//header('Content-Length: ' . mb_strlen(self::$data));
				echo self::$data;
				break;
			case 'xml':
				// !!! Unsupported right now
				break;
			case 'yaml':
				self::$data = Yaml::dump(self::$data);
				header('Content-Type: text/x-yaml');
				header('Content-Length: ' . mb_strlen(self::$data));
				echo self::$data;
				break;
		}

		die();
	}

	public static function userSearch()
	{
		$db = Application::get('db');
		$input = Application::get('input');

		$value = $input->get->getAlpha('term');
		$users = array();

		$result = $db->query("
			SELECT id_user, user_display_name
			FROM {db_prefix}users
			WHERE user_display_name LIKE {string:pattern}",
			array(
				'pattern' => '%' . $value . '%',
			)
		);

		if ($result->rowCount() > 0)
		{
			while ($row = $result->fetch())
			{
				$users[] = array(
					'id' => $row->id_user,
					'label' => $row->user_display_name,
					'value' => $row->user_display_name,
				);
			}
		}

		return $users;
	}
}