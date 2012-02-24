<?php

namespace smCore\Modules\Users\Controllers;
use smCore\Application, smCore\Module, smCore\Request, smCore\Settings;

class Main extends Module\Controller
{
	/**
	 * Load the language strings before dispatch.
	 */
	function preDispatch()
	{
		$module = $this->_getParentModule();
		$module->loadLanguage('strings.yaml');
	}

	/**
	 * Show the login screen.
	 */
	function login()
	{
		$module = $this->_getParentModule();
		$module->loadTemplates('main');
		$module->addTemplate('login');

		Application::$context['page_title'] = $module->lang('login_title');
	}

	/**
	 * Validate a login. If it doesn't validate, send them back to the login screen.
	 */
	function loginSubmit()
	{
		$module = $this->_getParentModule();

		try
		{
			$module->getModel('Main')->processLogin();
		}
		catch (\Exception $exception)
		{
			Application::$context['login_error'] = $exception->getMessage();
			$this->login();
			return;
		}

		$module->createEvent('post_successful_login')->fire();
/*
		if (isset($_SESSION['login_redirect']))
			$url = $_SESSION['login_redirect'];
		else */
			$url = Settings::URL . '/';

		Application::get('response')
			->addHeader(302)
			->addHeader("Location: $url")
			->sendOutput();
	}

	/**
	 * Log out and redirect to the home page.
	 */
	function logout()
	{
		$this->_getParentModule()->getModel('Main')->logout();

		Application::get('response')
			->addHeader(302)
			->addHeader("Location: " . Settings::URL)
			->sendOutput();
	}

	/**
	 * Show the terms of service & privacy policy
	 */
	function terms()
	{
		$module = $this->_getParentModule();

		$module->loadTemplates('main');
		$module->addTemplate('terms');

		Application::$context['page_title'] = $module->lang('terms.title');
	}
}