<?php

namespace smCore\Modules\Users\Controllers;
use smCore\Application, smCore\Module, smCore\Request, smCore\Settings;

class Register extends Module\Controller
{
	/**
	 * Load the language strings before dispatch.
	 */
	function preDispatch()
	{
		$module = $this->_getParentModule();
		$module->loadLangPackage();
		Application::get('menu')->setActive('register');
	}

	// @todo: A way to add custom steps
	function register()
	{
		$module = $this->_getParentModule();
		$model = $module->getModel('Register');

		$step = Application::get('input')->get->getAlnum('step') ?: 'start';

		if ($step === 'start')
		{
			// Do nothing.
			$module
				->addView('register')
				->setPageTitle($module->lang('users.titles.register'));
		}
		else if ($step === 'finish')
		{
			$user_data = $model->processRegistration();
			$model->sendActivationEmail($user_data['id']);

			$module
				->addView('activation_sent', array(
					'new_account_email' => $user_data['email'],
					'email_subject' => $module->lang('users.register.email_activate_account.subject'),
				))
				->setPageTitle($module->lang('users.register.titles.activation_sent'));
		}
		else
		{
			$module->throwLangException('users.exceptions.register.invalid_step');
		}
	}

	function activate()
	{
		$module = $this->_getParentModule();
		$model = $module->getModel('Register');

		$model->activateAccount();

		$module
			->addView('activated')
			->setPageTitle($module->lang('users.titles.activated'));
	}

	function resend()
	{
		$module = $this->_getParentModule();

		$module
			->addView('resend_ativation')
			->setPageTitle($module->lang('users.titles.resend_activation'));
	}
}