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

use smCore\Container;
use Twig_Error;

class Exception
{
	protected $_container;

	public function __construct(Container $container)
	{
		$this->_container = $container;

		set_exception_handler(array($this, 'handle'));
	}

	/**
	 * Custom exception handler
	 *
	 * @param Exception $exception The exception that needs to be handled.
	 */
	public function handle($exception)
	{
		if ($exception instanceof \smCore\Exception)
		{
			$message = $exception->getRawMessage();

			if (!empty($message) && isset($this->_container['lang']) && null !== $lang = $this->_container['lang'])
			{
				// If it's an array, we have replacements to send along
				if (is_array($message))
				{
					$message = $lang->get($message[0], array_slice($message, 1));
				}
				else
				{
					$message = $lang->get($message);
				}
			}
			else if (is_array($message))
			{
				$message = var_export($message, true);
			}
		}
		else
		{
			$message = $exception->getMessage();
		}

		$this->_container['sending_output'] = null;

		$show_trace = isset($this->_container['user']) && $this->_container['user']->hasPermission('org.smcore.core.is_admin');

		$response = $this->_container['response'];

		// We can't show a nice screen if the exception came from the template engine or the theme hasn't been loaded
		if (!($exception instanceof Twig_Error) && isset($this->_container['twig']) && null !== $twig = $this->_container['twig'])
		{
			$response->setBody($twig->render('error.html', array(
				'error_message' => $message,
				'error_trace' => print_r($exception->getTrace(), true),
				'show_trace' => $show_trace,
			)));
		}
		else
		{
			$response->setBody('Uncaught exception error:<hr /><pre>' . $message . '</pre>' . ($show_trace ? '<br /><pre>' . print_r($exception->getTrace(), true) . '</pre>' : ''));
		}

		if ($exception->getCode() !== 0)
		{
			$response->addHeader($exception->getCode());
		}

		$response->sendOutput();
	}
}