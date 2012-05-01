<?php

/**
 * Database layer
 *
 * @package database
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2012 Simple Machines and contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.0 Alpha 1
 */

namespace database;

/**
 * Abstract class of database adapters. Its methods are implemented by particular system-specific adapters
 * such as MySQL or PostgreSQL, keeping a consistent interface between them.
 */
abstract class DatabaseAdapter
{
	protected $_logger = null;
	protected $_db_prefix = null;

	public function __construct()
	{
		// @todo make logging optional
		$this->_logger = new DatabaseLogger;
	}

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
	abstract function initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_options = array());

	/**
	 * Retrieve the connection to use, optionally a specific type of connection.
	 *
	 * @abstract
	 * @param $type
	 */
	abstract function getConnection($type = 'write');

	/**
	 * Fix up the prefix so it doesn't require the database to be selected.
	 *
	 * @param $db_prefix
	 * @param $db_name
	 */
	abstract function fix_prefix(&$db_prefix, $db_name);

	/**
	 * Callback for preg_replace_calback on the query.
	 * It allows to replace on the fly a few pre-defined strings, for
	 * convenience ('query_see_board', 'query_wanna_see_board'), with
	 * their current values from $user_info.
	 * In addition, it performs checks and sanitization on the values
	 * sent to the database.
	 *
	 * @param $matches
	 */
	abstract function replacement_callback($matches);

	/**
	 * Just like the db_query, escape and quote a string,
	 * but not executing the query.
	 *
	 * @param $db_string
	 * @param $db_values
	 * @param null $connection
	 */
	abstract function quote($db_string, $db_values, $connection = null);

	/**
	 * Do a query.  Takes care of errors too.
	 *
	 * @param $identifier
	 * @param $db_string
	 * @param array $db_values
	 * @param null $connection
	 */
	abstract function query($db_string, $db_values = array(), $connection = null, $identifier = '');

	/**
	 * Affected rows
	 *
	 * @param resource $connection
	 */
	abstract function affected_rows($connection = null);

	/**
	 * insert_id
	 *
	 * @param string $table
	 * @param string $field = null
	 * @param resource $connection = null
	 */
	abstract function insert_id($table, $field = null, $connection = null);

	/**
	 * Do a transaction.
	 *
	 * @param string $type - the step to perform (i.e. 'begin', 'commit', 'rollback')
	 * @param resource $connection = null
	 */
	abstract function transaction($type = 'commit', $connection = null);

	/**
	 * Database error!
	 * Backtrace, log, try to fix.
	 *
	 * @param string $db_string
	 * @param resource $connection = null
	 */
	abstract function error($db_string, $connection = null);

	/**
	 * Insert
	 *
	 * @param string $method, options 'replace', 'ignore', 'insert'
	 * @param $table
	 * @param $columns
	 * @param $data
	 * @param $keys
	 * @param bool $disable_trans = false
	 * @param resource $connection = null
	 */
	abstract function insert($method = 'replace', $table, $columns, $data, $keys, $disable_trans = false, $connection = null);

	/**
	 * This function tries to work out additional error information from a back trace.
	 *
	 * @param $error_message
	 * @param $log_message
	 * @param $error_type
	 * @param $file
	 * @param $line
	 */
	abstract function error_backtrace($error_message, $log_message = '', $error_type = false, $file = null, $line = null);

	/**
	 * Escape the LIKE wildcards so that they match the character and not the wildcard.
	 *
	 * @param $string
	 * @param bool $translate_human_wildcards = false, if true, turns human readable wildcards into SQL wildcards.
	 */
	abstract function escape_wildcard_string($string, $translate_human_wildcards=false);

	/**
	 * Attempt to reinitiate the connection when it may have been dropped for reasons that may be bypassed.
	 * (i.e. when the connection is lost)
	 *
	 * @abstract
	 */
	abstract function reinitiate();

	/**
	 * Whether the database system is case sensitive.
	 *
	 * @abstract
	 */
	abstract function isCaseSensitive();

	/**
	 * Number of rows returned in the result.
	 *
	 * @abstract
	 * @param $result
	 */
	abstract function num_rows($result);

	/**
	 * Select database.
	 *
	 * @abstract
	 * @param $db_name
	 * @param $connection
	 */
	abstract function select_db($db_name, $connection);

	/**
	 * Retrieve database server information.
	 *
	 * @abstract
	 * @return string
	 */
	abstract function server_info();

	/**
	 * Fetch association from result.
	 *
	 * @abstract
	 * @param $result
	 */
	abstract function fetch_assoc($result);

	/**
	 * Fetch a row from result.
	 *
	 * @abstract
	 * @param $result
	 */
	abstract function fetch_row($result);

	/**
	 * Free the resultset.
	 *
	 * @abstract
	 * @param $result
	 */
	abstract function free_result($result);

	/**
	 * Seek in the result.
	 *
	 * @abstract
	 * @param $result
	 * @param $index
	 * @return bool
	 */
	abstract function data_seek($result, $index);

	/**
	 * @abstract
	 * @param $result
	 * @return integer
	 */
	abstract function num_fields($result);

	/**
	 * Escape string
	 *
	 * @abstract
	 * @param $string
	 * @return string
	 */
	abstract function escape_string($string);

	/**
	 * Unescape string
	 *
	 * @abstract
	 * @param $string
	 * @return string
	 */
	abstract function unescape_string($string);

	/**
	 * Return error information
	 *
	 * @abstract
	 * @param $connection
	 * @return string
	 */
	abstract function getDatabaseError($connection);

	/**
	 * @abstract
	 * @return string
	 */
	abstract function title();

	/**
	 * @abstract
	 * @return bool
	 */
	abstract function isSybase();
}