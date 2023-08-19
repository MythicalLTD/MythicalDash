<?php
use Livaco\EasyDiscordWebhook\DiscordWebhook;
use Livaco\EasyDiscordWebhook\Objects\Embed;

include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['subject']) && isset($_GET['priority']) && isset($_GET['description'])) {
    if (!$_GET['subject'] == "" && !$_GET['priority'] == "" && !$_GET['description'] == "") {
        $subject = mysqli_real_escape_string($conn, $_GET['subject']);
        $priority = mysqli_real_escape_string($conn, $_GET['priority']);
        $description = mysqli_real_escape_string($conn, $_GET['description']);
        $attachment = mysqli_real_escape_string($conn, $_GET['attachment']);
        $api_key = $userdb['api_key'];
        $conn->query("INSERT INTO `mythicaldash_tickets` (`ownerkey`, `ticketuuid`, `subject`, `priority`, `description`, `attachment`) VALUES ('".$api_key."', '".generate_keynoinfo()."', '".$subject."', '".$priority."', '".$description."', '".$attachment."');");
        $conn->close();
        DiscordWebhook::new($settings['discord_webhook'])
        ->addEmbed(Embed::new()
            ->setTitle($settings['name']. " | New Ticket")
            ->setDescription("Hi there it looks like you have a new ticket on the client area make sure to check it out!")
            ->addField("Ticket Owner", $userdb['username'], false)
            ->addField("Ticket priority", $priority, false)
            ->addField("Ticket Subject", $subject, false)
            ->addField("Ticket Description", $description, false)
            ->addField("Ticket Attachment", $attachment, false)
            ->addField("Ticket URL","https://test.test.test",false)
            ->setFooter("Ticket created at " . date("Y-m-d H:i:s"))
            ->setColor("#0x3498db")
            ->setAuthor($settings['name'],$appURL,$settings['logo'])
            ->setThumbnail($settings['logo'])
        )
        ->execute();
        header('location: /help-center/tickets');
        $conn->close();
        die();
    } else {
        header('location: /help-center?e=Missing the required information to create a ticket.');
        die();
    }
} else {
    header('location: /help-center?e=Missing the required information to create a ticket.');
    die();
}
?>