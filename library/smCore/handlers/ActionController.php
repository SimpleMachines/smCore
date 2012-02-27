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
 */

namespace smCore\handlers;

/**
 * Interface for action handlers (ModuleControllers or other Controllers)
 */
interface ActionController
{
	/**
	 * Method called before dispatching to the action handler method.
	 * It allows any setting up the handler may need, such as loading language files or checking custom permissions,
	 * before actually executing the action.
	 *
	 * @abstract
	 */
	public function preDispatch();

	/**
	 * Method called after the action handler has been executed.
	 * This method is performed by the core, after executing an action handler (on normal exit).
	 * Allows cleanup, for example. Or custom hooks/events implemented by the module.
	 *
	 * @abstract
	 */
	public function postDispatch();
}