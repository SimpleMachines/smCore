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
		$module->loadLangPackage();
	}

	/**
	 * Show the login screen.
	 */
	function login($error = null)
	{
		$module = $this->_getParentModule();
		Application::get('menu')->setActive('login');

		$module
			->addView('login', array(
				'error' => $error,
				'username' => Application::get('input')->post->getRaw('username') ?: '',
				'cookie_time' => Application::get('input')->post->getInt('cookie_time'),
			))
			->setPageTitle($module->lang('users.titles.login'));
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
			$this->login($exception->getMessage());
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

		$module
			->addView('terms')
			->setPageTitle($module->lang('users.titles.terms'));
	}
}