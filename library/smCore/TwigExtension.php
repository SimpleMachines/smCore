<?php

namespace smCore;

use Twig_Extension, Twig_Function_Function, Twig_Filter_Function;

class TwigExtension extends Twig_Extension
{
	public function getFunctions()
	{
		return array(
			'lang' => new Twig_Function_Function(__CLASS__ . '::function_lang'),
			'smcMenu' => new Twig_Function_Function(__CLASS__ . '::function_smcMenu'),
			'smcDebug' => new Twig_Function_Function(__CLASS__ . '::function_smcDebug'),
			'dynamic_macro' => new Twig_Function_Function(__CLASS__ . '::function_dynamic_macro', array(
				'is_safe' => array('html'),
			)),
			'url_for' => new Twig_Function_Function(__CLASS__ . '::function_url_for', array(
				'is_safe' => array('all'),
			)),
		);
	}

	public function getFilters()
	{
		return array(
			'split' => new Twig_Filter_Function(__CLASS__ . '::filter_split'),
			'hms' => new Twig_Filter_Function(__CLASS__ . '::filter_hms'),
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

	public static function function_lang()
	{
		$args = func_get_args();

		if (count($args) < 1)
		{
			return '';
		}

		return Application::get('lang')->get($args[0], array_slice($args, 1));
	}

	public static function function_smcMenu()
	{
		$menu = Application::get('menu');
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

	public static function function_smcDebug($value)
	{
		return var_export($value, true);
	}

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
			throw new \Twig_Error_Runtime('Who do you think you are? Gandalf?');
		}

		if (!method_exists($template, 'get' . $name))
		{
			throw new \Twig_Error_Runtime(sprintf('Dynamic macro calls only work on macros defined in the same file. Macro "%s" is undefined.', $name));
		}

		return call_user_method_array('get' . $name, $template, $arguments);
	}

	public static function function_url_for($endpoint, array $query_arguments = array())
	{
		if (!empty($endpoint))
		{
			$url = Settings::URL . '/' . trim($endpoint, '/') . '/';
		}
		else
		{
			$url = Settings::URL . '/';
		}

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

			return $url . '?' . implode(';', $arguments);
		}

		return $url;
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
		if (strlen($delimiter) < 1)
		{
			throw new Twig_Error_Runtime();
		}

		if (null === $limit)
		{
			return explode($delimiter, $value);
		}

		return explode($delimiter, $value, $limit);
	}

	public static function filter_hms($value)
	{
		$value = (int) $value;

		$hours = floor($value / 3600);
		$minutes = floor(($value - $hours * 3600) / 60);
		$seconds = $value % 60;

		return $hours . ':' . $minutes . ':' . $seconds;
	}
}