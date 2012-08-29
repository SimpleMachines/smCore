<?php

namespace smCore\Modules\Admin;

use smCore\Event, smCore\MenuItem;

class Events
{
	public function __construct()
	{
	}

	public static function menu(Event $event)
	{
		if ($event['app']['user']->hasPermission('org.smcore.auth.is_admin'))
		{
			$admin = new MenuItem('admin', 'Admin', '/admin/', true, 90);

			$main        = new MenuItem('admin_center', 'Main', '/admin/', true, 1);
			$config      = new MenuItem('admin_config', 'Config', '/admin/config/', true, 10);
			$modules     = new MenuItem('admin_modules', 'Modules', '/admin/modules/', true, 20);
			$maintenance = new MenuItem('admin_maintenance', 'Maintenance', '/admin/maintenance/', true, 90);

			$maintenance->addItem(new MenuItem('admin_maintenance_main', 'General', '/admin/maintenance/', true, 1));
			$maintenance->addItem(new MenuItem('admin_maintenance_database', 'Database', '/admin/maintenance/database/', true, 10));
			$maintenance->addItem(new MenuItem('admin_maintenance_cache', 'Cache', '/admin/maintenance/cache/', true, 20));

			$admin->addItem($main);
			$admin->addItem($config);
			$admin->addItem($modules);
			$admin->addItem($maintenance);

			$event['menu']->addItem($admin);
		}
	}
}