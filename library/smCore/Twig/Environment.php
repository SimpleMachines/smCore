<?php

/**
 * smCore Twig Environment
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

namespace smCore\Twig;

use smCore\Application;
use Twig_Environment, Twig_LoaderInterface;

class Environment extends Twig_Environment
{
	protected $_app;

	public function __construct(Application $app, Twig_LoaderInterface $loader = null, $options = array())
	{
		$this->_app = $app;

		parent::__construct($loader, $options);
	}

	/**
	 * Merges 
	 */
	public function mergeGlobals(array $context)
	{
		$settings = $this->_app['settings'];

		return array_merge(array(
			'user' => $this->_app['user'],
			'scripturl' => $settings['url'],
			'theme_url' => rtrim($settings['url'], '/') . '/themes/default', // @todo
			'default_theme_url' => rtrim($settings['url'], '/') . '/themes/default',
		), parent::mergeGlobals($context));
	}

	/**
	* Gets the cache filename for a given template.
	*
	* @param string $name The template name
	*
	* @return string The cache file name
	*/
	public function getCacheFilename($name)
	{
		if (false === $this->cache)
		{
			return false;
		}

		$class = substr($this->getTemplateClass($name), strlen($this->templateClassPrefix));

		return $this->cache . '/.twig.' . trim($class, '_') . '.php';
	}

	/**
	 * Gets the template class associated with the given string. Modified to 
	 *
	 * @param string  $name  The name for which to calculate the template class name
	 * @param integer $index The index if it is an embedded template
	 *
	 * @return string The template class name
	 */
	public function getTemplateClass($name, $index = null)
	{
		$settings = $this->_app['settings'];

		$class = preg_replace('/[^a-z0-9_]/i', '_', str_replace($settings['path'], '', $this->loader->getCacheKey($name)));

		// Append a semi-unique suffix. The regex makes "File_One.html" and "File.One.html" the same =\
		return $this->templateClassPrefix . $class . '_' . substr(md5($name), 0, 10) . (null === $index ? '' : '_' . $index);
	}
}