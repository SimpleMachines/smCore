<?php

namespace smCore\TemplateEngine;

class Errors
{
	protected static $prior = null;
	protected static $retain = 0;
	protected static $files = array();

	public function __construct()
	{
		self::register();
	}

	public function __destruct()
	{
		self::restore();
	}

	public static function register()
	{
		if (self::$retain++ > 0)
			return;

		self::$prior = set_error_handler(array(__CLASS__, 'handler'));
	}

	public static function restore()
	{
		if (--self::$retain > 0)
			return;

		restore_error_handler();
	}

	public static function reset()
	{
		self::$files = array();
	}

	public static function remap($file, $line)
	{
		list ($trace) = debug_backtrace();

		// Make sure we have the path is absolute.
		$from_file = realpath($trace['file']);
		$from_line = (int) $trace['line'];

		self::$files[$from_file][$from_line] = array(
			'file' => $file,
			'line' => (int) $line,
		);
	}

	public static function handler($errno, $errstr, $file, $line, $ctx)
	{
		$from_file = realpath($file);
		if (isset(self::$files[$from_file]))
		{
			$mappings = self::$files[$from_file];

			// Put the highest first so we can stop at the right one.
			krsort($mappings);
			foreach ($mappings as $from_line => $mapping)
			{
				// The first one we hit that's lower is the correct one (higher won't be.)
				if ($from_line <= $line)
				{
					$file = $mapping['file'];
					$line = $mapping['line'] + ($line - $from_line);
				}
			}

			if (self::$prior === null)
				return self::defaultHandler($errno, $errstr, $file, $line);
		}

		// Call the old error handler with the updated file and line.
		if (self::$prior !== null)
			return call_user_func(self::$prior, $errno, $errstr, $file, $line, $ctx);

		return false;
	}

	protected static function defaultHandler($errno, $errstr, $file, $line)
	{
		// We're only going to be custom if there was no prior...
		// This shouldn't ever really happen anyway.
		$setting = @ini_get('display_errors');
		if ($setting == 0 && $setting !== 'stderr')
			return true;

		// We need some way to get the pretty error, even if there are new errors later.
		$consts = get_defined_constants();
		$error_type = $errno;
		foreach ($consts as $name => $value)
		{
			if (strpos($name, 'E_') === 0 && $value === $errno)
				$error_type = $name;
		}

		$fp = fopen($setting === 'stderr' ? 'php://stderr' : 'php://output', 'wt');
		if ($fp)
		{
			fwrite($fp, $error_type . ': ' . $errstr . ' in ' . $file . ' on line ' . $line . "\n");
			fclose($fp);
		}

		if ($errno % 255 == E_ERROR)
			die;

		return true;
	}
}