<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="/assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include(__DIR__ . '/../../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Settings
    </title>
</head>

<body>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Settings</h4>
                        <?php include(__DIR__ . '/../../components/alert.php') ?>
                        <div class="card mb-4">
                            <h5 class="card-header">General</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/general" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="app:name" class="form-label">Company Name</label>
                                            <input class="form-control" type="text" id="app:name" name="app:name"
                                                value="<?= SettingsManager::getSetting("name") ?>"
                                                placeholder="MythicalSystems">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="app:logo" class="form-label">Company Logo</label>
                                            <input class="form-control" type="text" id="app:logo" name="app:logo"
                                                value="<?= SettingsManager::getSetting("logo") ?>" autofocus="">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Seo Settings</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/seo" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="seo:description" class="form-label">Description</label>
                                            <input class="form-control" type="text" id="seo:description"
                                                name="seo:description"
                                                value="<?= SettingsManager::getSetting("seo_description") ?>"
                                                placeholder="MythicalSystems">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="seo:keywords" class="form-label">Keywords</label>
                                            <input class="form-control" type="text" id="seo:keywords"
                                                name="seo:keywords"
                                                value="<?= SettingsManager::getSetting("seo_keywords") ?>" autofocus="">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Pterodactyl Settings</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/pterodactyl" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="pterodactyl:url" class="form-label">Panel URL</label>
                                            <input class="form-control" type="text" id="pterodactyl:url"
                                                name="pterodactyl:url"
                                                value="<?= SettingsManager::getSetting("PterodactylURL") ?>"
                                                placeholder="https://panel.example.com">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="pterodactyl:api_key" class="form-label">Panel API Key</label>
                                            <input class="form-control" type="password" id="pterodactyl:api_key"
                                                name="pterodactyl:api_key"
                                                value="<?= SettingsManager::getSetting("PterodactylAPIKey") ?>"
                                                autofocus="">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Stripe</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/stripe" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("enable_stripe") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="stripe:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="stripe:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Currency ISO (ALL LOWERCASE) <a href="https://stripe.com/docs/currencies#presentment-currencies" target="_blank">list</a></label>
                                            <div>
                                                <input type="text" required="" class="form-control" name="stripe:stripe_currency"
                                                    value="<?= SettingsManager::getSetting("stripe_currency") ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">Public Key</label>
                                            <div>
                                                <input type="text" required="" class="form-control" name="stripe:public_key"
                                                    value="<?= SettingsManager::getSetting("stripe_publishable_key") ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">Secret Key</label>
                                            <div>
                                                <input type="password" required="" class="form-control" name="stripe:private_key"
                                                    value="<?= SettingsManager::getSetting("stripe_secret_key") ?>">
                                            </div>
                                        </div>
                                        <br>

                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Discord Settings</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/discord" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Enable Oath2</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("enable_discord_link") == 'true') {
                                                    ?>
                                                    <select name="discord:enable" class="form-control">
                                                        <option value="true">True</option>
                                                        <option value="false">False</option>
                                                    </select>
                                                    <?php
                                                } else if (SettingsManager::getSetting("enable_discord_link") == 'false') {
                                                    ?>
                                                        <select name="discord:enable" class="form-control">
                                                            <option value="false">False</option>
                                                            <option value="true">True</option>
                                                        </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                        <select name="discord:enable" class="form-control">
                                                            <option value="false">True</option>
                                                            <option value="false">False</option>
                                                        </select>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="discord:serverid" class="form-label">Discord Server ID</label>
                                            <input class="form-control" type="text" id="discord:serverid"
                                                name="discord:serverid"
                                                value="<?= SettingsManager::getSetting("discord_serverid") ?>"
                                                placeholder="000000000000">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="discord:invite" class="form-label">Discord Invite</label>
                                            <input class="form-control" type="text" id="discord:invite"
                                                name="discord:invite"
                                                value="<?= SettingsManager::getSetting("discord_invite") ?>"
                                                placeholder="MythicalSystems">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Discord Webhook</label>
                                            <input type="password" required="" class="form-control"
                                                name="discord:webhook"
                                                value="<?= SettingsManager::getSetting("discord_webhook") ?>">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="discord:client_id" class="form-label">Discord Client ID</label>
                                            <input class="form-control" type="text" id="discord:client_id"
                                                name="discord:client_id"
                                                value="<?= SettingsManager::getSetting("discord_clientid") ?>"
                                                placeholder="000000000000">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Discord Client Secret</label>
                                            <input type="password" required="" class="form-control"
                                                name="discord:client_secret"
                                                value="<?= SettingsManager::getSetting("discord_clientsecret") ?>">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Default Resources Settings</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/resources" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:coins" class="form-label">Coins</label>
                                            <input class="form-control" type="text" id="resources:coins"
                                                name="resources:coins"
                                                value="<?= SettingsManager::getSetting("def_coins") ?>"
                                                placeholder="15">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:ram" class="form-label">Ram</label>
                                            <input class="form-control" type="text" id="resources:ram"
                                                name="resources:ram"
                                                value="<?= SettingsManager::getSetting("def_memory") ?>"
                                                placeholder="1024">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:disk" class="form-label">Disk</label>
                                            <input class="form-control" type="text" id="resources:disk"
                                                name="resources:disk"
                                                value="<?= SettingsManager::getSetting("def_disk_space") ?>"
                                                placeholder="1024">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:cpu" class="form-label">Cpu</label>
                                            <input class="form-control" type="text" id="resources:cpu"
                                                name="resources:cpu"
                                                value="<?= SettingsManager::getSetting("def_cpu") ?>" placeholder="100">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:svlimit" class="form-label">Server Limit</label>
                                            <input class="form-control" type="text" id="resources:svlimit"
                                                name="resources:svlimit"
                                                value="<?= SettingsManager::getSetting("def_server_limit") ?>"
                                                placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:ports" class="form-label">Server Allocations</label>
                                            <input class="form-control" type="text" id="resources:ports"
                                                name="resources:ports"
                                                value="<?= SettingsManager::getSetting("def_port") ?>" placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:databases" class="form-label">Server Databases</label>
                                            <input class="form-control" type="text" id="resources:databases"
                                                name="resources:databases"
                                                value="<?= SettingsManager::getSetting("def_db") ?>" placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:backups" class="form-label">Server Backups</label>
                                            <input class="form-control" type="text" id="resources:backups"
                                                name="resources:backups"
                                                value="<?= SettingsManager::getSetting("def_backups") ?>"
                                                placeholder="2">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Enable AFK</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("enable_afk") == 'true') {
                                                    ?>
                                                    <select name="resources:eafk" class="form-control">
                                                        <option value="true">True</option>
                                                        <option value="false">False</option>
                                                    </select>
                                                    <?php
                                                } else if (SettingsManager::getSetting("enable_afk") == 'false') {
                                                    ?>
                                                        <select name="resources:eafk" class="form-control">
                                                            <option value="false">False</option>
                                                            <option value="true">True</option>
                                                        </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                        <select name="resources:eafk" class="form-control">
                                                            <option value="false">True</option>
                                                            <option value="false">False</option>
                                                        </select>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="afk:coins:per:min" class="form-label">Coins per minute
                                                afk</label>
                                            <input class="form-control" type="text" id="afk:coins:per:min"
                                                name="afk:coins:per:min"
                                                value="<?= SettingsManager::getSetting("afk_coins_per_min") ?>"
                                                placeholder="2">
                                        </div>

                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Store Resources Price Settings</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/store" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-1">
                                            <label for="store:ram" class="form-label">Ram</label>
                                            <input class="form-control" type="text" id="store:ram" name="store:ram"
                                                value="<?= SettingsManager::getSetting("price_memory") ?>"
                                                placeholder="400">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="store:disk" class="form-label">Disk</label>
                                            <input class="form-control" type="text" id="store:disk" name="store:disk"
                                                value="<?= SettingsManager::getSetting("price_disk_space") ?>"
                                                placeholder="300">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="store:cpu" class="form-label">Cpu</label>
                                            <input class="form-control" type="text" id="store:cpu" name="store:cpu"
                                                value="<?= SettingsManager::getSetting("price_cpu") ?>"
                                                placeholder="450">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:svlimit" class="form-label">Server Slots</label>
                                            <input class="form-control" type="text" id="store:svlimit"
                                                name="store:svlimit"
                                                value="<?= SettingsManager::getSetting("price_server_limit") ?>"
                                                placeholder="1200">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:ports" class="form-label">Server Allocations</label>
                                            <input class="form-control" type="text" id="store:ports" name="store:ports"
                                                value="<?= SettingsManager::getSetting("price_allocation") ?>"
                                                placeholder="900">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:databases" class="form-label">Server Databases</label>
                                            <input class="form-control" type="text" id="store:databases"
                                                name="store:databases"
                                                value="<?= SettingsManager::getSetting("price_database") ?>"
                                                placeholder="150">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:backups" class="form-label">Server Backups</label>
                                            <input class="form-control" type="text" id="store:backups"
                                                name="store:backups"
                                                value="<?= SettingsManager::getSetting("price_backup") ?>"
                                                placeholder="200">
                                        </div>

                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Mail</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/mail" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label class="control-label">SMTP</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("enable_smtp") == 'true') {
                                                    ?>
                                                    <select name="mail:enable" class="form-control">
                                                        <option value="true">Enable</option>
                                                        <option value="false">Disable</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select name="mail:enable" class="form-control">
                                                        <option value="false">Disable</option>
                                                        <option value="true">Enable</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Encryption</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("smtpSecure") == 'ssl') {
                                                    ?>
                                                    <select name="mail:encryption" class="form-control">
                                                        <option value="ssl">SSL</option>
                                                        <option value="tls">TLS</option>
                                                    </select>
                                                    <?php
                                                } else if (SettingsManager::getSetting("smtpSecure") == 'tls') {
                                                    ?>
                                                        <select name="mail:encryption" class="form-control">
                                                            <option value="tls">TLS</option>
                                                            <option value="ssl">SSL</option>
                                                        </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                        <select name="mail:encryption" class="form-control">
                                                            <option value="ssl">SSL</option>
                                                            <option value="tls">TLS</option>
                                                        </select>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">SMTP Host</label>
                                            <div>
                                                <input required="" type="text" class="form-control" name="mail:host"
                                                    value="<?= SettingsManager::getSetting("smtpHost") ?>">
                                                <p class="text-muted small">Enter the SMTP server
                                                    address that mail should be sent through.</p>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">SMTP Port</label>
                                            <div>
                                                <input required="" type="number" class="form-control" name="mail:port"
                                                    value="<?= SettingsManager::getSetting("smtpPort") ?>">
                                                <p class="text-muted small">Enter the SMTP server
                                                    port that mail should be sent through.</p>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="control-label">Username <span
                                                    class="field-optional"></span></label>
                                            <div>
                                                <input type="text" class="form-control" name="mail:username"
                                                    value="<?= SettingsManager::getSetting("smtpUsername") ?>">
                                                <p class="text-muted small">The username to use when
                                                    connecting to the SMTP server.</p>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="control-label">Password <span
                                                    class="field-optional"></span></label>
                                            <div>
                                                <input type="password"
                                                    value="<?= SettingsManager::getSetting("smtpPassword") ?>"
                                                    class="form-control" name="mail:password">
                                                <p class="text-muted small">The password to use in
                                                    conjunction with the SMTP username.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Turnstile Settings</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/recaptcha" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("enable_turnstile") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="recaptcha:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="recaptcha:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label class="control-label">Site Key</label>
                                            <div>
                                                <input type="text" required="" class="form-control"
                                                    name="recaptcha:website_key"
                                                    value="<?= SettingsManager::getSetting("turnstile_sitekey") ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label class="control-label">Secret Key</label>
                                            <div>
                                                <input type="password" required="" class="form-control"
                                                    name="recaptcha:secret_key"
                                                    value="<?= SettingsManager::getSetting("turnstile_secretkey") ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Ads</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/ads" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("enable_ads") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="ads:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="ads:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <br>
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Anti AdBlocker Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("enable_adblocker_detection") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="ads:adblocker">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="ads:adblocker">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label">Ads Code</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control" name="ads:code"
                                                    rows="4"
                                                    value=""><?= SettingsManager::getSetting("ads_code") ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Linkvertise</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/linkvertise" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("linkvertise_enabled") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="ads:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="ads:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label class="control-label">Key</label>
                                            <div>
                                                <input type="number" required="" class="form-control" name="ads:code"
                                                    value="<?= SettingsManager::getSetting("linkvertise_code") ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label class="control-label">Coins</label>
                                            <div>
                                                <input type="number" required="" class="form-control" name="ads:coins"
                                                    value="<?= SettingsManager::getSetting("linkvertise_coins") ?>">
                                            </div>
                                        </div>
                                        <br>

                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Custom CSS</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/customcss" method="POST">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("customcss_enabled") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="customcss:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="customcss:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label">Style.css</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control"
                                                    name="customcss:code" rows="4"
                                                    value=""><?= SettingsManager::getSetting("customcss_code") ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Custom Header Code</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/customhead" method="POST">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("customhead_enabled") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="customhead:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="customhead:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label">Custom head code</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control"
                                                    name="customhead:code" rows="4"
                                                    value=""><?= SettingsManager::getSetting("customhead_code") ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Terms of Service</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/tos" method="POST">
                                    <div class="row">
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label">Custom head code (HTML SUPPORTED)</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control" name="text"
                                                    rows="4"
                                                    value=""><?= SettingsManager::getSetting("terms_of_service") ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Privacy Policy</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/pp" method="POST">
                                    <div class="row">
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label">Text (HTML SUPPORTED)</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control" name="text"
                                                    rows="4"
                                                    value=""><?= SettingsManager::getSetting("privacy_policy") ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header">Server Purge</h5>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/purge" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting("server_purge") == 'true') {
                                                    ?>
                                                    <select class="form-control" name="purge:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="purge:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <?php
                                        if (SettingsManager::getSetting("server_purge") == 'true') {
                                            ?>
                                            <div class="form-group col-md-2">
                                                <label class="control-label">Execute Purge</label><br>
                                                <button type="button" type="button" data-bs-toggle="modal"
                                                    data-bs-target="#runpurge"
                                                    class="btn btn-danger waves-effect waves-light">Execute changes</a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <br>
                                    <div class="mt-2">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="runpurge" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Woah, buddy, are you sure you want to run this?</h3>
                                        <p class="form-check-label">If you chose to continue, we are going to delete
                                            %servers% from
                                            your dashboard and panel. Because they did not click the active button on
                                            the server list from inside the dash. Please keep in mind that this function
                                            will be executed after you give your users some time to mark their server as
                                            active. This action can not be undone. Mythical systems do not take any
                                            responsibility for you not being able to read and understand how those
                                            functions work or how to use them! If you want to continue, please press
                                            continue. MAKE SURE YOU READ THIS BEFORE DOING ANYTHING.
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" required type="checkbox" id="ikwhatidoing"
                                                name="ikwhatidoing" />
                                            <label class="form-check-label" for="ikwhatidoing">
                                                I know what this function does, and I have read the text!
                                            </label>
                                        </div>
                                    </div>
                                    <form method="POST" action="/admin/servers/purge" class="row g-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-danger me-sm-3 me-1">Continue</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include(__DIR__ . '/../../components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
        <?php include(__DIR__ . '/../../requirements/footer.php') ?>
    </div>
</body>

</html>