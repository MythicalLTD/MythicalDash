<?php

namespace MythicalDash\Chat\interface;

class LeaderboardTypes {
	public static string $COINS = 'coins';
	public static string $SERVERS = 'servers';
	public static string $MINUTES_AFK = 'minutes_afk';
	public static string $LINKVERTISE = 'linkvertise';
	public static string $SHAREUS = 'shareus';
	public static string $GYANILINKS = 'gyanilinks';
	public static string $LINKPAYS = 'linkpays';
	public static string $REFERRALS = 'referrals';

	public static function getLeaderboardTypes(): array {
		return [
			self::$COINS,
			self::$SERVERS,
			self::$MINUTES_AFK,
			self::$LINKVERTISE,
			self::$SHAREUS,
			self::$GYANILINKS,
			self::$LINKPAYS,
			self::$REFERRALS,
		];
	}
}