<?php

/**
 * smCore platform
 *
 * @package smCore
 * @author Norv
 * @license MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * @version 1.0 alpha
 *
 */

namespace smCore;
use smCore\Application, smCore\events\Event, smCore\model\Role, smCore\model\Permission, smCore\model\Group,
	smCore\FatalException, smCore\model\User;

/**
 * AccessManager class is the point of entry of access and permission checks to resources.
 *
 * Permissions model is very different in the ACL-based system and SMF system.
 * What can be called a "global permission" applies to actions, certain items (such as own vs any posts/comments),
 * various resources (such as posts, files, profile avatars etc).
 * Permissions profiles in SMF 2 further define local permissions taking precendence where applicable.
 * Post-count groups are also treated differently by the forum.
 * Certain groups are treated differently, again, such as 'local moderators'.
 *
 * The impedance mismatch is too significant to consider refactoring SMF 3.0 at this level, while an ACL-based
 * interface is appropriate for future versions.
 *
 * For compatibility with SMF, the purpose of this class is now meant to simply define the same consistent
 * interface for an ACL based system it was doing from the start, in the measure of possible, however this
 * interface won't be used for a while.
 * Instead, the class will gather the applicable methods or functions it needs to make available to the forum module.
 * The additional purpose of this class and helpers (Permission, Role/Group, Action) is to design and implement
 * in time adapters from the forum module model, to the ACL model.
 */
class AccessManager
{
	private $_resources = array();
	private $_roles = array();
	private $_permissions = array();

	protected static $_instance = null;

	/**
	 * Creates and initializes the AccessManager instance.
	 */
	protected function __construct()
	{
			// $this->_loadRoles();
			// $this->_loadPermissions();
	}

	/**
	 * Load the roles defined for the application.
	 * Admins are a special case, they will have all permissions no matter what.
	 * Guests are added in particular too.
	 */
	protected function _loadRoles()
	{
		// Instead of roles, this method will now load the groups as defined by membergroups table.
		// Consider the types of 'membergroups' (special groups such as admin or the forum-specific local mod)
		// and respectively the forum-specific 'post-count' groups.
		Group::loadGroups();

		// Admins are a special case
		$this->allow('admin');
		$this->deny('admin', 'role', 'guest');

		// Allow modules/plugins to add their own roles
		$event = new Event(null, 'core.roles_loaded', &$this->_acl);
		$event->fire();
	}

	/**
	 * Loads the permissions and corresponding roles. It will basically not know about resources... at this time.
	 */
	protected function _loadPermissions()
	{
		$permissions = Permission::loadPermissions();

		// Implementation removed, therefore this method cannot be used at this time.
		throw new FatalException('core_not_implemented_method');

	}

	/**
	 * Add a role.
	 *
	 * @param $role
	 * @param array $parents
	 */
	public function addRole($role, $parents = array())
	{
		// Implementation removed.
		throw new FatalException('core_not_implemented_method');
	}

	/**
	 * Add a resource to the ACL.
	 *
	 * @param $resource
	 */
	public function addResource($resource)
	{
		if (!empty($resource) && !in_array($resource, $this->_resources))
		{
			$this->_resources[] = $resource;
		}
	}

	/**
	 * Allow $role the permission $permission to $resource.
	 * If $resource is null, it will be interpreted as allowing $role to have $permission on all. (applicable)
	 *
	 * @param $role
	 * @param $resource
	 * @param $permission
	 */
	public function allow($role, $resource = null, $permission = null)
	{
		// Set $role as allowed to have $permission on $resource.
		// i.e. it can be used by the forum to set local permissions on boards.

		// Implementation removed.
		throw new FatalException('core_not_implemented_method');
	}

	/**
	 * Deny $role the permission $permission to $resource.
	 * If $resource is null, it will be interpreted as denying $role to have $permission on all. (applicable)
	 *
	 * @param $role
	 * @param $resource
	 * @param $permission
	 */
	public function deny($role, $resource = null, $permission = null)
	{
		// Set $role as denied $permission on $resource.
		// It can be used by the forum (and other modules, this isn't specific) to set 'deny'-type permissions
		//

		// Implementation removed.
		throw new FatalException('core_not_implemented_method');
	}

	/**
	 * Whether $role is allowed the permission $permission to $resource.
	 *
	 * @param null $role
	 * @param null $resource
	 * @param null $permission
	 * @return bool
	 */
	public function isAllowed($role = null, $resource = null, $permission = null)
	{
		// This method cannot be used at this time.
		// Implementation removed.
        return true;
		throw new FatalException('core_not_implemented_method');

	}

	/**
	 * Special Guest role. Whoever uses it should refer to it by AccessManager::getGuestRole().
	 *
	 * @return mixed
	 */
	function getGuestRole()
	{
		return $this->_roles[0];
	}

	/**
	 * Check if the user is allowed to do $permission.
	 *
	 * For SMF, this is allowedTo() from Security.php, which must be loaded at this time
	 * (right now it's loaded as ForumSecurity.php, for dev/debugging purpose)
	 *
	 * @param string $permission
	 * @param null $resources
	 * @param bool $useForumPermissions
	 * @internal param null $tags
	 * @internal param array $boards = null
	 * @return bool if the user can do the permission
	 */
	public function allowedTo($permission, $resources = null, $useForumPermissions = false)
	{
		// Nothing?
		if (empty($permission))
			return true;

		// For SMF, this is allowedTo() from Security.php
		// We're going to just use it for the moment.
		if ($useForumPermissions && function_exists('allowedTo'))
			return allowedTo($permission, $resources);

		if (User::getCurrentUser()->isAdmin())
			return true;

		return false;
	}

	// Singleton - no cloning.
	protected function __clone(){}

	/**
	 * Return the AccessManager instance.
	 *
	 * @static
	 * @return \smCore\AccessManager
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();

		return self::$_instance;
	}
}

