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
 * FileWriter abstract class, that all FileWriters are expected to implement.
 */
abstract class FileWriter
{
	/**
	 * Encode the PHP value(s) sent to this method into a string, depending of the format implemented by specialized classes.
	 * @param array $inputValues
	 * @param string $file
	 */
	abstract function write($inputValues, $file = null);

}