<?php

/**
 * smCore FileIO JSON Reader
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

namespace smCore\FileIO;

class JsonReader extends Reader
{
	/**
	 * Read the contents of a file from the filesystem, and return the corresponding PHP values.
	 * (i.e. an array)
	 *
	 * @param string $filename The name of the file we're going to try to read
	 *
	 * @return mixed
	 */
	public function read($filename)
	{
		if (!file_exists($filename) || !is_readable($filename))
			return;

		$data = json_decode(file_get_contents($filename), true);

		if ($data === null)
		{
			// @todo: do something with json_last_error()
		}

		return $data;
	}
}