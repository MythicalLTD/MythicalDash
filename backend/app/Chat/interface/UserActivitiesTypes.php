<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
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
     * J4R (Join for Rewards).
     */
    public static string $j4r_reward = 'j4r:reward';
    public static string $j4r_check = 'j4r:check';
    public static string $j4r_server_joined = 'j4r:server_joined';
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

    /**
     * Backups.
     */
    public static string $admin_backup_create = 'admin:backup:create';
    public static string $admin_backup_restore = 'admin:backup:restore';
    public static string $admin_backup_delete = 'admin:backup:delete';

    /**
     * Images.
     */
    public static string $admin_image_create = 'admin:image:create';
    public static string $admin_image_update = 'admin:image:update';
    public static string $admin_image_delete = 'admin:image:delete';

    /**
     * Redirect Links.
     */
    public static string $admin_redirect_link_create = 'admin:redirect_link:create';
    public static string $admin_redirect_link_update = 'admin:redirect_link:update';
    public static string $admin_redirect_link_delete = 'admin:redirect_link:delete';

    /**
     * Roles.
     */
    public static string $admin_role_create = 'admin:role:create';
    public static string $admin_role_update = 'admin:role:update';
    public static string $admin_role_delete = 'admin:role:delete';

    /**
     * Permissions.
     */
    public static string $admin_permission_create = 'admin:permission:create';
    public static string $admin_permission_update = 'admin:permission:update';
    public static string $admin_permission_delete = 'admin:permission:delete';

    /**
     * J4R Servers.
     */
    public static string $j4r_server_create = 'admin:j4r_server:create';
    public static string $j4r_server_update = 'admin:j4r_server:update';
    public static string $j4r_server_delete = 'admin:j4r_server:delete';
    public static string $j4r_server_lock = 'admin:j4r_server:lock';
    public static string $j4r_server_unlock = 'admin:j4r_server:unlock';

    /**
     * Image Reports.
     */
    public static string $image_report_create = 'image_report:create';
    public static string $image_report_update = 'admin:image_report:update';
    public static string $image_report_delete = 'admin:image_report:delete';
    public static string $image_report_view = 'admin:image_report:view';
    public static string $image_report_resolve = 'admin:image_report:resolve';
    public static string $image_report_dismiss = 'admin:image_report:dismiss';
}
