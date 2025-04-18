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

use MythicalDash\App;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\Earn\Linkvertise;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Hooks\MythicalSystems\User\UUIDManager;
use MythicalDash\Plugins\Events\Events\LinkForRewardEvent;

$router->get('/api/user/earn/l4r/linkvertise/start', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    header('Content-Type: text/html');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://publisher.linkvertise.com; style-src 'self' 'unsafe-inline';");
    global $eventManager;

    // Check if Linkvertise is enabled
    if ($config->getSetting(ConfigInterface::L4R_LINKVERTISE_ENABLED, 'false') !== 'true') {
        header('Location: /earn/links');
        exit;
    }

    $dayLimit = $config->getSetting(ConfigInterface::L4R_LINKVERTISE_DAILY_LIMIT, 5);
    $coolDown = $config->getSetting(ConfigInterface::L4R_LINKVERTISE_COOLDOWN_TIME, 3600);

    $dayCount = 0;
    $links = Linkvertise::getAllByUser($session->getInfo(UserColumns::UUID, false), 35);
    foreach ($links as $link) {
        // Check if link was created within last 24 hours
        $createdAt = strtotime($link['created_at']);
        $now = time();
        $dayAgo = $now - (24 * 60 * 60);

        if ($createdAt > $dayAgo) {
            ++$dayCount;
            $timeSinceLastLink = $now - $createdAt;
            if ($timeSinceLastLink < $coolDown) {
                $waitTime = $coolDown - $timeSinceLastLink;
                $waitMinutes = ceil($waitTime / 60);
                $eventManager->emit(LinkForRewardEvent::onLinkCoolDownReached(), [
                    'user' => $session->getInfo(UserColumns::UUID, false),
                    'wait_time' => $waitMinutes,
                ]);
                ?>
				<!DOCTYPE html>
				<html>

				<head>
					<title>Cooldown</title>
					<style>
						body {
							margin: 0;
							padding: 0;
							background-color: #111827;
							height: 100vh;
							display: flex;
							align-items: center;
							justify-content: center;
							font-family: system-ui, -apple-system, sans-serif;
						}

						.container {
							text-align: center;
						}

						h1 {
							color: #ffffff;
							font-size: 1.875rem;
							font-weight: bold;
							margin-bottom: 1.5rem;
						}

						p {
							color: #9CA3AF;
							margin-bottom: 2rem;
						}

						.button {
							background-color: #4F46E5;
							color: #ffffff;
							padding: 0.75rem 1.5rem;
							border-radius: 0.5rem;
							text-decoration: none;
						}
					</style>
				</head>

				<body>
					<div class="container">
						<h1>Please wait</h1>
						<p>You need to wait <?php echo $waitMinutes; ?> minutes before creating another link</p>
						<a href="/earn/links" class="button btn-back">Go back</a>
					</div>
				</body>

				</html>
				<?php
                exit;
            }
        }

        if ($dayCount >= $dayLimit) {
            $eventManager->emit(LinkForRewardEvent::onLinkDailyLimitReached(), [
                'user' => $session->getInfo(UserColumns::UUID, false),
                'day_limit' => $dayLimit,
            ]);
            ?>
			<!DOCTYPE html>
			<html>

			<head>
				<title>Daily Limit Reached</title>
				<style>
					body {
						margin: 0;
						padding: 0;
						background-color: #111827;
						height: 100vh;
						display: flex;
						align-items: center;
						justify-content: center;
						font-family: system-ui, -apple-system, sans-serif;
					}

					.container {
						text-align: center;
					}

					h1 {
						color: #ffffff;
						font-size: 1.875rem;
						font-weight: bold;
						margin-bottom: 1.5rem;
					}

					p {
						color: #9CA3AF;
						margin-bottom: 2rem;
					}

					.button {
						background-color: #4F46E5;
						color: #ffffff;
						padding: 0.75rem 1.5rem;
						border-radius: 0.5rem;
						text-decoration: none;
					}

					.button:hover {
						background-color: #4338CA;
					}

					.button.btn-back {
						background-color: #6B7280;
					}

					.button.btn-back:hover {
						background-color: #4B5563;
					}
					
				</style>
			</head>

			<body>
				<div class="container">
					<h1>Daily Limit Reached</h1>
					<p>You have reached your daily limit of <?php echo $dayLimit; ?> links. Please try again tomorrow.</p>

					<a href="/earn/links" class="button btn-back">Go back</a>
				</div>
			</body>

			</html>
			<?php
            exit;
        }
    }

    $linkvertiseUUID = UUIDManager::generateUUID();
    $id = Linkvertise::create($linkvertiseUUID, $session->getInfo(UserColumns::UUID, false));
    if ($id === 0) {
        $eventManager->emit(LinkForRewardEvent::onLinkInvalid(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);
        ?>
		<!DOCTYPE html>
		<html>

		<head>
			<title>Error</title>
			<style>
				body {
					margin: 0;
					padding: 0;
					background-color: #111827;
					height: 100vh;
					display: flex;
					align-items: center;
					justify-content: center;
					font-family: system-ui, -apple-system, sans-serif;
				}

				.container {
					text-align: center;
				}

				h1 {
					color: #ffffff;
					font-size: 1.875rem;
					font-weight: bold;
					margin-bottom: 1.5rem;
				}

				p {
					color: #9CA3AF;
					margin-bottom: 2rem;
				}

				.button {
					background-color: #4F46E5;
					color: #ffffff;
					padding: 0.75rem 1.5rem;
					border-radius: 0.5rem;
					text-decoration: none;
					font-weight: bold;
					transition: background-color 0.2s;
					display: inline-block;
				}

				.button:hover {
					background-color: #4338CA;
				}

				.button.btn-back {
					background-color: #6B7280;
				}

				.button.btn-back:hover {
					background-color: #4B5563;
				}
			</style>
		</head>

		<body>
			<div class="container">
				<h1>Error</h1>
				<p>Failed to create linkvertise code</p>
			</div>
		</body>

		</html>
		<?php
        return;
    }

    ?>
	<!DOCTYPE html>
	<html>

	<head>
		<title>Continue to Linkvertise</title>
		<style>
			body {
				margin: 0;
				padding: 0;
				background-color: #111827;
				height: 100vh;
				display: flex;
				align-items: center;
				justify-content: center;
				font-family: system-ui, -apple-system, sans-serif;
			}

			.container {
				text-align: center;
			}

			h1 {
				color: #ffffff;
				font-size: 1.875rem;
				font-weight: bold;
				margin-bottom: 1.5rem;
			}

			p {
				color: #9CA3AF;
				margin-bottom: 2rem;
			}

			.button {
				background-color: #4F46E5;
				color: #ffffff;
				padding: 0.75rem 1.5rem;
				border-radius: 0.5rem;
				text-decoration: none;
				font-weight: bold;
				transition: background-color 0.2s;
				display: inline-block;
			}

			.button:hover {
				background-color: #4338CA;
			}

			.button.btn-back {
				background-color: #6B7280;
			}

			.button.btn-back:hover {
				background-color: #4B5563;
			}
		</style>
	</head>

	<body>
		<div class="container">
			<h1>Continue to Linkvertise</h1>
			<p>Click the button below to continue to Linkvertise and earn rewards</p>
			<a href="/api/user/earn/l4r/linkvertise/earn/<?php echo $linkvertiseUUID; ?>" class="button">
				Continue to Linkvertise
			</a>
			<a href="/dashboard" class="button btn-back">
				Go back
			</a>
			<script src="https://publisher.linkvertise.com/cdn/linkvertise.js"></script>
			<script>linkvertise(<?php echo $config->getSetting(ConfigInterface::L4R_LINKVERTISE_USER_ID, '583258'); ?>, { whitelist: [], blacklist: [] });</script>
		</div>
	</body>

	</html>
	<?php
});

