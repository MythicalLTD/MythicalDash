<?php


use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;

$router->add('/api/system/ga-ads', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $googleAdsEnabled = $config->getSetting(ConfigInterface::GOOGLE_ADS_ENABLED, 'false');
    $googleAdsClientId = $config->getSetting(ConfigInterface::GOOGLE_ADS_CLIENT_ID, '');

    if ($googleAdsEnabled == 'true') {
        echo trim($googleAdsClientId) !== '' ? trim($googleAdsClientId) : 'null';
    } else {
        echo 'null';
    }
});
