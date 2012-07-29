<?php

/**
 * smCore Exception Handler
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

namespace smCore\Handlers;

use smCore\Application;
use Twig_Error;

class Exception
{
	public function __construct()
	{
		set_exception_handler(array($this, 'handle'));
	}

	/**
	 * Custom exception handler
	 *
	 * @param Exception $exception The exception that needs to be handled.
	 */
	public function handle($exception)
	{
		$show_trace = Application::get('user', false) !== null && Application::get('user')->hasPermission('org.smcore.core.is_admin');

		// We can't show a nice screen if the exception came from the template engine or the theme hasn't been loaded
		if (!($exception instanceof Twig_Error) && Application::$twig !== null)
		{
			Application::$twig->display('error.html', array(
				'error_message' => $exception->getMessage(),
				'error_trace' => print_r($exception->getTrace(), true),
				'show_trace' => $show_trace,
			));
		}
		else
		{
			echo 'Uncaught exception error:<hr /><pre>' . $exception->getMessage() . '</pre>';

			if ($show_trace)
			{
				echo '<br /><pre>' . print_r($exception->getTrace(), true) . '</pre>';
			}
		}

		die();
	}
}