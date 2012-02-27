<?php

/**
 * Database layer
 *
 * @package database
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines and contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.0 Alpha 1
 */

namespace database;

use smCore\Exception, smCore\Configuration, Settings, smCore\Language;

/**
 * This file is the main MySQL database adapter.
 *
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 Alpha 1
 */

class DefaultMysqlAdapter extends DatabaseAdapter
{
	private $_connections = null;

	/**
	 * Initiate a database connection.
	 *
	 * @param $db_server
	 * @param $db_name
	 * @param $db_user
	 * @param $db_passwd
	 * @param $db_prefix
	 * @param array $db_options
	 * @return null|resource
	 */
	function initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_options = array())
	{
		// clean this up

		if (empty($db_options['type']))
			$db_options['type'] = 'write';

		// type = 'write' or 'read' or som'thing.
		if (empty($this->_connections))
			$this->_connections = array();

		// we already have it and want to use it?
		if (key_exists($db_options['type'], $this->_connections) && !empty($this->_connections[$db_options['type']]))
			$connection = $this->_connections[$db_options['type']];
		else
		{
			$connection = @mysql_connect($db_server, $db_user, $db_passwd);
			$this->_connections[$db_options['type']] = $connection;
		}

		// Not lucky today
		if (!$connection)
		{
			if (!empty($db_options['non_fatal']))
				return null;
			else
				throw new Exception('core_database_connection_failed');
		}

		// Select the database, unless told not to
		if (empty($db_options['dont_select_db']) && !@mysql_select_db($db_name, $connection) && empty($db_options['non_fatal']))
			throw new Exception('core_database_connection_failed');

		// Initialize the prefix
		$this->_db_prefix = $db_prefix;

		// This makes it possible to have SMF automatically change the sql_mode and autocommit if needed.
		if (key_exists('mysql_set_mode', $db_options) && $db_options['mysql_set_mode'] === true)
			$this->query('', 'SET sql_mode = \'\', AUTOCOMMIT = 1',
				array(),
				false
			);

		// We know we're UTF-8 types, just do it.
		// We're not going to like this later, though.
		$this->query('', 'SET NAMES utf8',
				array(),
				false
			);

		return $connection;
	}

	/**
	 * @param $db_prefix
	 * @param $db_name
	 */
	function fix_prefix(&$db_prefix, $db_name)
	{
		$db_prefix = is_numeric(substr($db_prefix, 0, 1)) ? $db_name . '.' . $db_prefix : '`' . $db_name . '`.' . $db_prefix;
		$this->_db_prefix = $db_prefix;
	}

	/**
	 * @param $matches
	 * @return array|string
	 */
	function replacement_callback($matches)
	{
		global $db_callback;

		list ($values, $connection) = $db_callback;

		if ($matches[1] === 'db_prefix')
			return $this->_db_prefix;

		// if ($matches[1] === 'query_see_board')
		//	return $user_info['query_see_board'];

		// if ($matches[1] === 'query_wanna_see_board')
		//	return $user_info['query_wanna_see_board'];

		if (!isset($matches[2]))
			$this->error_backtrace('Invalid value inserted or no type specified.', '', E_USER_ERROR, __FILE__, __LINE__);

		if (!isset($values[$matches[2]]))
			$this->error_backtrace('The database value you\'re trying to insert does not exist: ' . htmlspecialchars($matches[2]), '', E_USER_ERROR, __FILE__, __LINE__);

		$replacement = $values[$matches[2]];

		switch ($matches[1])
		{
			case 'int':
				if (!is_numeric($replacement) || (string) $replacement !== (string) (int) $replacement)
					$this->error_backtrace('Wrong value type sent to the database. Integer expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (int) $replacement;
				break;

			case 'string':
			case 'text':
				return sprintf('\'%1$s\'', mysql_real_escape_string($replacement, $connection));
				break;

			case 'array_int':
				if (is_array($replacement))
				{
					if (empty($replacement))
						$this->error_backtrace('Database error, given array of integer values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
					{
						if (!is_numeric($value) || (string) $value !== (string) (int) $value)
							$this->error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

						$replacement[$key] = (string) (int) $value;
					}

					return implode(', ', $replacement);
				}
				else
					$this->error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

				break;

			case 'array_string':
				if (is_array($replacement))
				{
					if (empty($replacement))
						$this->error_backtrace('Database error, given array of string values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
						$replacement[$key] = sprintf('\'%1$s\'', mysql_real_escape_string($value, $connection));

					return implode(', ', $replacement);
				}
				else
					$this->error_backtrace('Wrong value type sent to the database. Array of strings expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				break;

			case 'date':
				if (preg_match('~^(\d{4})-([0-1]?\d)-([0-3]?\d)$~', $replacement, $date_matches) === 1)
					return sprintf('\'%04d-%02d-%02d\'', $date_matches[1], $date_matches[2], $date_matches[3]);
				else
					$this->error_backtrace('Wrong value type sent to the database. Date expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				break;

			case 'float':
				if (!is_numeric($replacement))
					$this->error_backtrace('Wrong value type sent to the database. Floating point number expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (float) $replacement;
				break;

			case 'identifier':
				// Backticks inside identifiers are supported as of MySQL 4.1. We don't need them for SMF.
				return '`' . strtr($replacement, array('`' => '', '.' => '')) . '`';
				break;

			case 'raw':
				return $replacement;
				break;

			default:
				$this->error_backtrace('Undefined type used in the database query. (' . $matches[1] . ':' . $matches[2] . ')', '', false, __FILE__, __LINE__);
				break;
		}
	}

	/**
	 * @param $db_string
	 * @param $db_values
	 * @param null $connection
	 * @return mixed
	 */
	function quote($db_string, $db_values, $connection = null)
	{
		global $db_callback;

		// Only bother if there's something to replace.
		if (strpos($db_string, '{') !== false)
		{
			// This is needed by the callback function.
			$db_callback = array($db_values, $connection === null ? $this->getConnection() : $connection);

			// Do the quoting and escaping
			$db_string = preg_replace_callback('~{([a-z_]+)(?::([a-zA-Z0-9_-]+))?}~', array($this, 'replacement__callback'), $db_string);

			// Clear this global variable.
			$db_callback = array();
		}

		return $db_string;
	}

	/**
	 * @param $identifier
	 * @param $db_string
	 * @param array $db_values
	 * @param null $connection
	 * @return \resource
	 */
	function query($db_string, $db_values = array(), $connection = null, $identifier = '')
	{
		global $time_start;
		global $db_unbuffered, $db_callback;

		// Comments that are allowed in a query are preg_removed.
		static $allowed_comments_from = array(
		    '~\s+~s',
		    '~/\*!40001 SQL_NO_CACHE \*/~',
		    '~/\*!40000 USE INDEX \([A-Za-z\_]+?\) \*/~',
		    '~/\*!40100 ON DUPLICATE KEY UPDATE id_msg = \d+ \*/~',
		);
		static $allowed_comments_to = array(
		    ' ',
		    '',
		    '',
		    '',
		);

		// Decide which connection to use.
		$connection = $connection === null ? $this->getConnection() : $connection;

		// One more query...
		$this->_logger->addQueryCount();

		if (empty(Configuration::getConf()->_disableQueryCheck) && strpos($db_string, '\'') !== false && empty($db_values['security_override']))
			$this->error_backtrace('Hacking attempt...', 'Illegal character (\') used in query...', true, __FILE__, __LINE__);

		// Use "ORDER BY null" to prevent Mysql doing filesorts for Group By clauses without an Order By
		if (strpos($db_string, 'GROUP BY') !== false && strpos($db_string, 'ORDER BY') === false && strpos($db_string, 'INSERT INTO') === false)
		{
			// Add before LIMIT
			if ($pos = strpos($db_string, 'LIMIT '))
				$db_string = substr($db_string, 0, $pos) . "\t\t\tORDER BY null\n" . substr($db_string, $pos, strlen($db_string));
			else
				// Append it.
				$db_string .= "\n\t\t\tORDER BY null";
		}

		if (empty($db_values['security_override']) && (!empty($db_values) || strpos($db_string, '{db_prefix}') !== false))
		{
			// Pass some values to the global space for use in the callback function.
			$db_callback = array($db_values, $connection);

			// Inject the values passed to this function.
			$db_string = preg_replace_callback('~{([a-z_]+)(?::([a-zA-Z0-9_-]+))?}~', array($this, 'replacement_callback'), $db_string);

			// This shouldn't be residing in global space any longer.
			$db_callback = array();
		}

		// Debugging.
		if (isset(Settings::$database['db_show_debug']) && Settings::$database['db_show_debug'] === true)
		{
			// Get the file and line number this function was called.
			list ($file, $line) = $this->error_backtrace('', '', 'return', __FILE__, __LINE__);

			$this->_logger->addPreviousQueries();

			$st = microtime(true);
			// Don't overload it.
			$this->_logger->logQuery($db_string, $file, $line, $st - $time_start);
		}

		// First, we clean strings out of the query, reduce whitespace, lowercase, and trim - so we can check it over.
		if (empty(Configuration::getConf()->_disableQueryCheck))
		{
			$clean = '';
			$old_pos = 0;
			$pos = -1;
			while (true)
			{
				$pos = strpos($db_string, '\'', $pos + 1);
				if ($pos === false)
					break;
				$clean .= substr($db_string, $old_pos, $pos - $old_pos);

				while (true)
				{
					$pos1 = strpos($db_string, '\'', $pos + 1);
					$pos2 = strpos($db_string, '\\', $pos + 1);
					if ($pos1 === false)
						break;
					elseif ($pos2 == false || $pos2 > $pos1)
					{
						$pos = $pos1;
						break;
					}

					$pos = $pos2 + 1;
				}
				$clean .= ' %s ';

				$old_pos = $pos + 1;
			}
			$clean .= substr($db_string, $old_pos);
			$clean = trim(strtolower(preg_replace($allowed_comments_from, $allowed_comments_to, $clean)));

			// We don't use UNION in SMF, at least so far.  But it's useful for injections.
			if (strpos($clean, 'union') !== false && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0)
				$fail = true;
			// Comments?  We don't use comments in our queries, we leave 'em outside!
			elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== false || strpos($clean, ';') !== false)
				$fail = true;
			// Trying to change passwords, slow us down, or something?
			elseif (strpos($clean, 'sleep') !== false && preg_match('~(^|[^a-z])sleep($|[^[_a-z])~s', $clean) != 0)
				$fail = true;
			elseif (strpos($clean, 'benchmark') !== false && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0)
				$fail = true;
			// Sub selects?  We don't use those either.
			elseif (preg_match('~\([^)]*?select~s', $clean) != 0)
				$fail = true;

			if (!empty($fail))
				$this->error_backtrace('Hacking attempt...', 'Hacking attempt...' . "\n" . $db_string, E_USER_ERROR, __FILE__, __LINE__);
		}

		if (empty($db_unbuffered))
			$ret = @mysql_query($db_string, $connection);
		else
			$ret = @mysql_unbuffered_query($db_string, $connection);
		if ($ret === false && empty($db_values['db_error_skip']))
			$ret = $this->error($db_string, $connection);

		// Debugging.
		if (isset(Settings::$database['db_show_debug']) && Settings::$database['db_show_debug'] === true)
			$this->_logger->setQueryTime($st);

		return $ret;
	}

	/**
	 * @param null $connection
	 * @return int
	 */
	function affected_rows($connection = null)
	{
		return mysql_affected_rows($connection == null ? $this->getConnection() : $connection);
	}

	/**
	 * @param $table
	 * @param null $field
	 * @param null $connection
	 * @return int
	 */
	function insert_id($table, $field = null, $connection = null)
	{
		$table = str_replace('{db_prefix}', $this->_db_prefix, $table);

		// MySQL doesn't need the table or field information.
		return mysql_insert_id($connection === null ? $this->getConnection() : $connection);
	}

	/**
	 * @param string $type
	 * @param null $connection
	 * @return bool|\resource
	 */
	function transaction($type = 'commit', $connection = null)
	{
		// Decide which connection to use
		$connection = $connection == null ? $this->getConnection() : $connection;

		if ($type == 'begin')
			return @mysql_query('BEGIN', $connection);
		elseif ($type == 'rollback')
			return @mysql_query('ROLLBACK', $connection);
		elseif ($type == 'commit')
			return @mysql_query('COMMIT', $connection);

		return false;
	}

	/**
	 * @param $db_string
	 * @param $connection = null
	 * @return mixed
	 */
	function error($db_string, $connection = null)
	{
		// Get the file and line numbers.
		list ($file, $line) = $this->error_backtrace('', '', 'return', __FILE__, __LINE__);

		// Decide which connection to use
		$connection = $connection === null ? $this->getConnection() : $connection;

		// This is the error message...
		$query_error = mysql_error($connection);
		$query_errno = mysql_errno($connection);

		// Error numbers:
		//    1016: Can't open file '....MYI'
		//    1030: Got error ??? from table handler.
		//    1034: Incorrect key file for table.
		//    1035: Old key file for table.
		//    1205: Lock wait timeout exceeded.
		//    1213: Deadlock found.
		//    2006: Server has gone away.
		//    2013: Lost connection to server during query.

		// Log the error.
        $enableErrorQueryLogging = Configuration::getConf()->_enableErrorQueryLogging;
		if ($query_errno != 1213 && $query_errno != 1205 && !empty($this->_logger))
			$this->_logger->logError(Language::getLanguage()->get('database_error') . ': ' . $query_error . (!empty($enableErrorQueryLogging) ? "\n\n$db_string" : ''), 'database', $file, $line);

		// @todo quickRepair(), retryQuery()

		// Nothing's defined yet... just die with it.
		// die($query_error);

		// if (allowedTo('admin_forum'))
		//	$error_message = nl2br($query_error) . '<br />' . Language::getLanguage()->get('file') . ': ' . $file . '<br />' . Language::getLanguage()->get('line') . ': ' . $line;
		//else
		$error_message = Language::getLanguage()->get('try_again');

		//if (allowedTo('admin_forum') && isset(Settings::$database['db_show_debug']) && Settings::$database['db_show_debug'] === true)
		//{
		//	$error_message .= '<br /><br />' . nl2br($db_string);
		//}

		throw new Exception($error_message, 0, null, 'database');
	}

	/**
	 * @param string $method
	 * @param $table
	 * @param $columns
	 * @param $data
	 * @param $keys
	 * @param bool $disable_trans
	 * @param null $connection
	 * @return
	 */
	function insert($method = 'replace', $table, $columns, $data, $keys, $disable_trans = false, $connection = null)
	{
		$connection = $connection === null ? $this->getConnection() : $connection;

		// With nothing to insert, simply return.
		if (empty($data))
			return;

		// Replace the prefix holder with the actual prefix.
		$table = str_replace('{db_prefix}', $this->_db_prefix, $table);

		// Inserting data as a single row can be done as a single array.
		if (!is_array($data[array_rand($data)]))
		    $data = array($data);

		// Create the mold for a single row insert.
		$insertData = '(';
		foreach ($columns as $columnName => $type)
		{
			// Are we restricting the length?
			if (strpos($type, 'string-') !== false)
				$insertData .= sprintf('SUBSTRING({string:%1$s}, 1, ' . substr($type, 7) . '), ', $columnName);
			else
				$insertData .= sprintf('{%1$s:%2$s}, ', $type, $columnName);
		}
		$insertData = substr($insertData, 0, -2) . ')';

		// Create an array consisting of only the columns.
		$indexed_columns = array_keys($columns);

		// Here's where the variables are injected to the query.
		$insertRows = array();
		foreach ($data as $dataRow)
			$insertRows[] = $this->quote($insertData, array_combine($indexed_columns, $dataRow), $connection);

		// Determine the method of insertion.
		$queryTitle = $method == 'replace' ? 'REPLACE' : ($method == 'ignore' ? 'INSERT IGNORE' : 'INSERT');

		// Do the insert.
		$this->query('', '
			' . $queryTitle . ' INTO ' . $table . '(`' . implode('`, `', $indexed_columns) . '`)
			VALUES
				' . implode(',
				', $insertRows),
			array(
				'security_override' => true,
				'db_error_skip' => $table === $this->_db_prefix . 'log_errors',
			),
			$connection
		);
	}

	/**
	 * @param $error_message
	 * @param string $log_message
	 * @param bool $error_type
	 * @param null $file
	 * @param null $line
	 * @return array
	 */
	function error_backtrace($error_message, $log_message = '', $error_type = false, $file = null, $line = null)
	{
		if (empty($log_message))
			$log_message = $error_message;

		foreach (debug_backtrace() as $step)
		{
			// Found it?
			if (strpos($step['function'], 'query') === false && !in_array(substr($step['function'], 0, 7), array('smf_db_', 'preg_re', 'db_erro', 'call_us')) && substr($step['function'], 0, 2) != '__')
			{
				$log_message .= '<br />Function: ' . $step['function'];
				break;
			}

			if (isset($step['line']))
			{
				$file = $step['file'];
				$line = $step['line'];
			}
		}

		// A special case - we want the file and line numbers for debugging.
		if ($error_type == 'return')
			return array($file, $line);

		// Is always a critical error.
		$this->_logger->logError($log_message, 'critical', $file, $line);

		if (function_exists('fatal_error'))
		{
            // @todo review this
			$this->_logger->fatalError($error_message, false);

			// Cannot continue...
			exit;
		}
		elseif ($error_type)
			trigger_error($error_message . ($line !== null ? '<em>(' . basename($file) . '-' . $line . ')</em>' : ''), $error_type);
		else
			trigger_error($error_message . ($line !== null ? '<em>(' . basename($file) . '-' . $line . ')</em>' : ''));
	}

	/**
	 * @param $string
	 * @param bool $translate_human_wildcards
	 * @return string
	 */
	function escape_wildcard_string($string, $translate_human_wildcards=false)
	{
		$replacements = array(
			'%' => '\%',
			'_' => '\_',
			'\\' => '\\\\',
		);

		if ($translate_human_wildcards)
			$replacements += array(
				'*' => '%',
			);

		return strtr($string, $replacements);
	}

	/**
	 * Retrieve the connection to use, optionally a specific type of connection.
	 *
	 * @param $type
	 */
	function getConnection($type = 'write')
	{
		if (empty($type))
			$type = 'write';
		if (!empty($this->_connections) && (key_exists($type, $this->_connections)) && !empty($this->_connections[$type]))
			return $this->_connections[$type];
		throw new Exception('core_database_connection_not_initialized');
	}

	/**
	 * Attempt to reinitiate the connection when it may have been dropped for reasons that may be bypassed.
	 * (i.e. when the connection is lost)
	 *
	 * @return null
	 */
	function reinitiate()
	{
		// TODO: Implement reinitiate() method.
		// we don't know how to reinitiate a connection. :P
		return null;
	}

    /**
     *
     */
	function quickRepair()
	{

	}

    /**
     * @param $db_string
     * @throws \smCore\Exception
     */
	function retryQuery($db_string)
	{
		throw new Exception('core_not_implemented');

		// @todo clean this mess
		// Database error auto fixing

	}

	/**
	 * Whether the database system is case sensitive.
	 *
	 * @return bool
	 */
	function isCaseSensitive()
	{
		return false;
	}

	/**
	 * Number of rows returned in the result.
	 *
	 * @param $result
	 */
	function num_rows($result)
	{
		mysql_num_rows($result);
	}


	/**
	 * Select database.
	 *
	 * @param $db_name
	 * @param $connection
	 */
	function select_db($db_name, $connection)
	{
		@mysql_select_db($db_name, $connection);
	}

	/**
	 * Retrieve database server information.
	 *
	 * @return string
	 */
	function server_info()
	{
		return mysql_get_server_info();
	}


	/**
	 * Fetch association from result.
	 *
	 * @param $result
     * @return array
     */
	function fetch_assoc($result)
	{
		return mysql_fetch_assoc($result);
	}

	/**
	 * Fetch a row from result.
	 *
	 * @param $result
     * @return array
     */
	function fetch_row($result)
	{
		return mysql_fetch_row($result);
	}

	/**
	 * Free the resultset.
	 *
	 * @param $result
	 */
	function free_result($result)
	{
		mysql_free_result($result);
	}

	/**
	 * Seek in the result.
	 *
	 * @param $result
	 * @param $index
	 * @return bool
	 */
	function data_seek($result, $index)
	{
		return mysql_data_seek($result, $index);
	}

	/**
	 * @param $result
	 * @return integer
	 */
	function num_fields($result)
	{
		return mysql_num_fields($result);
	}

	/**
	 * Escape string
	 *
	 * @param $string
	 * @return string
	 */
	function escape_string($string)
	{
		return addslashes($string);
	}

	/**
	 * Unescape string
	 *
	 * @param $string
	 * @return string
	 */
	function unescape_string($string)
	{
		return stripslashes($string);
	}


	/**
	 * Return error information
	 *
	 * @param $connection
	 * @return string
	 */
	function getDatabaseError($connection)
	{
		return @mysql_error($connection);
	}


	/**
	 * @return string
	 */
	function title()
	{
		return 'MySQL';
	}

	/**
	 * @return bool
	 */
	function isSybase()
	{
		return false;
	}
}
