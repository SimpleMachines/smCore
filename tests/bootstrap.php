<?php

/**
 * smCore platform
 * Unit tests bootstrap file.
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
 * Portions created by the Initial Developer are Copyright (C) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * @version 1.0 alpha
 *
 */

require_once dirname(dirname(__FILE__)) . '/Settings.php';
$libraryPath = Settings::LIBRARY_PATH;
require_once $libraryPath . '/smCore/DefaultAutoloader.php';
\smCore\DefaultAutoloader::set_library_path($libraryPath . '/');
\smCore\DefaultAutoloader::add_directory(Settings::APP_PATH . '/library/sfYaml/');
\smCore\DefaultAutoloader::add_directory(Settings::APP_PATH . '/library/simpletest/');
\smCore\DefaultAutoloader::register();

// comment this out
\smCore\storage\Storage::initConnection(Settings::$database);

// Compat file, which will be removed!
require_once(dirname(dirname(__FILE__)) . '/Compat.php');


