<?php

/**
 * smCore platform
 *
 * @package smCore
 * @license MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * @version 1.0 alpha
 */

namespace smCore\filesystem;

/**
 * Writer for yaml format.
 */
class YamlFileWriter extends FileWriter
{
	/**
	 * Create and initialize this writer.
	 */
	function __construct()
	{
		// @todo
	}

	/**
	 * @see smCore/filesystem/FileWriter::write()
	 * @param $inputValues
	 * @param string $file=null
	 */
	function write($inputValues, $file = null)
	{
		// @todo write a yaml file contents, with the specified value(s)
		// This method should actually write the file on disk, if $file is given.
		// otherwise, this method returns the formatted yaml string.
	}

}
