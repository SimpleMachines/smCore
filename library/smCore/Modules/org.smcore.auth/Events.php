<?php

namespace smCore\Modules\Auth;

use smCore\Event, smCore\MenuItem;

class Events
{
	public static function menu(Event $event)
	{
		$lang = $event['app']['lang'];

		if ($event['app']['user']->hasPermission('org.smcore.auth.is_guest'))
		{
			$event['menu']->addItem(new MenuItem('login', $lang->get('auth.menu.login'), '/login/', true, 98));
			$event['menu']->addItem(new MenuItem('register', $lang->get('auth.menu.register'), '/register/', true, 99));
		}
		else
		{
			$event['menu']->addItem(new MenuItem('logout', $lang->get('auth.menu.logout'), '/logout/', true, 99));
		}

		// The admin menu item will only exist if the current user is an admin
		if (isset($event['menu']['admin']))
		{
//			$event['menu']['admin']['admin_maintenance']->addItem(new MenuItem('admin_maintenance_users', $lang->get('auth.menu.admin.maintenance.users'), '/admin/maintenance/users/', true, 30));
		}
	}
}