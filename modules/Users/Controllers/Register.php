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
		$module->loadLanguage('strings.yaml');
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
			$module->loadTemplates('register');
			$module->addTemplate('register_main');

			Application::$context['page_title'] = $module->lang('register.titles.main');
		}
		else if ($step === 'finish')
		{
			$user_data = $model->processRegistration();
			$model->sendActivationEmail($user_data['id']);

			$module->loadTemplates('register');
			$module->addTemplate('register_activation_sent');

			Application::$context['new_account_email'] = $user_data['email'];
			Application::$context['email_subject'] = $module->lang('register.email_activate_account.subject');

			Application::$context['page_title'] = $module->lang('register.titles.activation_sent');
		}
		else
		{
			$module->throwLangException('exceptions.register.invalid_step');
		}
	}

	function activate()
	{
		$module = $this->_getParentModule();
		$model = $module->getModel('Register');

		$model->activateAccount();

		$module->loadTemplates('register');
		$module->addTemplate('register_activated');

		Application::$context['page_title'] = $module->lang('register.titles.activated');
	}

	function resend()
	{
		$module = $this->_getParentModule();

		$module->loadTemplates('register');
		$module->addTemplate('register_resend');

		Application::$context['page_title'] = $module->lang('register.titles.resend');
	}
}