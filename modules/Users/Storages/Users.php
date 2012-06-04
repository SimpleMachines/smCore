<?php

/**
 * Users Storage
 *
 * @package com.fustrate.services
 * @author Steven "Fustrate" Hoffman
 * @license MPL 1.1
 * @version 1.0 Alpha 1
 */

namespace smCore\Modules\Users\Storages;
use smCore\Application, smCore\Module, smCore\Modules\Users\Models\User,
	Exception;

class Users extends Module\Storage
{
	public function getPage($num = 10, $page = 1, $where = null)
	{
		$db = Application::get('db');

		if ($page < 1)
			$page = 1;

		$select = $db->select()
			->from(
				array('u' => 'beta_users'),
				array('*')
			)
			->limitPage($page, $num);

		if (!empty($where))
			$select->where($where);

		$result = $db->query($select);

		$users = array();

		if ($result->rowCount() > 0)
		{
			while ($row = $result->fetch())
				$users[$row->id_user] = $this->_createUserModel($row);
		}

		return $users;
	}

	protected function _createUserModel($data)
	{
		return $this
			->_getParentModule()
			->getModel('User')
			->createFromRow($data);
	}
}