$router->get('/api/user/earn/l4r/linkvertise/earn/(.*)', function (string $code): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    global $eventManager;

    // Check if Linkvertise is enabled
    if ($config->getSetting(ConfigInterface::L4R_LINKVERTISE_ENABLED, 'false') !== 'true') {
        header('Location: /earn/links');
        exit;
    }

    $minToComplete = $config->getSetting(ConfigInterface::L4R_LINKVERTISE_MIN_TIME_TO_COMPLETE, 60);
    $coolDown = $config->getSetting(ConfigInterface::L4R_LINKVERTISE_COOLDOWN_TIME, 3600);
    $coinsPerLink = $config->getSetting(ConfigInterface::L4R_LINKVERTISE_COINS_PER_LINK, 60);

    // Validate code format
    if (empty($code) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $code)) {
        header('Location: /earn/links');
        $eventManager->emit(LinkForRewardEvent::onLinkInvalid(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
            'link' => $code,
        ]);
        exit;
    }

    $linkId = Linkvertise::convertCodeToId($code);
    if ($linkId === 0) {
        header('Location: /earn/links');
        $eventManager->emit(LinkForRewardEvent::onLinkInvalid(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
            'link' => $linkId,
        ]);
        exit;
    }

    $link = Linkvertise::getById($linkId);
    if (empty($link)) {
        header('Location: /earn/links');
        $eventManager->emit(LinkForRewardEvent::onLinkInvalid(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
            'link' => $linkId,
        ]);
        exit;
    }

    // Validate link ownership
    if ($link['user'] !== $session->getInfo(UserColumns::UUID, false)) {
        header('Location: /earn/links');
        $eventManager->emit(LinkForRewardEvent::onLinkInvalid(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
            'link' => $linkId,
        ]);
        exit;
    }

    // Check if link is already completed
    if ($link['completed'] == 'true') {
        header('Location: /earn/links');
        $eventManager->emit(LinkForRewardEvent::onLinkInvalid(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
            'link' => $linkId,
        ]);
        exit;
    }

    header('Content-Type: text/html');

    // Get the time when the link was created
    $createdAt = strtotime($link['created_at']);
    $now = time();
    $timeTaken = $now - $createdAt;

    // Check if user took less time than required
    if ($timeTaken < $minToComplete) {
        Linkvertise::delete($linkId);
        $eventManager->emit(LinkForRewardEvent::onLinkToEarly(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
            'link' => $linkId,
        ]);
        ?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Too Fast</title>
			<style>
				body {
					margin: 0;
					padding: 0;
					background-color: #111827;
					height: 100vh;
					display: flex;
					align-items: center;
					justify-content: center;
					font-family: system-ui, -apple-system, sans-serif;
				}
				.container {
					text-align: center;
				}
				h1 {
					color: #ffffff;
					font-size: 1.875rem;
					font-weight: bold;
					margin-bottom: 1.5rem;
				}
				p {
					color: #9CA3AF;
					margin-bottom: 2rem;
				}
				.button {
					background-color: #4F46E5;
					color: #ffffff;
					padding: 0.75rem 1.5rem;
					border-radius: 0.5rem;
					text-decoration: none;
					font-weight: bold;
					transition: background-color 0.2s;
					display: inline-block;
				}
				.button:hover {
					background-color: #4338CA;
				}
				.button.btn-back {
					background-color: #6B7280;
				}
				.button.btn-back:hover {
					background-color: #4B5563;
				}
			</style>
		</head>
		<body>
			<div class="container">
				<h1>Too Fast!</h1>
				<p>You completed the link too quickly. Please take at least <?php echo $minToComplete; ?> seconds to complete the link next time.</p>
				<a href="/earn/links" class="button btn-back">Go back</a>
			</div>
		</body>
		</html>
		<?php
        exit;
    }

    // User took enough time, give them coins
    Linkvertise::markAsCompleted($linkId);
    $currentCredits = (int) $session->getInfo(UserColumns::CREDITS, false);
    $coinsToAdd = (int) $coinsPerLink;
    $newTotal = (string) ($currentCredits + $coinsToAdd);
    $session->setInfo(UserColumns::CREDITS, (string) $newTotal, false);
    $eventManager->emit(LinkForRewardEvent::onLinkRedeemed(), [
        'user' => $session->getInfo(UserColumns::UUID, false),
        'link' => $linkId,
    ]);
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$user_redeemed_code,
        CloudFlareRealIP::getRealIP(),
        "Redeemed code: $code for $coinsPerLink credits"
    );
    // Show success page
    ?>
	<!DOCTYPE html>
	<html>
	<head>
		<title>Success</title>
		<style>
			body {
				margin: 0;
				padding: 0;
				background-color: #111827;
				height: 100vh;
				display: flex;
				align-items: center;
				justify-content: center;
				font-family: system-ui, -apple-system, sans-serif;
			}
			.container {
				text-align: center;
			}
			h1 {
				color: #ffffff;
				font-size: 1.875rem;
				font-weight: bold;
				margin-bottom: 1.5rem;
			}
			p {
				color: #9CA3AF;
				margin-bottom: 2rem;
			}
			.button {
				background-color: #4F46E5;
				color: #ffffff;
				padding: 0.75rem 1.5rem;
				border-radius: 0.5rem;
				text-decoration: none;
				font-weight: bold;
				transition: background-color 0.2s;
				display: inline-block;
			}
			.button:hover {
				background-color: #4338CA;
			}
			.button.btn-back {
				background-color: #6B7280;
			}
			.button.btn-back:hover {
				background-color: #4B5563;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<h1>Success!</h1>
			<p>You have successfully completed the link and earned <?php echo $coinsPerLink; ?> coins!</p>
			<a href="/earn/links" class="button btn-back">Go back</a>
		</div>
	</body>
	</html>
	<?php
});
