<?php

/**
 * smCore Role Model
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

namespace smCore\Model;

use smCore\Application, smCore\Storage\Factory as StorageFactory;

class Role extends AbstractModel
{
	// Data for each individual user loaded
	protected $_id;
	protected $_name;
	protected $_inherits;
	protected $_permissions;

	const RECURSION_LIMIT = 10;

	public function __construct(Application $app, $id, $name, $inherits = 0, array $permissions = array())
	{
		parent::__construct($app);

		$this->_id = (int) $id;
		$this->_name = $name;

		if ($inherits > 0)
		{
			$this->_inherits = $inherits;
		}

		$this->_permissions = $permissions;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function hasPermission($name, $recursion = 0)
	{
		// Recursive permissions? Not on my watch.
		if ($recursion > self::RECURSION_LIMIT)
		{
			return false;
		}

		if (isset($this->_permissions[$name]))
		{
			return $this->_permissions[$name];
		}

		if ($this->_inherits !== null)
		{
			$inherits = $this->_app['storage_factory']->factory('Roles')->getRoleById($this->_inherits)->hasPermission($name, $recursion + 1);

			if ($inherits !== null)
			{
				return $inherits;
			}
		}
	}

	public function addPermission($name, $state)
	{
	}

	public function removePermission($name)
	{
	}
}