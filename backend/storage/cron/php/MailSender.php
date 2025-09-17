<?php

namespace MythicalDash\Cron;


use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Cron\Cron;
use MythicalDash\Cron\TimeTask;
use MythicalDash\Chat\Mails\MailQueue;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Mails\MailList;
use MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;

class MailSender implements TimeTask
{
/**
	 * Entry point for the cron mail sender.
	 */
	public function run()
	{

		$cron = new Cron('mail-sender', '1M');
		try {
			$cron->runIfDue(function () {
				$this->sendMails();
			});
		} catch (\Exception $e) {
			$app = \MythicalDash\App::getInstance(false, true);
			$app->getLogger()->error('Failed to send mail: ' . $e->getMessage());
			\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, $e->getMessage());
		}
	}

	/**
	 * Process and send all pending mails in the queue.
	 */
	private function sendMails()
	{
		$app = \MythicalDash\App::getInstance(false, true);
		$config = new ConfigFactory($app->getDatabase()->getPdo());
		$mailEnabled = $config->getDBSetting(ConfigInterface::SMTP_ENABLED, "false");
		BungeeChatApi::sendOutputWithNewLine('&aSending mails: ' . $mailEnabled);
		if ($mailEnabled == "false") {
			BungeeChatApi::sendOutputWithNewLine('&cMail is disabled, skipping mail sending: ' . $mailEnabled);
			return;
		}

		BungeeChatApi::sendOutputWithNewLine('&aProcessing mails');
		// Only process mails with status 'pending' and not locked
		$mailQueue = array_filter(MailQueue::getAll(), function ($mail) {
			BungeeChatApi::sendOutputWithNewLine('&aProcessing mail: ' . $mail['id']);
			return ($mail['status'] ?? 'pending') === 'pending' && ($mail['locked'] ?? 'false') === 'false';
		});
		$totalToProcess = count($mailQueue);
		if ($totalToProcess > 25) {
			$mailQueue = array_slice(array_values($mailQueue), 0, 25);
			BungeeChatApi::sendOutputWithNewLine('&eLimiting mail send to 25 items this run (out of ' . $totalToProcess . ')');
		}
		BungeeChatApi::sendOutputWithNewLine('&aFound ' . count($mailQueue) . ' mails to process');

		BungeeChatApi::sendOutputWithNewLine('&aProcessing mails');
		foreach ($mailQueue as $mail) {
			BungeeChatApi::sendOutputWithNewLine('&aProcessing mail: ' . $mail['id']);
			// Lock the mail queue item to avoid duplicate processing
			MailQueue::update($mail['id'], ['locked' => 'true']);
			
			// Find the MailList entry that references this queue ID
			$mailInfo = $this->getMailListByQueueId($mail['id']);
			if (!$mailInfo) {
				\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'MailList entry not found for queue id: ' . $mail['id']);
				BungeeChatApi::sendOutputWithNewLine('&cMailList entry not found for queue id: ' . $mail['id'] .':'. print_r($mail, true));
				MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
				continue;
			}
			BungeeChatApi::sendOutputWithNewLine('&aFound mailInfo: ' . $mailInfo['id']);
			$userInfo = User::getUserByUuid($mailInfo['user_uuid']);
			if (!$userInfo || empty($userInfo['email']) || !filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
				\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'Invalid or missing user/email for mail queue id: ' . $mail['id']);
				$app->getLogger()->error('Invalid or missing user/email for mail queue id: ' . $mail['id']);
				MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
				BungeeChatApi::sendOutputWithNewLine('&cInvalid or missing user/email for mail queue id: ' . $mail['id']);
				MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
				continue;
			}
			BungeeChatApi::sendOutputWithNewLine('&aFound userInfo: ' . $userInfo['email']);
			$this->sendMail($mail, $mailInfo, $userInfo);
		}
		\MythicalDash\Chat\TimedTask::markRun('mail-sender', true, 'Sent ' . count($mailQueue) . ' mails');
	}

	/**
	 * Get MailList entry by queue ID.
	 */
	private function getMailListByQueueId(int $queueId): ?array
	{
		try {
			$pdo = \MythicalDash\Chat\Database::getPdoConnection();
			$stmt = $pdo->prepare('SELECT * FROM mythicaldash_mail_list WHERE queue_id = :queue_id LIMIT 1');
			$stmt->execute(['queue_id' => $queueId]);
			
			return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Send a single mail and update status accordingly.
	 * Retries up to 3 times on failure.
	 */
	private function sendMail(array $mail, array $mailInfo, array $userInfo)
	{
		$app = \MythicalDash\App::getInstance(false, true);
		$config = new ConfigFactory($app->getDatabase()->getPdo());
		$mailEnabled = $config->getDBSetting(ConfigInterface::SMTP_ENABLED, "false");
		BungeeChatApi::sendOutputWithNewLine('&aMail enabled: ' . $mailEnabled);

		if ($mailEnabled == "false") {
			BungeeChatApi::sendOutputWithNewLine('&cMail is disabled, skipping mail sending: ' . $mailEnabled);
			return;
		}
		BungeeChatApi::sendOutputWithNewLine('&aSending mail to &e' . $userInfo['email']);

		$maxRetries = 3;
		$attempt = 0;
		$success = false;
		$lastError = '';
		while ($attempt < $maxRetries && !$success) {
			BungeeChatApi::sendOutputWithNewLine('&aAttempting to send mail to &e' . $userInfo['email'] . " (Attempt $attempt/$maxRetries)");
			$attempt++;
			try {
				BungeeChatApi::sendOutputWithNewLine('&aSending mail to &e' . $userInfo['email'] . " (Attempt $attempt/$maxRetries)");
				if ($config->getDBSetting(ConfigInterface::SMTP_HOST, null) == null) {
					BungeeChatApi::sendOutputWithNewLine('&cSMTP host is not set, skipping mail sending');
					\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'SMTP host is not set, skipping mail sending');
					MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
					return;
				}
				if ($config->getDBSetting(ConfigInterface::SMTP_USER, null) == null) {
					BungeeChatApi::sendOutputWithNewLine('&cSMTP user is not set, skipping mail sending');
					\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'SMTP user is not set, skipping mail sending');
					MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
					return;
				}
				if ($config->getDBSetting(ConfigInterface::SMTP_PORT, null) == null) {
					BungeeChatApi::sendOutputWithNewLine('&cSMTP port is not set, skipping mail sending');
					\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'SMTP port is not set, skipping mail sending');
					MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
					return;
				}
				if ($config->getDBSetting(ConfigInterface::SMTP_FROM, null) == null) {
					BungeeChatApi::sendOutputWithNewLine('&cSMTP from is not set, skipping mail sending');
					\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'SMTP from is not set, skipping mail sending');
					MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
					return;
				}
				if ($config->getDBSetting(ConfigInterface::SMTP_ENCRYPTION, null) == null) {
					BungeeChatApi::sendOutputWithNewLine('&cSMTP encryption is not set, skipping mail sending');
					\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'SMTP encryption is not set, skipping mail sending');
					MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
					return;
				}

				$mailObj = new \PHPMailer\PHPMailer\PHPMailer(false);
				$mailObj->isSMTP();
				$mailObj->Host = $config->getDBSetting(ConfigInterface::SMTP_HOST, null);
				$mailObj->SMTPAuth = true;
				$mailObj->Username = $config->getDBSetting(ConfigInterface::SMTP_USER, null);
				$mailObj->Password = $config->getDBSetting(ConfigInterface::SMTP_PASS, null);
				$mailObj->SMTPSecure = $config->getDBSetting(ConfigInterface::SMTP_ENCRYPTION, 'ssl');
				$mailObj->Port = $config->getDBSetting(ConfigInterface::SMTP_PORT, null);
				$mailObj->setFrom($config->getDBSetting(ConfigInterface::SMTP_FROM, null), $config->getDBSetting(ConfigInterface::APP_NAME, null));
				$mailObj->addReplyTo($config->getDBSetting(ConfigInterface::SMTP_FROM, null), $config->getDBSetting(ConfigInterface::APP_NAME, null));
				$mailObj->isHTML(true);
				$mailObj->Name = $config->getDBSetting(ConfigInterface::APP_NAME, "FeatherPanel");
				$mailObj->Subject = $mail['subject'];
				$mailObj->Body = $mail['body'];
				$mailObj->addAddress($userInfo['email']);
				$mailObj->send();
				$success = true;
				MailQueue::update($mail['id'], ['status' => 'sent', 'locked' => 'false']);
				BungeeChatApi::sendOutputWithNewLine('&aMail sent to &e' . $userInfo['email']);
			} catch (\Exception $e) {
				$lastError = $e->getMessage();
				BungeeChatApi::sendOutputWithNewLine('&cFailed to send mail (attempt ' . $attempt . '): ' . $lastError);
				$app->getLogger()->error('Failed to send mail (attempt ' . $attempt . '): ' . $lastError);
				\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'Failed to send mail (attempt ' . $attempt . '): ' . $lastError);
				if ($attempt < $maxRetries) {
					sleep(2); // Wait before retrying
				}
			}
		}
		if (!$success) {
			\MythicalDash\Chat\TimedTask::markRun('mail-sender', false, 'Failed to send mail to ' . $userInfo['email']);
			MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
		}

	}
}