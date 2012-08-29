<?php

namespace smCore\HelloWorld;

use smCore\Event, smCore\MenuItem;

class Events
{
	public function __construct()
	{
	}

	public static function menu(Event $event)
	{
		$event['menu']->addItem(new MenuItem('home', 'Home', '/', true, 1));
	}
}