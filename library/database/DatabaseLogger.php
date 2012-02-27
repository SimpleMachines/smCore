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
use smCore\model\Storage;

/**
 * Simple database logger class.
 * The database adapters (database/XxxYyyAdapter.php) use it to log their messages or queries.
 */
class DatabaseLogger
{
    private $_queryCount = 0;
	private $_messages = array();
	private $_queries = array();

    /**
     * Increment query count
     */
	public function addQueryCount()
	{
		$this->_queryCount++;
	}

	/**
	 * Log a message in the database log.
	 *
	 * @param $message
	 */
	public function logMessage($message)
	{
		$this->_messages[] = $message;
	}

	/**
	 * Log a query in the query log.
	 * The query should be sent as a fully formed string. To log raw queries and their parameters,
	 * use logRawQuery() method.
	 *
	 * @param $query
	 * @param $file
	 * @param $line
	 * @param $start
	 */
	public function logQuery($query, $file, $line, $start)
	{
		// Don't overload it.
		if ($this->_queryCount < 50)
			$this->_queries[] = array(
				'query' => $query,
				'file' => $file,
				'line' => $line,
				'start' => $start
				);
	}

	/**
	 * Log the query as it's being prepared, along with the parameters that will be sent with it.
	 * For debugging purposes.
	 *
	 * @param $query
	 * @param $parameters
	 * @internal param $variables
	 */
	public function logRawQuery($query, $parameters)
	{
		// this won't work.
		// @todo
		$adapter = Storage::database();
		$this->_queries[] = array(
			'query' => $adapter->quote($query, $parameters),
			'parameters' => $parameters
		);
	}

	public function addPreviousQueries()
	{
		if (!empty($_SESSION['debug_redirect']))
		{
			$this->_queries = array_merge($_SESSION['debug_redirect'], $this->_queries);
			$this->_queryCount++;
			$_SESSION['debug_redirect'] = array();
		}
	}

    /**
     * Log time of the query
     *
     * @param $time
     */
	public function setQueryTime($time)
	{
		$this->_queries[count($this->_queries) - 1]['time'] = microtime(true) - $time;
	}

    /**
     * @param $error
     * @param string $type
     * @param string $file
     * @param int $line
     */
    public function logError($error, $type = 'default', $file = '', $line = 0)
    {
        // logError($txt['database_error'], 'database', $file, $line);
        // @todo not implemented
    }

    /**
     * @param $error
     * @param $type
     */
    public function fatalError($error, $type)
    {
        // @todo not implemented
    }
}