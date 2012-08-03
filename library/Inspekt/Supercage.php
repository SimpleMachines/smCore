<?php
/**
 * Inspekt Supercage
 *
 * @author Ed Finkler <coj@funkatron.com>
 *
 * @package Inspekt
 */

// Require main Inspekt class
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Inspekt.php';

// Require the Cage class
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Inspekt/Cage.php';

/**
 * The Supercage object wraps ALL of the superglobals
 *
 * @package Inspekt
 *
 */
Class Inspekt_Supercage
{
	/**
	 * The cages
	 *
	 * @var Inspekt_Cage
	 */
	var $get;
	var $post;
	var $cookie;
	var $env;
	var $files;
	var $session;
	var $server;

	/**
	 * Enter description here...
	 *
	 * @return Inspekt_Supercage
	 */
	public function Inspekt_Supercage(){}

	/**
	 * Enter description here...
	 *
	 * @param string  $config_file
	 * @param boolean $strict
	 * @return Inspekt_Supercage
	 */
	static public function Factory($config_file = null, $strict = true)
	{
		$sc	= new Inspekt_Supercage();
		$sc->_makeCages($config_file, $strict);

		// eliminate the $_REQUEST superglobal
		if ($strict)
			$_REQUEST = null;

		return $sc;
	}

	/**
	 * Enter description here...
	 *
	 * @see Inspekt_Supercage::Factory()
	 * @param string  $config_file
	 * @param boolean $strict
	 */
	protected function _makeCages($config_file = null, $strict = true)
	{
		$this->get = Inspekt::makeGetCage($config_file, $strict);
		$this->post = Inspekt::makePostCage($config_file, $strict);
		$this->cookie = Inspekt::makeCookieCage($config_file, $strict);
		$this->env = Inspekt::makeEnvCage($config_file, $strict);
		$this->files = Inspekt::makeFilesCage($config_file, $strict);
		// $this->session = Inspekt::makeSessionCage($config_file, $strict);
		$this->server = Inspekt::makeServerCage($config_file, $strict);
	}

	public function addAccessor($name)
	{
		$this->get->addAccessor($name);
		$this->post->addAccessor($name);
		$this->cookie->addAccessor($name);
		$this->env->addAccessor($name);
		$this->files->addAccessor($name);
		// $this->session->addAccessor($name);
		$this->server->addAccessor($name);
	}
}