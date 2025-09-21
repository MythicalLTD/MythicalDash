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
    private $smtpConnection = null;
    private $emailsSentThisConnection = 0;
    private $maxEmailsPerConnection = 200;  //you can also change this if your specific smtp server allows more
    private $lastRateLimitError = 0;
    private $connectionStartTime = 0;
    private $maxConnectionTime = 300; // 5 minutes max per connection to the smtp server (if connected for too long they may time u out so it will rotate after)
    private $emailsSentThisHour = 0;
    private $hourlyLimitStartTime = 0;
    
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
        // After a whole hour this thing will reset back to 0
        if (time() - $this->hourlyLimitStartTime > 3600) {
            $this->emailsSentThisHour = 0;
            $this->hourlyLimitStartTime = time();
        }

        $app = \MythicalDash\App::getInstance(false, true);
        $config = new ConfigFactory($app->getDatabase()->getPdo());
        $mailEnabled = $config->getDBSetting(ConfigInterface::SMTP_ENABLED, "false");
        
        if ($mailEnabled == "false") {
            BungeeChatApi::sendOutputWithNewLine('&cMail is disabled, skipping mail sending');
            return;
        }

        // Only process mails with status 'pending' and not locked
        $mailQueue = array_filter(MailQueue::getAll(), function ($mail) {
            return ($mail['status'] ?? 'pending') === 'pending' && ($mail['locked'] ?? 'false') === 'false';
        });
        
        $totalToProcess = count($mailQueue);
        
        // If no emails to process, exit early saving resources
        if ($totalToProcess === 0) {
            BungeeChatApi::sendOutputWithNewLine('&aNo emails to process');
            return;
        }
        
        // Quick calculations to check how many emails we can send thsi round 
        $availableThisHour = 500 - $this->emailsSentThisHour; //you can change this number if you require a higher amount / your specific smtp server allows more
        
        if ($availableThisHour <= 0) {
            BungeeChatApi::sendOutputWithNewLine('&eHourly limit reached (' . $this->emailsSentThisHour . '/500), waiting for reset');
            return;
        }
        
        // Process as many as possible in this run while avoiding time outs or bans from smtp or recepeient
        $batchSize = min($totalToProcess, $availableThisHour);
        $processedCount = 0;
        $successCount = 0;
        $failCount = 0;
        
        BungeeChatApi::sendOutputWithNewLine('&aFound ' . $totalToProcess . ' mails to process');
        BungeeChatApi::sendOutputWithNewLine('&aProcessing ' . $batchSize . ' emails ('. $this->emailsSentThisHour . '/500 sent this hour)');

        $rateLimitHit = false;
        
        foreach ($mailQueue as $mail) {
            if ($processedCount >= $batchSize || $rateLimitHit) break;
            
            // Check connection time and rotate if needed (new connection usually resets the timeout if connected for too long)
            if (time() - $this->connectionStartTime > $this->maxConnectionTime) {
                $this->closeSmtpConnection();
                BungeeChatApi::sendOutputWithNewLine('&aRotating SMTP connection after maximum time');
            }
            
            // Lock the mail queue item to avoid duplicate processing
            MailQueue::update($mail['id'], ['locked' => 'true']);
            
            // Find the MailList entry that references this queue ID
            $mailInfo = $this->getMailListByQueueId($mail['id']);
            if (!$mailInfo) {
                MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
                $failCount++;
                $processedCount++;
                continue;
            }
            
            $userInfo = User::getUserByUuid($mailInfo['user_uuid']);
            if (!$userInfo || empty($userInfo['email']) || !filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
                MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
                $failCount++;
                $processedCount++;
                continue;
            }
            
            $result = $this->sendMail($mail, $mailInfo, $userInfo);
            
            if ($result === 'success') {
                $successCount++;
                $this->emailsSentThisHour++;
            } elseif ($result === 'rate_limit') {
                BungeeChatApi::sendOutputWithNewLine('&cRate limit detected - stopping batch processing');
                MailQueue::update($mail['id'], ['locked' => 'false']);
                $this->lastRateLimitError = time();
                $rateLimitHit = true;
                continue;
            } else {
                $failCount++;
            }
            
            $processedCount++;
            
            // Rotate SMTP connection periodically (again to avoid timeout)
            if ($this->emailsSentThisConnection >= $this->maxEmailsPerConnection) {
                $this->closeSmtpConnection();
                BungeeChatApi::sendOutputWithNewLine('&aRotating SMTP connection after ' . $this->emailsSentThisConnection . ' emails');
            }
        }
        
        // Close the SMTP connection
        $this->closeSmtpConnection();
        
        BungeeChatApi::sendOutputWithNewLine('&aProcessed ' . $processedCount . ' mails: ' . $successCount . ' succeeded, ' . $failCount . ' failed');
        BungeeChatApi::sendOutputWithNewLine('&aTotal sent this hour: ' . $this->emailsSentThisHour . '/500');
        
        \MythicalDash\Chat\TimedTask::markRun('mail-sender', true, 'Processed ' . $processedCount . ' mails: ' . $successCount . ' succeeded, ' . $failCount . ' failed');
    }

    /**
     * Initialize SMTP connection with settings that shouldn't make it go to spam
     */
    private function initializeSmtpConnection($config)
    {
        try {
            // Validate all required SMTP settings
            $requiredSettings = [
                ConfigInterface::SMTP_HOST,
                ConfigInterface::SMTP_USER,
                ConfigInterface::SMTP_PASS,
                ConfigInterface::SMTP_PORT,
                ConfigInterface::SMTP_FROM,
                ConfigInterface::SMTP_ENCRYPTION
            ];
            
            foreach ($requiredSettings as $setting) {
                if ($config->getDBSetting($setting, null) == null) {
                    throw new \Exception("SMTP setting $setting is not configured");
                }
            }
            
            $mailObj = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings for maximum performance
            $mailObj->isSMTP();
            $mailObj->Host = $config->getDBSetting(ConfigInterface::SMTP_HOST, null);
            $mailObj->SMTPAuth = true;
            $mailObj->Username = $config->getDBSetting(ConfigInterface::SMTP_USER, null);
            $mailObj->Password = $config->getDBSetting(ConfigInterface::SMTP_PASS, null);
            $mailObj->SMTPSecure = $config->getDBSetting(ConfigInterface::SMTP_ENCRYPTION, 'ssl');
            $mailObj->Port = $config->getDBSetting(ConfigInterface::SMTP_PORT, null);
            
            // Optimize timeouts for performance
            $mailObj->Timeout = 15;
            $mailObj->SMTPKeepAlive = true;
            
            // UTF-8 encoding for emoji and special character support
            $mailObj->CharSet = 'UTF-8';
            $mailObj->Encoding = 'base64';
            
            // Important headers to avoid being marked as spam 
            $mailObj->Priority = 3;
            
            // Professional headers for better deliverability and no spam folder
            $appName = $config->getDBSetting(ConfigInterface::APP_NAME, "MythicalDash");
            $fromEmail = $config->getDBSetting(ConfigInterface::SMTP_FROM, null);
            
            // Set from address (must match authenticated domain) 
            $mailObj->setFrom($fromEmail, $appName);
            
            // Add List-Unsubscribe header (important for deliverability and usually main reason something gets sent to spam) 
            $unsubscribeUrl = $config->getDBSetting(ConfigInterface::APP_URL, '') . '/unsubscribe?email={recipient}';
            $mailObj->addCustomHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>'. 'mailto:unsubscribe@' . parse_url($fromEmail, PHP_URL_HOST));
            $mailObj->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            
            // Add Precedence header to avoid auto-responders
            $mailObj->addCustomHeader('Precedence', 'bulk');
            
            // Add X-Auto-Response-Suppress header
            $mailObj->addCustomHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');
            
            // Add Message-ID for tracking
            $messageId = '<' . time() . '.' . uniqid() . '@' . parse_url($fromEmail, PHP_URL_HOST) . '>';
            $mailObj->MessageID = $messageId;
            
            $this->smtpConnection = $mailObj;
            $this->emailsSentThisConnection = 0;
            $this->connectionStartTime = time();
            
            return $mailObj;
            
        } catch (\Exception $e) {
            BungeeChatApi::sendOutputWithNewLine('&cFailed to initialize SMTP connection: ' . $e->getMessage());
            $this->smtpConnection = null;
            return null;
        }
    }

    /**
     * Close SMTP connection
     */
    private function closeSmtpConnection()
    {
        if ($this->smtpConnection && method_exists($this->smtpConnection, 'smtpClose')) {
            $this->smtpConnection->smtpClose();
        }
        $this->smtpConnection = null;
        $this->emailsSentThisConnection = 0;
        $this->connectionStartTime = 0;
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
     * Returns 'success', 'failure', or 'rate_limit'
     */
    private function sendMail(array $mail, array $mailInfo, array $userInfo)
    {
        $app = \MythicalDash\App::getInstance(false, true);
        $config = new ConfigFactory($app->getDatabase()->getPdo());
        
        $maxRetries = 1; // Only one retry to avoid hitting limits (each request may add up to +1 to the limit)
        $attempt = 0;
        $success = false;
        
        while ($attempt < $maxRetries && !$success) {
            $attempt++;
            
            try {
                // If no mail object provided or connection is stale, create a new one
                if (!$this->smtpConnection) {
                    $this->initializeSmtpConnection($config);
                    if (!$this->smtpConnection) {
                        throw new \Exception("Failed to initialize SMTP connection");
                    }
                }
                
                // Clear all addresses and add the new recipient
                $this->smtpConnection->clearAddresses();
                $this->smtpConnection->clearReplyTos();
                $this->smtpConnection->addAddress($userInfo['email']);
                $this->smtpConnection->addReplyTo(
                    $config->getDBSetting(ConfigInterface::SMTP_FROM, null),
                    $config->getDBSetting(ConfigInterface::APP_NAME, "MythicalDash")
                );
                
                // Update List-Unsubscribe header with recipient-specific URL 
                $unsubscribeUrl = $config->getDBSetting(ConfigInterface::APP_URL, '') . '/unsubscribe?email=' . urlencode($userInfo['email']);
                $this->smtpConnection->clearCustomHeaders();
                $this->smtpConnection->addCustomHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>' . 'mailto:unsubscribe@' . parse_url($config->getDBSetting(ConfigInterface::SMTP_FROM, null), PHP_URL_HOST));
                $this->smtpConnection->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                $this->smtpConnection->addCustomHeader('Precedence', 'bulk');
                $this->smtpConnection->addCustomHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');
                
                // Content
                $this->smtpConnection->isHTML(true);
                $this->smtpConnection->Subject = $mail['subject'];
                
                // Ensure proper UTF-8 encoding for the body with emoji support
                $this->smtpConnection->Body = mb_convert_encoding($mail['body'], 'HTML-ENTITIES', 'UTF-8');
                
                // Alternative plain text body for email clients that don't support HTML
                $this->smtpConnection->AltBody = strip_tags($mail['body']);
                
                $this->smtpConnection->send();
                $success = true;
                
                MailQueue::update($mail['id'], ['status' => 'sent', 'locked' => 'false']);
                $this->emailsSentThisConnection++;
                
                return 'success';
                
            } catch (\Exception $e) {
                $error = $e->getMessage();
                
                // Handle specific SMTP errors 
                if (strpos($error, 'Mail send limit exceeded') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&cSMTP send limit exceeded - stopping batch');
                    $this->closeSmtpConnection();
                    return 'rate_limit';
                }
                
                if (strpos($error, '421 Rate limit reached') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&cRate limit reached - pausing sends');
                    $this->closeSmtpConnection();
                    return 'rate_limit';
                }
                
                if (strpos($error, '421 Rejected due to policy violations') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&cPolicy violation - check your sending practices');
                    $this->closeSmtpConnection();
                    $this->lastRateLimitError = time();
                    return 'rate_limit';
                }
                
                // Check for mailbox unavailable errors
                if (strpos($error, 'mailbox unavailable') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&cMailbox unavailable: ' . $userInfo['email']);
                    MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
                    return 'failure';
                }
                
                // Handle other SMTP errors
                if (strpos($error, '421') !== false || strpos($error, 'too many connections') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&eConnection rate limit hit - slowing down');
                    $this->closeSmtpConnection();
                    // Small delay before continuing with next email
                    usleep(100000);
                } else if (strpos($error, '450') !== false || strpos($error, '451') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&eTemporary sending limit exceeded - slowing down');
                    $this->closeSmtpConnection();
                    // Small delay before continuing with next email
                    usleep(100000);
                } else if (strpos($error, '550') !== false || strpos($error, 'rejected') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&cEmail rejected by server: ' . $userInfo['email']);
                    MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
                    return 'failure';
                } else if (strpos($error, 'connection') !== false || strpos($error, 'socket') !== false) {
                    BungeeChatApi::sendOutputWithNewLine('&eConnection error - reinitializing connection');
                    $this->closeSmtpConnection();
                    // Small delay before retrying
                    usleep(50000);
                } else {
                    BungeeChatApi::sendOutputWithNewLine('&cFailed to send mail to ' . $userInfo['email'] . ': ' . $error);
                }
                
                if ($attempt >= $maxRetries) {
                    MailQueue::update($mail['id'], ['status' => 'failed', 'locked' => 'false']);
                    return 'failure';
                }
            }
        }
        
        return 'failure';
    }
}
