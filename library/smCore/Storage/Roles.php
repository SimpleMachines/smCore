<?php

/**
 * smCore Roles Storage
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

use smCore\Model\Role;

class Roles extends AbstractStorage
{
	protected $_loaded_roles;

	const ROLE_GUEST = 0;
	const ROLE_ADMIN = 1;
	const ROLE_MEMBER = 2;

	public function getRoles()
	{
		if ($this->_loaded_roles !== null)
		{
			return $this->_loaded_roles;
		}

		$cache = $this->_app['cache'];

		if (false === $this->_loaded_roles = $cache->load('core_roles'))
		{
			$db = $this->_app['db'];

			$this->_loaded_roles = array();

			$roles_query = $db->query("
				SELECT *
				FROM {db_prefix}roles");

			// If there aren't any roles, don't bother doing anything
			if ($roles_query->rowCount() > 0)
			{
				while ($role = $roles_query->fetch())
				{
					$this->_loaded_roles[$role['id_role']] = array(
						'id' => $role['id_role'],
						'title' => $role['role_title'],
						'permissions' => !empty($role['role_permission']) ? array('org.smcore.auth.' . $role['role_permission'] => true) : array(),
						'inherits' => $role['role_inherits'],
					);
				}

				$permissions_query = $db->query("
					SELECT *
					FROM {db_prefix}permissions
					WHERE permission_state != 0
						AND permission_role IN {array_int:roles}",
					array(
						'roles' => array_keys($this->_loaded_roles)
					)
				);

				if ($permissions_query->rowCount() > 0)
				{
					while ($permission = $permissions_query->fetch())
					{
						$this->_loaded_roles[$permission['permission_role']]['permissions'][$permission['permission_namespace'] . '.' . $permission['permission_name']] = $permission['permission_state'] == "1";
					}
				}

				foreach ($this->_loaded_roles as $id => $role)
				{
					$this->_loaded_roles[$id] = new Role($this->_app, $id, $role['title'], $role['inherits'], $role['permissions']);
				}
			}

			$cache->save('core_roles', $this->_loaded_roles);
		}

		return $this->_loaded_roles;
	}

	public function setRolePermission($role, $namespace, $name, $state)
	{
	}

	public function unsetRolePermission($role, $namespace, $name)
	{
	}

	public function getRoleById($id)
	{
		$roles = $this->getRoles();

		if (array_key_exists($id, $roles))
		{
			return $roles[$id];
		}
	}
}