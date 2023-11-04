<?php
namespace MythicalDash;

use Atakde\DiscordWebhook\DiscordWebhook;
use Atakde\DiscordWebhook\Message\MessageFactory;

class DiscordWebHookHandler
{
    public static function NewAccount($appURL, $username, $avatar, $first_name, $last_name, $email, $ip)
    {
        $messageFactory = new MessageFactory();
        $embedMessage = $messageFactory->create('embed');
        $embedMessage->setAvatarUrl(SettingsManager::getSetting('logo'));
        $embedMessage->setUsername(SettingsManager::getSetting('name'));
        $embedMessage->setTitle("MythicalDash - New User");
        $embedMessage->setDescription("A new user registered on your host!");
        $embedMessage->setUrl($appURL."/admin/users?search=".$username);
        $embedMessage->setColor(0x00ff00);
        $embedMessage->setFooterText("Copyright © 2021-2023 MythicalSystems.");
        $embedMessage->setAuthorName($username);
        $embedMessage->setAuthorUrl($appURL."/admin/users?search=".$username);
        $embedMessage->setAuthorIcon($avatar);
        $embedMessage->setFields([
            [
                'name' => 'First Name',
                'value' => $first_name,
                'inline' => true
            ],
            [
                'name' => 'Last Name',
                'value' => $last_name,
                'inline' => true
            ],
            [
                'name' => 'Username',
                'value' => $username,
                'inline' => false
            ],
            [
                'name' => 'Email',
                'value' => $email,
                'inline' => false
            ],
            [
                'name' => 'IP Address',
                'value' => $ip,
                'inline' => false
            ]
        ]);

        $webhook = new DiscordWebhook($embedMessage);
        $webhook->setWebhookUrl(SettingsManager::getSetting('discord_webhook'));
        $webhook->send();
    }
    public static function NewLogin($appURL, $username, $email, $avatar, $ip)
    {
        $messageFactory = new MessageFactory();
        $embedMessage = $messageFactory->create('embed');
        $embedMessage->setAvatarUrl(SettingsManager::getSetting('logo'));
        $embedMessage->setUsername(SettingsManager::getSetting('name'));
        $embedMessage->setTitle("MythicalDash - New Login");
        $embedMessage->setDescription("A user just logged in on your host!");
        $embedMessage->setUrl($appURL."/admin/users?search=".$username);
        $embedMessage->setColor(0x00ff00);
        $embedMessage->setFooterText("Copyright © 2021-2023 MythicalSystems.");
        $embedMessage->setAuthorName($username);
        $embedMessage->setAuthorUrl($appURL."/admin/users?search=".$username);
        $embedMessage->setAuthorIcon($avatar);
        $embedMessage->setFields([
            [
                'name' => 'Username',
                'value' => $username,
                'inline' => false
            ],
            [
                'name' => 'Email',
                'value' => $email,
                'inline' => false
            ],
            [
                'name' => 'IP Address',
                'value' => $ip,
                'inline' => false
            ]
        ]);

        $webhook = new DiscordWebhook($embedMessage);
        $webhook->setWebhookUrl(SettingsManager::getSetting('discord_webhook'));
        $webhook->send();
    }
    public static function NewServer($appURL, $name)
    {
        $messageFactory = new MessageFactory();
        $embedMessage = $messageFactory->create('embed');
        $embedMessage->setAvatarUrl(SettingsManager::getSetting('logo'));
        $embedMessage->setUsername(SettingsManager::getSetting('name'));
        $embedMessage->setTitle("MythicalDash - New Server");
        $embedMessage->setDescription("A user just created a new server!");
        $embedMessage->setUrl($appURL."/admin/servers");
        $embedMessage->setColor(0x00ff00);
        $embedMessage->setFooterText("Copyright © 2021-2023 MythicalSystems.");
        $embedMessage->setFields([
            [
                'name' => 'Name',
                'value' => $name,
                'inline' => false
            ]
        ]);

        $webhook = new DiscordWebhook($embedMessage);
        $webhook->setWebhookUrl(SettingsManager::getSetting('discord_webhook'));
        $webhook->send();
    }

    public static function NewTicket($appURL, $subject, $priority, $description, $attachment)
    {
        $messageFactory = new MessageFactory();
        $embedMessage = $messageFactory->create('embed');
        $embedMessage->setAvatarUrl(SettingsManager::getSetting('logo'));
        $embedMessage->setUsername(SettingsManager::getSetting('name'));
        $embedMessage->setTitle("MythicalDash - New Server");
        $embedMessage->setDescription("A user just created a new server!");
        $embedMessage->setColor(0x00ff00);
        $embedMessage->setUrl($appURL."/admin/tickets");
        $embedMessage->setFooterText("Copyright © 2021-2023 MythicalSystems.");
        $embedMessage->setFields([
            [
                'name' => 'Subject',
                'value' => $subject,
                'inline' => false
            ],
            [
                'name' => 'Priority',
                'value' => $priority,
                'inline' => false
            ],
            [
                'name' => 'Description',
                'value' => $description,
                'inline' => false
            ],
            [
                'name' => 'Attachment',
                'value' => $attachment,
                'inline' => false
            ]
        ]);

        $webhook = new DiscordWebhook($embedMessage);
        $webhook->setWebhookUrl(SettingsManager::getSetting('discord_webhook'));
        $webhook->send();
    }
}
?>