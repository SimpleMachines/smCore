<?php

namespace smCore\Modules\Users;

use smCore\Event, smCore\MenuItem;

class Events
{
	public static function menu(Event $event)
	{
		$lang = $event['app']['lang'];

		if (!$event['app']['user']->hasPermission('org.smcore.auth.is_guest'))
		{
			$profile = new MenuItem('user', $lang->get('users.menu.profile'), '/user/', true, 80);

			$username = $event['app']['router']->getMatch('username');

			if (null === $username)
			{
				$username = $event['app']['user']['display_name'];
			}

			if (mb_strtolower($username) === mb_strtolower($event['app']['user']['display_name']))
			{
				$profile->addItem(new MenuItem('user_profile', $lang->get('users.menu.summary'), '/user/', true, 1));

				if ($event['app']['user']->hasPermission('org.smcore.users.edit_own_profile'))
				{
					$profile->addItem(new MenuItem('user_settings', $lang->get('users.menu.settings'), '/user/' . $username . '/settings/', true, 50));
				}
			}
			else
			{
				$profile->addItem(new MenuItem('user_profile', $lang->get('users.menu.summary'), '/user/' . $username, true, 1));

				if ($event['app']['user']->hasPermission('org.smcore.users.edit_any_profile'))
				{
					$profile->addItem(new MenuItem('user_settings', $lang->get('users.menu.settings'), '/user/' . $username . '/settings/', true, 50));
				}
			}

			$event['menu']->addItem($profile);

			// The admin menu item will only exist if the current user is an admin
			if (isset($event['menu']['admin']))
			{
				$event['menu']['admin']->addItem(new MenuItem('admin_users', $lang->get('users.menu.admin.users'), '/admin/users/', true, 30));
			}
		}
	}
}