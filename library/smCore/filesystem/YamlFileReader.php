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
use sfYaml;

/**
 * Yaml format reader.
 */
class YamlFileReader extends FileReader
{
	/**
	 * Create and initialize this readert
	 */
	function __construct()
	{
		// @todo
		// initialize sfYaml if necessary (it seems not)
	}

	/**
	 * @see smCore/filesystem/FileReader::read()
	 * @param $file
	 * @return array
	 */
	function read($file)
	{
		// @todo read a yaml file contents, and return an array
		// (or whatever it is)
		return sfYaml::load($file);
	}

}