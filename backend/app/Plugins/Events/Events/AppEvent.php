<?php

namespace MythicalDash\Plugins\Events\Events;

use MythicalDash\Plugins\Events\PluginEvent;

class AppEvent implements PluginEvent {
	public static function onAppLoad() : string {
		return "app::Load";
	}

	public static function onRouterReady() : string {
		return "router::Ready";
	}
}