<?php

namespace smCore\Modules\Users\Models;
use smCore\Application, smCore\Module;

class User extends Module\Model
{
	protected $_id;
	protected $_displayname;
	protected $_username;
	protected $_email;
	protected $_registered;

	public function getId()
	{
		return $this->_id;
	}

	public function setId($id)
	{
		$this->_id = (int) $id;

		return $this;
	}

	public function getDisplayName()
	{
		return $this->_displayname;
	}

	public function setDisplayName($name)
	{
		$this->_displayname = $name;

		return $this;
	}

	public function getUsername()
	{
		return $this->_username;
	}

	public function setUsername($username)
	{
		$this->_username = $username;

		return $this;
	}

	public function getEmail()
	{
		return $this->_email;
	}

	public function setEmail($email)
	{
		$this->_email = $email;

		return $this;
	}

	public function getRegistered()
	{
		return $this->_registered;
	}

	public function setRegistered($registered)
	{
		$this->_registered = (int) $registered;

		return $this;
	}

	public function createFromRow($data)
	{
		$this
			->setId($data->id_user)
			->setDisplayName($data->user_display_name)
			->setUsername($data->user_login)
			->setEmail($data->user_email)
			->setRegistered($data->user_registered);

		return $this;
	}
}