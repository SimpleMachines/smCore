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

namespace smCore\storage;
use smCore\model\Storage;

/**
 * Themes database work.
 */
class ThemeStorage
{
    /**
     * Retrieve all installed themes.
     */
	function getThemes()
	{
		// @todo not yet implemented
	}

	/**
	 * Load theme data for $id from the storage.
	 *
	 * @static
	 * @param $id
	 * @return bool|array
	 */
	function loadThemeData($id)
	{
		// load a theme from storage
		$result = Storage::database()->query("
				SELECT *
				FROM themes
				WHERE id_theme = $id", array(
					'id' => $id));

		if (Storage::database()->affected_rows($result) > 0)
			return false;
		else
			return Storage::database()->fetch_assoc($result);

	}

    /**
     * Retrieve theme with the specified name.
     *
     * @param $name
     */
	function getTheme($name)
	{
        // @todo not yet implemented
        // convenience method
	}

    /**
     * The default path of themes
     */
	function getThemesDefaultPath()
	{
        // @todo not yet implemented
	}

    /**
     * Retrieve the path of a theme
     *
     * @param $theme
     */
	function getThemePath($theme)
	{
        // @todo not yet implemented
	}

    /**
     * Retrieve the default theme
     */
	function getDefaultTheme()
	{
        // @todo not yet implemented
    }
}