<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;


include(__DIR__ . '/../requirements/page.php');
if (SettingsManager::getSetting("enable_stripe") == "false") {
    header('location: /');
}

?>