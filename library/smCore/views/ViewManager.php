<?php


/**
 * smCore platform
 *
 * @package smCore
 * @author Norv
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
 *
 */

namespace smCore\views;
use smCore\model\User, smCore\TemplateEngine\Expression, smCore\Language, smCore\model\Storage,
	\Settings, smCore\Application, smCore\Exception, smCore\TemplateEngine\Theme;

/**
 * View manager. More of a theme manager for the moment.
 */
class ViewManager
{
	private static $_instance = null;
	private $_theme = null;

	/**
	 * Make an instance too, if you will.
	 */
	function __construct()
	{
		//
	}

	/**
     * Retrieve the instance if needed.
     * It probably won't be too often.
     *
	 * @static
	 * @return \smCore\views\ViewManager
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Setup the theme. This is the initialization of the theme for the application.
	 */
	public function setupTheme()
	{
		$user = User::getCurrentUser();
		$id = $user->getThemeId();

		$this->_theme = $this->loadThemeData($id);

		$this->_theme->loadTemplates('index');
		$this->_theme->addLayer('main', 'site');

		$this->_theme->addNamespace('ui', 'com.fustrate.ui');

		$this->_theme->loadTemplates('common');

		Application::getInstance()->addToContext(array(
			'page_title' => '...',
			'reload_counter' => 0,
			// 'theme_url',
			// 'default_theme_url',
			'scripturl' => Settings::APP_URL,
			'time_display' => date('g:i:s A', time()),
			'route' => array(null, null, null),
			)
		);

		Expression::setLangFunction('smCore\Language::getLanguage()->get');
		// Expression::setFormatFunction('smCore\views\ToxgFormat::format');

		Language::getLanguage()->load(\Settings::APP_LANGUAGE_DIR . '/menu.yaml');
	}

	/**
	 * Load data for theme with the given theme ID.
	 * This method will create the Theme instance, by loading the appropriate theme class for this id
	 * (always a subclass of Theme).
	 *
	 * @static
	 * @param int $id
	 * @return \smCore\TemplateEngine\Theme
	 * @throws \smCore\Exception
	 */
	public function loadThemeData($id)
	{
		$themeData = Storage::getThemeStorage()->loadThemeData($id);

		// If the user's theme doesn't exist, try the default theme instead
		if (empty($themeData) && $id != 1)
			$themeData = Storage::getThemeStorage()->loadThemeData(1);

		if (empty($themeData))
			throw new Exception('no_default_theme');

		include_once(Settings::APP_THEME_DIR . '/' . $themeData->theme_dir . '/include.php');
        $theme = new Theme($themeData->theme_name, $themeData->theme_dir);
		// $theme = new $themeData->theme_class($themeData->theme_name);

		return $theme;
	}

	/**
     * Retrieve the current theme
     *
	 * @return \smCore\TemplateEngine\Theme
	 */
	public function getTheme()
	{
		return $this->_theme;
	}

    /**
     * Shortcut to the current theme, for modules' convenience
     *
     * @static
     * @return \smCore\TemplateEngine\Theme
     */
    public static function theme()
    {
        return self::getInstance()->_theme;
    }
}