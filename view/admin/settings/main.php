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
        <?= SettingsManager::getSetting('name') ?> - Settings
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
                        <?php
                        if (isset($_GET['sqlr'])) {
                            ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <code><?= $_GET['sqlr'] ?></code>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">General</h5>
                            <div class="card-text text-center">
                                <p>Customize your general settings here.</p>
                                <p>If you want a new language to show here drag and drop the language file inside the
                                    <code>/lang</code>
                                    folder!!.
                                </p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/general" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-2">
                                            <label for="app:name" class="form-label">Company Name</label>
                                            <input class="form-control" type="text" id="app:name" name="app:name"
                                                value="<?= SettingsManager::getSetting('name') ?>"
                                                placeholder="MythicalSystems">
                                        </div>

                                        <div class="mb-3 col-md-2">
                                            <label for="app:logo" class="form-label">Company Logo</label>
                                            <input class="form-control" type="text" id="app:logo" name="app:logo"
                                                value="<?= SettingsManager::getSetting('logo') ?>" autofocus="">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="app:logo" class="form-label">Background Picture</label>
                                            <input class="form-control" type="text" id="app:bg" name="app:bg"
                                                value="<?= SettingsManager::getSetting('bg') ?>" autofocus="">
                                        </div>

                                        <div class="mb-3 col-md-2">
                                            <label for="app:name" class="form-label">Language</label>
                                            <!--<input class="form-control" type="text" id="app:lang" name="app:lang"
                                                value="<?= SettingsManager::getSetting('lang') ?>" placeholder="en_US">-->
                                            <select class="form-control" name="app:lang">
                                                <?php
                                                echo "<option value='" . SettingsManager::getSetting("lang") . "'>" . SettingsManager::getSetting("lang") . " (X)</option>";

                                                $path = __DIR__ . "/../../../lang/";
                                                $files = scandir($path);
                                                foreach ($files as $file) {
                                                    if ($file != '.' && $file != '..') {
                                                        $fileName = pathinfo($file, PATHINFO_FILENAME);
                                                        echo "<option value='$fileName'>$fileName</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="app:name" class="form-label">Timezone</label>
                                            <select class="form-control" name="app:timezone">
                                                <?php
                                                $timezone_identifiers = DateTimeZone::listIdentifiers();
                                                echo '<option value="' . SettingsManager::getSetting('timezone') . '">' . SettingsManager::getSetting('timezone') . ' (X)</option>';

                                                foreach ($timezone_identifiers as $timezone) {
                                                    echo '<option value="' . $timezone . '">' . $timezone . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-1">
                                            <label class="control-label">Snow</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('show_snow') == 'true') {
                                                    ?>
                                                    <select class="form-control" name="app:snow">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="app:snow">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Links</h5>
                            <div class="card-text text-center">
                                <label>Customize your custom links here. &NewLine;</label><br>
                                <label>In this page you can change what display links you want to show in the sidebar!
                                    If you don't want to show any please input <code>none</code> into the field!</label>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/links" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-2">
                                            <label for="url:panel" class="form-label">Panel</label>
                                            <input class="form-control" type="text" disabled id="url:panel"
                                                name="url:panel"
                                                value="<?= SettingsManager::getSetting('PterodactylURL') ?>"
                                                placeholder="MythicalSystems">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="url:discord" class="form-label">Discord</label>
                                            <input class="form-control" type="text" disabled id="url:website"
                                                name="url:discord"
                                                value="<?= SettingsManager::getSetting('discord_invite') ?>"
                                                autofocus="">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="url:website" class="form-label">WebSite</label>
                                            <input class="form-control" type="text" id="url:website" name="url:website"
                                                value="<?= SettingsManager::getSetting('website') ?>" autofocus="">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="url:status" class="form-label">Status Page</label>
                                            <input class="form-control" type="text" id="url:status" name="url:status"
                                                value="<?= SettingsManager::getSetting('status') ?>" autofocus="">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="url:x" class="form-label">Twitter (X)</label>
                                            <input class="form-control" type="text" id="url:x" name="url:x"
                                                value="<?= SettingsManager::getSetting('x') ?>" autofocus="">
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Seo Settings</h5>
                            <div class="card-text text-center">
                                <label>Customize your seo settings here. &NewLine;</label><br>
                                <label>SEO settings are the settings from Google Search or from the embed!</label>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/seo" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="seo:description" class="form-label">Description</label>
                                            <input class="form-control" type="text" id="seo:description"
                                                name="seo:description"
                                                value="<?= SettingsManager::getSetting('seo_description') ?>"
                                                placeholder="MythicalSystems">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="seo:keywords" class="form-label">Keywords</label>
                                            <input class="form-control" type="text" id="seo:keywords"
                                                name="seo:keywords"
                                                value="<?= SettingsManager::getSetting('seo_keywords') ?>" autofocus="">
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Pterodactyl Settings</h5>
                            <div class="card-text text-center">
                                <label>Customize your pterodactyl settings here. &NewLine;</label><br>
                                <label>Panel URL: This shall be your pterodactyl panel URL (no / at the end,
                                    please).</label><br>
                                <label>Panel API Key: This shall be your pterodactyl admin API key (with full permission
                                    to read/write).</label>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/pterodactyl" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="pterodactyl:url" class="form-label">Panel URL</label>
                                            <input class="form-control" type="text" id="pterodactyl:url"
                                                name="pterodactyl:url"
                                                value="<?= SettingsManager::getSetting('PterodactylURL') ?>"
                                                placeholder="https://panel.example.com">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="pterodactyl:api_key" class="form-label">Panel API Key</label>
                                            <input class="form-control" type="password" id="pterodactyl:api_key"
                                                name="pterodactyl:api_key"
                                                value="<?= SettingsManager::getSetting('PterodactylAPIKey') ?>"
                                                autofocus="">
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Stripe</h5>
                            <div class="card-text text-center">
                                <label>Customize your stripe settings here. &NewLine;</label><br>
                                <label>Currency: This shall be your currency ISO (<a
                                        href="https://stripe.com/docs/currencies#presentment-currencies">Here you can
                                        find a list</a>).</label><br>
                                <label>Price: This shall be the price per 1 coin (This shall be cents EX 1000 = 1
                                    <?= strtoupper(SettingsManager::getSetting('stripe_currency')) ?>).
                                </label><br>
                                <label>Public Key: This shall be the publishable key that you can get from <a
                                        href="https://dashboard.stripe.com/apikeys">here</a>.</label><br>
                                <label>Secret Key: This shall be the secret key that you can get from <a
                                        href="https://dashboard.stripe.com/apikeys">here</a>.</label>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/stripe" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_stripe') == 'true') {
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
                                        <div class="form-group col-md-1">
                                            <label class="control-label">Currency</label>
                                            <div>
                                                <input type="text" required="" class="form-control"
                                                    name="stripe:stripe_currency"
                                                    value="<?= strtoupper(SettingsManager::getSetting('stripe_currency')) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label class="control-label">Price</label>
                                            <div>
                                                <input type="text" required="" class="form-control"
                                                    name="stripe:stripe_coin_per_balance"
                                                    value="<?= SettingsManager::getSetting('stripe_coin_per_balance') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Public Key</label>
                                            <div>
                                                <input type="text" required="" class="form-control"
                                                    name="stripe:public_key"
                                                    value="<?= SettingsManager::getSetting('stripe_publishable_key') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Secret Key</label>
                                            <div>
                                                <input type="password" required="" class="form-control"
                                                    name="stripe:private_key"
                                                    value="<?= SettingsManager::getSetting('stripe_secret_key') ?>">
                                            </div>
                                        </div>
                                        <br>

                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Discord Settings</h5>
                            <div class="card-text text-center">
                                <label>Customize your discord settings here. &NewLine;</label><br>
                                <label>Discord Client ID: This shall be your Discord Client ID (<a
                                        href="https://discord.com/developers/applications">Here you can the discord
                                        developer portal</a>).</label><br>
                                <label>Discord Client ID: This shall be your Discord Client Secret (<a
                                        href="https://discord.com/developers/applications">Here you can the discord
                                        developer portal</a>).</label><br>
                                <p></p>
                                <label>Discord Oath2 Redirect #1: <code><?= $appURL ?>/auth/discord</code></label><br>
                                <label>Discord Oath2 Redirect #2:
                                    <code><?= $appURL ?>/auth/link/discord</code></label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/discord" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Enable Oath2</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_discord_link') == 'true') {
                                                    ?>
                                                    <select name="discord:enable" class="form-control">
                                                        <option value="true">True</option>
                                                        <option value="false">False</option>
                                                    </select>
                                                    <?php
                                                } else if (SettingsManager::getSetting('enable_discord_link') == 'false') {
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
                                        <div class="mb-3 col-md-2">
                                            <label for="discord:serverid" class="form-label">Discord Server ID</label>
                                            <input class="form-control" type="text" id="discord:serverid"
                                                name="discord:serverid"
                                                value="<?= SettingsManager::getSetting('discord_serverid') ?>"
                                                placeholder="000000000000">
                                        </div>
                                        <div class="mb-3 col-md-3">
                                            <label for="discord:invite" class="form-label">Discord Invite</label>
                                            <input class="form-control" type="text" id="discord:invite"
                                                name="discord:invite"
                                                value="<?= SettingsManager::getSetting('discord_invite') ?>"
                                                placeholder="MythicalSystems">
                                        </div>
                                        <div class="mb-3 col-md-5">
                                            <label class="form-label">Discord Webhook</label>
                                            <input type="password" required="" class="form-control"
                                                name="discord:webhook"
                                                value="<?= SettingsManager::getSetting('discord_webhook') ?>">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="discord:client_id" class="form-label">Discord Client ID</label>
                                            <input class="form-control" type="text" id="discord:client_id"
                                                name="discord:client_id"
                                                value="<?= SettingsManager::getSetting('discord_clientid') ?>"
                                                placeholder="000000000000">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Discord Client Secret</label>
                                            <input type="password" required="" class="form-control"
                                                name="discord:client_secret"
                                                value="<?= SettingsManager::getSetting('discord_clientsecret') ?>">
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Default Resources Settings</h5>
                            <div class="card-text text-center">
                                <label>1. Row: Customize your default resources. &NewLine;</label><br>
                                <label>2: Row: Customize your afk resources/settings. &NewLine;</label><br>
                                <label>3: Row: Customize your maximum resources for clients. &NewLine;</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/resources" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:coins" class="form-label">Coins</label>
                                            <input class="form-control" type="text" id="resources:coins"
                                                name="resources:coins"
                                                value="<?= SettingsManager::getSetting('def_coins') ?>"
                                                placeholder="15">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:ram" class="form-label">Ram (MB)</label>
                                            <input class="form-control" type="text" id="resources:ram"
                                                name="resources:ram"
                                                value="<?= SettingsManager::getSetting('def_memory') ?>"
                                                placeholder="1024">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:disk" class="form-label">Disk (MB)</label>
                                            <input class="form-control" type="text" id="resources:disk"
                                                name="resources:disk"
                                                value="<?= SettingsManager::getSetting('def_disk_space') ?>"
                                                placeholder="1024">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:cpu" class="form-label">Cpu (%)</label>
                                            <input class="form-control" type="text" id="resources:cpu"
                                                name="resources:cpu"
                                                value="<?= SettingsManager::getSetting('def_cpu') ?>" placeholder="100">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:svlimit" class="form-label">Server Limit</label>
                                            <input class="form-control" type="text" id="resources:svlimit"
                                                name="resources:svlimit"
                                                value="<?= SettingsManager::getSetting('def_server_limit') ?>"
                                                placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:ports" class="form-label">Server Allocations</label>
                                            <input class="form-control" type="text" id="resources:ports"
                                                name="resources:ports"
                                                value="<?= SettingsManager::getSetting('def_port') ?>" placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:databases" class="form-label">Server Databases</label>
                                            <input class="form-control" type="text" id="resources:databases"
                                                name="resources:databases"
                                                value="<?= SettingsManager::getSetting('def_db') ?>" placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:backups" class="form-label">Server Backups</label>
                                            <input class="form-control" type="text" id="resources:backups"
                                                name="resources:backups"
                                                value="<?= SettingsManager::getSetting('def_backups') ?>"
                                                placeholder="2">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Enable AFK</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_afk') == 'true') {
                                                    ?>
                                                    <select name="resources:eafk" class="form-control">
                                                        <option value="true">True</option>
                                                        <option value="false">False</option>
                                                    </select>
                                                    <?php
                                                } else if (SettingsManager::getSetting('enable_afk') == 'false') {
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
                                            <label for="afk:coins:per:min" class="form-label">Coins per minute</label>
                                            <input class="form-control" type="text" id="afk:coins:per:min"
                                                name="afk:coins:per:min"
                                                value="<?= SettingsManager::getSetting('afk_coins_per_min') ?>"
                                                placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:ram" class="form-label">Max Ram (MB)</label>
                                            <input class="form-control" type="text" name="resources:maxram"
                                                value="<?= SettingsManager::getSetting('max_ram') ?>"
                                                placeholder="1024">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:disk" class="form-label">Max Disk (MB)</label>
                                            <input class="form-control" type="text" name="resources:maxdisk"
                                                value="<?= SettingsManager::getSetting('max_disk') ?>"
                                                placeholder="1024">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="resources:cpu" class="form-label">Max Cpu (%)</label>
                                            <input class="form-control" type="text" name="resources:maxcpu"
                                                value="<?= SettingsManager::getSetting('max_cpu') ?>" placeholder="100">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:svlimit" class="form-label">Max Server Limit</label>
                                            <input class="form-control" type="text" name="resources:maxsvlimit"
                                                value="<?= SettingsManager::getSetting('max_servers') ?>"
                                                placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:ports" class="form-label">Max Server
                                                Allocations</label>
                                            <input class="form-control" type="text" name="resources:maxports"
                                                value="<?= SettingsManager::getSetting('max_allocations') ?>"
                                                placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:databases" class="form-label">Max Server
                                                Databases</label>
                                            <input class="form-control" type="text" name="resources:maxdatabases"
                                                value="<?= SettingsManager::getSetting('max_dbs') ?>" placeholder="2">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="resources:backups" class="form-label">Max Server Backups</label>
                                            <input class="form-control" type="text" name="resources:maxbackups"
                                                value="<?= SettingsManager::getSetting('max_backups') ?>"
                                                placeholder="2">
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Store Resources Price Settings</h5>
                            <div class="card-text text-center">
                                <label>Customize your default resources here. &NewLine;</label><br>
                                <label>Those values are set per GB or value.. &NewLine;</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/store" method="GET">
                                    <div class="row">
                                        <div class="mb-3 col-md-1">
                                            <label for="store:ram" class="form-label">Ram</label>
                                            <input class="form-control" type="text" id="store:ram" name="store:ram"
                                                value="<?= SettingsManager::getSetting('price_memory') ?>"
                                                placeholder="400">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="store:disk" class="form-label">Disk</label>
                                            <input class="form-control" type="text" id="store:disk" name="store:disk"
                                                value="<?= SettingsManager::getSetting('price_disk_space') ?>"
                                                placeholder="300">
                                        </div>
                                        <div class="mb-3 col-md-1">
                                            <label for="store:cpu" class="form-label">Cpu</label>
                                            <input class="form-control" type="text" id="store:cpu" name="store:cpu"
                                                value="<?= SettingsManager::getSetting('price_cpu') ?>"
                                                placeholder="450">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:svlimit" class="form-label">Server Slots</label>
                                            <input class="form-control" type="text" id="store:svlimit"
                                                name="store:svlimit"
                                                value="<?= SettingsManager::getSetting('price_server_limit') ?>"
                                                placeholder="1200">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:ports" class="form-label">Server Allocations</label>
                                            <input class="form-control" type="text" id="store:ports" name="store:ports"
                                                value="<?= SettingsManager::getSetting('price_allocation') ?>"
                                                placeholder="900">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:databases" class="form-label">Server Databases</label>
                                            <input class="form-control" type="text" id="store:databases"
                                                name="store:databases"
                                                value="<?= SettingsManager::getSetting('price_database') ?>"
                                                placeholder="150">
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label for="store:backups" class="form-label">Server Backups</label>
                                            <input class="form-control" type="text" id="store:backups"
                                                name="store:backups"
                                                value="<?= SettingsManager::getSetting('price_backup') ?>"
                                                placeholder="200">
                                        </div>

                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Mail</h5>
                            <div class="card-text text-center">
                                <label>Customize your mail settings here. &NewLine;</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/mail" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label class="control-label">SMTP</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_smtp') == 'true') {
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
                                                if (SettingsManager::getSetting('smtpSecure') == 'ssl') {
                                                    ?>
                                                    <select name="mail:encryption" class="form-control">
                                                        <option value="ssl">SSL</option>
                                                        <option value="tls">TLS</option>
                                                    </select>
                                                    <?php
                                                } else if (SettingsManager::getSetting('smtpSecure') == 'tls') {
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
                                                    value="<?= SettingsManager::getSetting('smtpHost') ?>">
                                                <p class="text-muted small">Enter the SMTP server
                                                    address that mail should be sent through.</p>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">SMTP Port</label>
                                            <div>
                                                <input required="" type="number" class="form-control" name="mail:port"
                                                    value="<?= SettingsManager::getSetting('smtpPort') ?>">
                                                <p class="text-muted small">Enter the SMTP server
                                                    port that mail should be sent through.</p>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="control-label">Username <span
                                                    class="field-optional"></span></label>
                                            <div>
                                                <input type="text" class="form-control" name="mail:username"
                                                    value="<?= SettingsManager::getSetting('smtpUsername') ?>">
                                                <p class="text-muted small">The username to use when
                                                    connecting to the SMTP server.</p>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">Password <span
                                                    class="field-optional"></span></label>
                                            <div>
                                                <input type="password"
                                                    value="<?= SettingsManager::getSetting('smtpPassword') ?>"
                                                    class="form-control" name="mail:password">
                                                <p class="text-muted small">The password to use in
                                                    conjunction with the SMTP username.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">Mail From <span
                                                    class="field-optional"></span></label>
                                            <div>
                                                <input type="text" class="form-control" name="mail:from:address"
                                                    value="<?= SettingsManager::getSetting('fromEmail') ?>">
                                                <p class="text-muted small">The email address where you send emails
                                                    from.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Turnstile Settings</h5>
                            <div class="card-text text-center">
                                <label>Customize your turnstile settings here. &NewLine;</label><br>
                                <label>You can configure turnstile only if you have a cloudflare account and a
                                    website.</label><br>
                                <label>Here you can get started. <a
                                        href="https://www.cloudflare.com/en-gb/products/turnstile/">Get
                                        Started</a></label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/recaptcha" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_turnstile') == 'true') {
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
                                                    value="<?= SettingsManager::getSetting('turnstile_sitekey') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label class="control-label">Secret Key</label>
                                            <div>
                                                <input type="password" required="" class="form-control"
                                                    name="recaptcha:secret_key"
                                                    value="<?= SettingsManager::getSetting('turnstile_secretkey') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Ads</h5>
                            <div class="card-text text-center">
                                <label>Customize your ads settings here. &NewLine;</label><br>
                                <label>You can code your own ad design that will be displayed on all the
                                    pages</label><br>
                                <label>This does support PHP/HTML CODE.</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/ads" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_ads') == 'true') {
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
                                        <div class="form-group">
                                            <label class="control-label">Ads Code</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control" name="ads:code"
                                                    rows="4"
                                                    value=""><?= SettingsManager::getSetting('ads_code') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Linkvertise</h5>
                            <div class="card-text text-center">
                                <label>Customize your linkvertise settings here. &NewLine;</label><br>
                                <label>You can get your linkvertise code <a
                                        href="https://publisher.linkvertise.com/dashboard#dynamic">here</a>.</label><br>
                                <label>Please only paste in the numbers from the full script code..</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/linkvertise" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('linkvertise_enabled') == 'true') {
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
                                                    value="<?= SettingsManager::getSetting('linkvertise_code') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label class="control-label">Coins</label>
                                            <div>
                                                <input type="number" required="" class="form-control" name="ads:coins"
                                                    value="<?= SettingsManager::getSetting('linkvertise_coins') ?>">
                                            </div>
                                        </div>
                                        <br>

                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Custom CSS</h5>
                            <div class="card-text text-center">
                                <label>Customize your css settings here. &NewLine;</label><br>
                                <label>You can get a list of custom css themes <a
                                        href="https://github.com/MythicalLTD/MythicalDash-Themes">here</a>.</label><br>
                                <label>Please only paste in the css.</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/customcss" method="POST">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('customcss_enabled') == 'true') {
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
                                                    value=""><?= SettingsManager::getSetting('customcss_code') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Custom Header Code</h5>
                            <div class="card-text text-center">
                                <label>Customize your header code here. &NewLine;</label><br>
                                <label>This code will be placed inside your header.</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/customhead" method="POST">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('customhead_enabled') == 'true') {
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
                                                    value=""><?= SettingsManager::getSetting('customhead_code') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Terms of Service</h5>
                            <div class="card-text text-center">
                                <label>Customize your terms of serice text here. &NewLine;</label><br>
                                <label>This code will be placed inside your terms of service page.</label><br>
                                <label>This code supports HTML/PHP.</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/tos" method="POST">
                                    <div class="row">
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label">Text</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control" name="text"
                                                    rows="4"
                                                    value=""><?= SettingsManager::getSetting('terms_of_service') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Security Options</h5>
                            <div class="card-text text-center">
                                <label>Customize your security options text here. &NewLine;</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/security" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Anti AdBlocker Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_adblocker_detection') == 'true') {
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
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Anti VPN Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_anti_vpn') == 'true') {
                                                    ?>
                                                    <select class="form-control" name="enable_anti_vpn">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="enable_anti_vpn">
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
                                            <label class="control-label">Anti Alting Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('enable_alting') == 'true') {
                                                    ?>
                                                    <select class="form-control" name="enable_alting">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="enable_alting">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Privacy Policy</h5>
                            <div class="card-text text-center">
                                <label>Customize your privacy policy text here. &NewLine;</label><br>
                                <label>This code will be placed inside your privacy policy page.</label><br>
                                <label>This code supports HTML/PHP.</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/pp" method="POST">
                                    <div class="row">
                                        <br>
                                        <div class="form-group">
                                            <label class="control-label">Text</label>
                                            <div>
                                                <textarea type="text" required="" class="form-control" name="text"
                                                    rows="4"
                                                    value=""><?= SettingsManager::getSetting('privacy_policy') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Server Purge</h5>
                            <div class="card-text text-center">
                                <label>Customize your server purge options here. &NewLine;</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/purge" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('server_purge') == 'true') {
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
                                        if (SettingsManager::getSetting('server_purge') == 'true') {
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
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Landing Page</h5>
                            <div class="card-text text-center">
                                <label>Customize your landingpage options here. &NewLine;</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <form action="/admin/settings/landingpage" method="GET">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Status</label>
                                            <div>
                                                <?php
                                                if (SettingsManager::getSetting('landingpage') == 'true') {
                                                    ?>
                                                    <select class="form-control" name="landingpage:enabled">
                                                        <option value="true">Enabled</option>
                                                        <option value="false">Disabled</option>
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <select class="form-control" name="landingpage:enabled">
                                                        <option value="false">Disabled</option>
                                                        <option value="true">Enabled</option>
                                                    </select>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>


                                    </div>
                                    <br>
                                    <div class="mt-2 text-center">
                                        <button type="submit" name="update_settings"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Save
                                            changes</button>
                                        <a href="/admin" class="btn btn-label-secondary waves-effect">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <h5 class="card-header text-center">Developer Options</h5>
                            <div class="card-text text-center">
                                <label>THIS IS THE DANGER ZONE DO NOT RUN ANYTHING OR TOUCH ANYTHING HERE IF NOT TOLD SO
                                    BY THE ORIGINAL DEV <code>mythicaldoggo</code> aka <code>nayskutzu</code>
                                    &NewLine;</label><br>
                                <p></p>
                            </div>
                            <hr class="my-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#executesql"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Run SQL
                                            SCRIPT</button><br><br>&NewLine;&nbsp;
                                        <a href="/admin/purgecache"
                                            class="btn btn-primary me-2 waves-effect waves-light" value="true">Purge
                                            Internal Caches</a>
                                    </div>
                                </div>
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
                    <div class="modal fade" id="executesql" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Woah, buddy, are you sure you want to run this?</h3>
                                        <p class="form-check-label">This thing is only for debug and fixing MythicalDash
                                            SQL structure</p>
                                    </div>
                                    <form method="POST" action="/admin/sql" class="row g-3">

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <label class="control-label">SQL</label>
                                                <div>
                                                    <textarea type="text" required="" class="form-control" name="cmd"
                                                        rows="4" value=""></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-danger me-sm-3 me-1">Run</button>
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