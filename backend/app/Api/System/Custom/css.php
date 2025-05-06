<?php

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
$router->add('/api/system/custom.css', function () {
	App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $customCss = $config->getSetting(ConfigInterface::CUSTOM_CSS, '');

    header('Content-Type: text/css');
    
    echo $customCss;
});
