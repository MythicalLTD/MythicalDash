<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 */

namespace MythicalDash\Chat\interface;

class UserActivitiesTypes
{
    public static string $login = 'auth:login';
    public static string $register = 'auth:register';
    public static string $verify = 'auth:verify';
    public static string $change_password = 'auth:change_password';
    public static string $two_factor_verify = 'auth:two_factor_verify';
    public static string $two_factor_disable = 'auth:two_factor_disable';
    public static string $email_view = 'email:view';
    public static string $email_delete = 'email:delete';
    public static string $user_update = 'user:update';
    public static string $user_new_support_pin = 'user:new_support_pin';
    public static string $user_reset_api_key = 'user:reset_api_key';
    public static string $user_redeemed_code = 'user:redeemed_code';

    /**
     * Ticket Endpoints.
     */
    public static string $ticket_create = 'ticket:create';
    public static string $ticket_update = 'ticket:update';
    public static string $ticket_reply = 'ticket:reply';

    /**
     * Admin location create.
     */
    public static string $admin_location_create = 'admin:location:create';
    public static string $admin_location_update = 'admin:location:update';
    public static string $admin_location_delete = 'admin:location:delete';
    /**
     * Egg Categories.
     */
    public static string $admin_egg_category_create = 'admin:egg_category:create';
    public static string $admin_egg_category_update = 'admin:egg_category:update';
    public static string $admin_egg_category_delete = 'admin:egg_category:delete';
    public static string $admin_egg_create = 'admin:egg:create';
    public static string $admin_egg_update = 'admin:egg:update';
    public static string $admin_egg_delete = 'admin:egg:delete';

    /**
     * Ticket Departments.
     */
    public static string $admin_ticket_department_create = 'admin:ticket_department:create';
    public static string $admin_ticket_department_update = 'admin:ticket_department:update';
    public static string $admin_ticket_department_delete = 'admin:ticket_department:delete';

    /**
     * Redeem Codes.
     */
    public static string $admin_created_redeem_code = 'admin:redeem_code:create';
    public static string $admin_updated_redeem_code = 'admin:redeem_code:update';
    public static string $admin_deleted_redeem_code = 'admin:redeem_code:delete';

    /**
     * Discord & Github.
     */
    public static string $discord_login = 'discord:login';
    public static string $github_login = 'github:login';
    public static string $discord_link = 'discord:link';
    public static string $github_link = 'github:link';
    public static string $discord_unlink = 'discord:unlink';
    public static string $github_unlink = 'github:unlink';

    /**
     * Store.
     */
    public static string $store_buy = 'store:buy';

    /**
     * Servers.
     */
    public static string $server_suspend = 'server:suspend';
    public static string $server_remove_suspend = 'server:remove_suspend';
    public static string $server_delete = 'server:delete';
    public static string $server_create = 'server:create';
    public static string $server_update = 'server:update';
    public static string $server_renew = 'server:renew';
    /**
     * Plugins.
     */
    public static string $plugin_setting_update = 'plugin:setting_update';
    public static string $plugin_setting_delete = 'plugin:setting_delete';

    /**
     * Mail Templates.
     */
    public static string $mail_template_create = 'mail_template:create';
    public static string $mail_template_update = 'mail_template:update';
    public static string $mail_template_delete = 'mail_template:delete';

    /**
     * Announcements.
     */
    public static string $announcement_create = 'announcement:create';
    public static string $announcement_update = 'announcement:update';
    public static string $announcement_delete = 'announcement:delete';
    public static string $announcement_tag_create = 'announcement_tag:create';
    public static string $announcement_tag_delete = 'announcement_tag:delete';
    public static string $announcement_asset_create = 'announcement_asset:create';
    public static string $announcement_asset_delete = 'announcement_asset:delete';

    /**
     * Server Queue.
     */
    public static string $admin_server_queue_create = 'admin:server_queue:create';
    public static string $admin_server_queue_update = 'admin:server_queue:update';
    public static string $admin_server_queue_delete = 'admin:server_queue:delete';

    /**
     * Settings.
     */
    public static string $admin_settings_update = 'admin:settings:update';

    /**
     * User.
     */
    public static string $admin_user_update = 'admin:user:update';
    public static string $admin_user_delete = 'admin:user:delete';

}
