<?php

namespace smCore\TemplateEngine;

class Filters
{
	protected static $_defaults = array(
		'date_format' => 'F j, Y, h:i:s A',
		'time_format' => 'h:i:s A',
		'money_format' => '',
	);

	// @todo: improve this, it's ugly. Also throw an exception.
	protected static function _requireParams($num, $params)
	{
		if (count($params) < $num)
			return false;
	}

	public static function setDefaults(array $defaults)
	{
	}

	public static function filter($value, $filters)
	{
		foreach ($filters as $type => $params)
		{
			if ($type === 'contains')
			{
				self::_requireParams(1, $params);

				if (is_string($value))
				{
					// Cast to a string so it doesn't try it as an int
					$value = strpos($value, (string) $params[0]) !== false;
				}
				else if (is_array($value))
				{
					$value = in_array($params[0], $value);
				}
			}
			else if ($type === 'date')
			{
				$value = date(!empty($params[0]) ? $params[0] : self::$_defaults['date_format'], $value);
			}
			else if ($type === 'default')
			{
				self::_requireParams(1, $params);

				if (empty($value))
					$value = $params[0];
			}
			else if ($type === 'divisibleby')
			{
				self::_requireParams(1, $params);

				$value = ($value % (int) $params[0]) === 0;
			}
			else if ($type === 'empty')
			{
				$value = empty($value);
			}
			else if ($type === 'even')
			{
				$value = is_numeric($value) && $value % 2 === 0;
			}
			else if ($type === 'float')
			{
				$value = round($value, !empty($params[0]) ? (int) $params[0] : 5);
			}
			else if ($type === 'join')
			{
				if (empty($params[0]))
					$params[0] = '';

				$value = implode($params[0], (array) $value);
			}
			else if ($type === 'json')
			{
				$value = json_encode($value);
			}
			else if ($type === 'length')
			{
				$value = is_array($value) ? count($value) : mb_strlen((string) $value);
			}
			else if ($type === 'lower')
			{
				$value = mb_strtolower($value);
			}
			else if ($type === 'ltrim')
			{
				$value = ltrim($value);
			}
			else if ($type === 'money')
			{
			}
			else if ($type === 'nl2br')
			{
				$value = nl2br($value);
			}
			else if ($type === 'null')
			{
				$value = $value === null;
			}
			else if ($type === 'odd')
			{
				$value = is_numeric($value) && $value % 2 === 1;
			}
			else if ($type === 'random')
			{
				if (empty($params[0]))
				{
					$value = $value[array_rand((array) $value)];
				}
			}
			else if ($type === 'rtrim')
			{
				$value = rtrim($value);
			}
			else if ($type === 'stripchars')
			{
			}
			else if ($type === 'striptags')
			{
			}
			else if ($type === 'time')
			{
				$value = date(!empty($params[0]) ? $params[0] : self::$_defaults['time_format'], $value);
			}
			else if ($type === 'trim')
			{
				$value = trim($value);
			}
			else if ($type === 'truncate')
			{
				self::_requireParams(1, $params);

				$params[0] = (int) $params[0];

				if (mb_strlen($value) > $params[0])
					$value = mb_substr($value, 0, $params[0]) . (!empty($params[1]) ? $params[1] : '');
			}
			else if ($type === 'truncatewords')
			{
			}
			else if ($type === 'ucfirst')
			{
				$value = mb_strtoupper($value[0]) . mb_strtolower(mb_substr($value, 1));
			}
			else if ($type === 'ucwords')
			{
				$value = mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
			}
			else if ($type === 'upper')
			{
				$value = mb_strtoupper($value);
			}
			else if ($type === 'urlencode')
			{
				$value = urlencode($value);
			}
			else if ($type === 'wordcount')
			{
				$value = count(preg_split('/\s+/', $value));
			}
			else if ($type === 'wordwrap')
			{
			}
			else if ($type === 'wrap')
			{
			}
			else
			{
				// @todo: custom filters
			}
		}

		return $value;
	}
}