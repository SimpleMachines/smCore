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
		$lang = $event['app']['lang'];

		if ($event['app']['user']->hasPermission('org.smcore.auth.is_admin'))
		{
			$admin = new MenuItem('admin', $lang->get('admin.menu.admin'), '/admin/', true, 90);

			$main        = new MenuItem('admin_center', $lang->get('admin.menu.main'), '/admin/', true, 1);
			$config      = new MenuItem('admin_config', $lang->get('admin.menu.config'), '/admin/config/', true, 10);
			$modules     = new MenuItem('admin_modules', $lang->get('admin.menu.modules'), '/admin/modules/', true, 20);
			$maintenance = new MenuItem('admin_maintenance', $lang->get('admin.menu.maintenance'), '/admin/maintenance/', true, 90);

			$maintenance->addItem(new MenuItem('admin_maintenance_main', $lang->get('admin.menu.maintenance.main'), '/admin/maintenance/', true, 1));
			$maintenance->addItem(new MenuItem('admin_maintenance_database', $lang->get('admin.menu.maintenance.database'), '/admin/maintenance/database/', true, 10));
			$maintenance->addItem(new MenuItem('admin_maintenance_cache', $lang->get('admin.menu.maintenance.cache'), '/admin/maintenance/cache/', true, 20));

			$admin->addItem($main);
			$admin->addItem($config);
			$admin->addItem($modules);
			$admin->addItem($maintenance);

			$event['menu']->addItem($admin);
		}
	}
}