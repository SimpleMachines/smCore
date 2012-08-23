<?php

/**
 * smCore Twig Extension
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

namespace smCore\Twig;

use smCore\Application;
use Twig_Error_Runtime, Twig_Extension, Twig_Function_Function, Twig_Filter_Function;

class Extension extends Twig_Extension
{
	protected static $_app;

	public function __construct(Application $app)
	{
		self::$_app = $app;
	}

	/**
	 * Functions built into smCore, available in any template
	 *
	 * @return array An array of functions to add
	 */
	public function getFunctions()
	{
		return array(
			'debug' => new Twig_Function_Function(__CLASS__ . '::function_debug'),
			'dynamic_macro' => new Twig_Function_Function(__CLASS__ . '::function_dynamic_macro', array(
				'is_safe' => array('html'),
			)),
			'lang' => new Twig_Function_Function(__CLASS__ . '::function_lang'),
			'smcMenu' => new Twig_Function_Function(__CLASS__ . '::function_smcMenu'),
			'url_for' => new Twig_Function_Function(__CLASS__ . '::function_url_for', array(
				'is_safe' => array('all'),
			)),
		);
	}

	/**
	 * Filters built into smCore, available in any template
	 *
	 * @return array An array of filters to add
	 */
	public function getFilters()
	{
		return array(
			'hms' => new Twig_Filter_Function(__CLASS__ . '::filter_hms'),
			'split' => new Twig_Filter_Function(__CLASS__ . '::filter_split'),
		);
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'smCore';
	}

	/**
	 * Use a language string from inside a template
	 *
	 * @param string $key The language key to use
	 *
	 * @return string
	 */
	public function function_lang($key)
	{
		return self::$_app['lang']->get($key, array_slice(func_get_args(), 1));
	}

	/**
	 * 
	 *
	 * @param 
	 *
	 * @return 
	 */
	public static function function_smcMenu()
	{
		$menu = self::$_app['menu'];
		$args = func_get_args();

		if (!empty($args))
		{
			if (is_array($args[0]))
			{
				$args = $args[0];
			}

			call_user_func_array(array($menu, 'setActive'), $args);
		}

		return $menu->getMenu();
	}

	/**
	 * Debug a variable from inside a template
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function function_debug($value)
	{
		return var_export($value, true);
	}

	/**
	 * Calls a macro based on a dynamic name, for when we don't exactly know the macro name we'll use.
	 *
	 * @param Twig_Template $template The template to use
	 * @param string        $name     Macro name
	 *
	 * @return string The output of the macro, just like if it were called normally
	 */
	public static function function_dynamic_macro(\Twig_Template $template, $name)
	{
		$arguments = array_slice(func_get_args(), 2);

		static $who_do_you_think_you_are = null;

		if (null === $who_do_you_think_you_are)
		{
			$who_do_you_think_you_are = array_map('strtolower', get_class_methods('Twig_Template') + array('getdebuginfo', 'gettemplatename'));
		}

		if (in_array('get' . strtolower($name), $who_do_you_think_you_are))
		{
			throw new Twig_Error_Runtime('Who do you think you are? Gandalf?');
		}

		if (!method_exists($template, 'get' . $name))
		{
			throw new Twig_Error_Runtime(sprintf('Invalid macro, "%s" is undefined.', $name));
		}

		return call_user_method_array('get' . $name, $template, $arguments);
	}

	/**
	 * Port of Flask's url_for function, to help create URLs on the fly.
	 *
	 * @param string $endpoint        The route to create a URL for
	 * @param array  $query_arguments An array of arguments for the query string, optional
	 *
	 * @return string The url that was created
	 */
	public static function function_url_for($endpoint, array $query_arguments = array())
	{
		if (!empty($endpoint))
		{
			$url = '/' . trim($endpoint, '/') . '/';
		}
		else
		{
			$url = '/';
		}

		$query = '';

		if (!empty($query_arguments))
		{
			$arguments = array();

			foreach ($query_arguments as $key => $value)
			{
				if (is_int($key))
				{
					$arguments[] = $value;
				}
				else
				{
					$arguments[] = urlencode($key) . '=' . urlencode($value);
				}
			}

			$query = '?' . implode(';', $arguments);
		}

		return self::$_app['settings']['url'] . str_replace('//', '/', $url) . $query;
	}

	/**
	 * Splits a value into an array.
	 *
	 * <pre>
	 *  {{ "one,two,three"|split(',') }}
	 *  {# returns array("one", "two", "three") #}
	 *
	 *  {{ "one,two,three,four,five"|split(',', 3) }}
	 *  {# returns array("one", "two", "three,four,five") #}
	 * </pre>
	 *
	 * @author Tyler King (https://github.com/aibot)
	 *
	 * @param string  $value     A value to split
	 * @param string  $delimiter The delimiter to split with
	 * @param int     $limit     Optional limit
	 *
	 * @return string The split string
	 */
	public static function filter_split($value, $delimiter, $limit = null)
	{
		if (!is_string($delimiter) || strlen($delimiter) < 1)
		{
			throw new Twig_Error_Runtime('');
		}

		if (null === $limit)
		{
			return explode($delimiter, $value);
		}

		return explode($delimiter, $value, $limit);
	}

	/**
	 * Format an amount of seconds into HH:MM:SS format, i.e. for system uptime.
	 *
	 * @param int $seconds
	 *
	 * @return string The formatted string
	 */
	public static function filter_hms($seconds)
	{
		$seconds = (int) $seconds;

		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds - $hours * 3600) / 60);
		$seconds = $seconds % 60;

		return sprintf('%d:%2$02d:%3$02d', $hours, $minutes, $seconds);
	}
}