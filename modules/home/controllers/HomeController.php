<?php

/**
 * smCore platform
 *
 * @package home
 * @author Fustrate
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
 */

namespace smCore\modules\home\controllers;
use smCore\Application, smCore\handlers\ModuleController;

/**
 * Simple home page.
 */
class HomeController extends ModuleController
{
	/**
	 * Construct this controller.
	 */
	public function __construct()
	{
		//
	}

    /**
     * action home
     */
	public function homeAction()
	{
		$module = $this->_parentModule;
		$module->loadTemplates('main');
		$module->addTemplate('home');

		Application::addValueToContext('random_welcome_message', mt_rand(0, 4));
		Application::addValueToContext('page_title', $module->lang('home'));
	}

	public function preDispatch()
	{
		$module = $this->_parentModule;
		$module->loadLanguage('strings.yaml');
	}

	public function postDispatch()
	{
		// TODO: Implement postDispatch() method.
	}
}
