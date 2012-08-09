<?php

/**
 * smCore FileIO Writer
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

/**
 * Abstract class that all FileWriters will implement. It contains the write() method.
 */
class Writer
{
	/**
	 * Read the contents of a file from the filesystem, and return the corresponding PHP values.
	 * (i.e. an array)
	 *
	 * @param mixed  $data     The data to write to the file
	 * @param string $filename The name of the file to write to
	 *
	 * @return boolean True if the data was written successfully, false otherwise
	 */
	public function write($data, $filename)
	{
		if ($fp = @fopen($filename, 'wb'))
		{
			stream_set_write_buffer($fp, 0);

			// @todo: make sure this is multibyte safe
			if (flock($fp, LOCK_EX))
			{
				$bytes_written = fwrite($fp, $data);
			}
			else
			{
				$bytes_written = false;
			}

			flock($fp, LOCK_UN);
			fclose($fp);
			@chmod($filename, 0666 & ~umask());

			return false !== $bytes_written;
		}

		return false;
	}
}