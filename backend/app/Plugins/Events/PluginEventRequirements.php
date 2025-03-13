<?php

namespace MythicalDash\Plugins\Events;

use MythicalDash\Plugins\PluginEvents;

interface PluginEventRequirements {
	public static function processEvents(PluginEvents $event) : void;
}