<?php

namespace smCore\Modules\Auth;

use smCore\Event, smCore\MenuItem;

class Events
{
	public function __construct()
	{
	}

	public static function menu(Event $event)
	{
		if ($event['app']['user']->hasPermission('org.smcore.auth.is_guest'))
		{
			$event['menu']->addItem(new MenuItem('login', 'Log In', '/login/', true, 98));
			$event['menu']->addItem(new MenuItem('register', 'Register', '/register/', true, 99));
		}
		else
		{
			$event['menu']->addItem(new MenuItem('logout', 'Log Out', '/logout/', true, 99));

			$profile = new MenuItem('profile', 'Profile', '/profile/', true, 80);

			$profile->addItem(new MenuItem('profile_main', 'My Account', '/profile/', true, 1));
			$profile->addItem(new MenuItem('profile_settings', 'Settings', '/profile/settings/', true, 50));

			$event['menu']->addItem($profile);
		}
	}
}