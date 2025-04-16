<?php
use MythicalDash\App;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\User\Leaderboard;
use MythicalDash\Chat\interface\LeaderboardTypes;
$router->add('/api/user/leaderboard/(.*)', function ($type) {
	App::init();
	$appInstance = App::getInstance(true);
	$appInstance->allowOnlyGET();
	new Session($appInstance);

	$config = $appInstance->getConfig();
	if (!$config->getSetting(\MythicalDash\Config\ConfigInterface::LEADERBOARD_ENABLED,'false') == 'false') {
		$appInstance->BadRequest('Leaderboard is disabled', ['error_code' => 'LEADERBOARD_DISABLED']);
	}

	$limit = $config->getSetting(\MythicalDash\Config\ConfigInterface::LEADERBOARD_LIMIT, 15);
	if (!in_array($type, LeaderboardTypes::getLeaderboardTypes())) {
		$appInstance->BadRequest('Invalid type', ['error_code' => 'INVALID_TYPE']);
	}
	
	$leaderboard = Leaderboard::getLeaderboard($limit, $type);

	$appInstance->OK('Here you go, cuz i heard you want some leaderboard!', ['leaderboard' => $leaderboard]);
});