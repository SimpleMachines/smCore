<?php

/**
 * smCore platform
 *
 * @package smCore
 * @author Fustrate
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

use smCore\Application as Application, smCore\Exception, smCore\TemplateEngine\Theme;

/**
 * Default theme, main class
 */
class DefaultTheme extends Theme
{
	protected $extension = 'tox';
	private $inherit_themes = array();
	protected $needs_compile = true;

    /**
     * Make me this theme
     * @param $name
     */
	public function __construct($name)
	{
		if (empty($name))
			throw new Exception('core_theme_invalid_name');
		if (!empty($this->inherit_themes))
			foreach ($this->inherit_themes as &$theme)
				$theme = dirname(dirname(__FILE__)) . '/' . $theme;

		parent::__construct($name, dirname(__FILE__), dirname(__FILE__), $this->inherit_themes, $this->needs_compile);
	}

    /**
     * Give control to the template engine
     */
	public function output()
	{
		$this->setTemplateParam('context', Application::getInstance()->getContext());
		$this->addCommonVars(array('context'));
		parent::output();
	}
}