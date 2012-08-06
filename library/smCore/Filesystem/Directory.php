<?php

/**
 * smCore Filesystem Directory Class
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

namespace smCore\Filesystem;

use IteratorAggregate, ArrayIterator;

class Directory implements IteratorAggregate
{
	protected $_path;
	protected $_files = array();

	public function __construct($path, $resolve = true)
	{
		if ($resolve)
		{
			$this->_path = $path;
		}
		else
		{
			$this->_path = $path;
		}
	}

	public function getIterator()
	{
		return new ArrayIterator($this->_files);
	}

	public function exists()
	{
		return file_exists($this->_path);
	}



}