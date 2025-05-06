<?php
use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
$router->add('/api/system/custom.js', function () {
	App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $customJs = $config->getSetting(ConfigInterface::CUSTOM_JS, '');

    header('Content-Type: application/javascript');
    
	echo $customJs;
});
