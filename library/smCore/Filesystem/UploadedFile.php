<?php

/**
 * smCore Filesystem Uploaded File Class
 *
 * This class differs from the main File class in that it provides special methods
 * that only really apply to file that are currently in a temp directory waiting
 * to be moved to their final location.
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

use smCore\Exception;

class UploadedFile extends File
{
	public function __construct($post_data)
	{
	}

	/**
	 * 
	 *
	 * @param 
	 *
	 * @return \smCore\Filesystem\File
	 */
	public function save($location = null)
	{
		if (empty($location))
		{
			throw new Exception('UploadedFile::save() needs a location to save the file to.');
		}
	}
}