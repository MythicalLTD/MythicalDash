<?php

namespace MythicalDash\Plugins\Events\Events;

use MythicalDash\Plugins\Events\PluginEvent;

class TicketEvent implements PluginEvent {

	public static function onTicketCreate(): string
	{
		return 'ticket::create';
	}

	public static function onTicketUpdate(): string
	{
		return 'ticket::update';
	}
	
	public static function onTicketReply() : string {
		return 'ticket::reply';
	}

	public static function onTicketView() : string {
		return 'ticket::view';
	}

	public static function onTicketAttachmentUpload() : string {
		return 'ticket::attachment::upload';
	}
}