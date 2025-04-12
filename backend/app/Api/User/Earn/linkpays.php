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
use MythicalSystems\User\UUIDManager;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Services\LinkPays\LinkPays;
use MythicalDash\Chat\Earn\LinkPays as LinkPaysDB;

$router->get('/api/user/earn/l4r/linkpays/start', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    header('Content-Type: text/html');

    // Check if LinkPays is enabled
    if ($config->getSetting(ConfigInterface::L4R_LINKPAYS_ENABLED, 'false') !== 'true') {
        header('Location: /earn/links?error=linkpays_not_enabled');
        exit;
    }

    $dayLimit = $config->getSetting(ConfigInterface::L4R_LINKPAYS_DAILY_LIMIT, 5);
    $coolDown = $config->getSetting(ConfigInterface::L4R_LINKPAYS_COOLDOWN_TIME, 3600);
    $appUrl = $config->getSetting(ConfigInterface::APP_URL, 'https://app.mythicaldash.com');

    $dayCount = 0;
    $links = LinkPaysDB::getAllByUser($session->getInfo(UserColumns::UUID, false), 35);
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
                ?>
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

    $ShareUSUUID = UUIDManager::generateUUID();
    $id = LinkPaysDB::create($ShareUSUUID, $session->getInfo(UserColumns::UUID, false));
    if ($id === 0) {
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
				<p>Failed to create linkpays code</p>
			</div>
		</body>

		</html>
		<?php
        return;
    }
    $finalLink = $appUrl . '/api/user/earn/l4r/linkpays/earn/' . $ShareUSUUID;
    try {
        $shareUS = new LinkPays($config->getSetting(ConfigInterface::L4R_LINKPAYS_API_KEY, ''));
        $link = $shareUS->getLink($finalLink);
        header('Location: ' . $link);
    } catch (Exception $e) {
        header('Location: /earn/links?error=linkpays_error');

        return;
    }

});

$router->get('/api/user/earn/l4r/linkpays/earn/(.*)', function (string $code): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $config = $appInstance->getConfig();
    // Check if LinkPays is enabled
    if ($config->getSetting(ConfigInterface::L4R_LINKPAYS_ENABLED, 'false') !== 'true') {
        header('Location: /earn/links');
        exit;
    }

    $minToComplete = $config->getSetting(ConfigInterface::L4R_LINKPAYS_MIN_TIME_TO_COMPLETE, 60);
    $coolDown = $config->getSetting(ConfigInterface::L4R_LINKPAYS_COOLDOWN_TIME, 3600);
    $coinsPerLink = $config->getSetting(ConfigInterface::L4R_LINKPAYS_COINS_PER_LINK, 60);

    // Validate code format
    if (empty($code) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $code)) {
        header('Location: /earn/links?error=invalid_code');
        exit;
    }

    $linkId = LinkPaysDB::convertCodeToId($code);
    if ($linkId === 0) {
        header('Location: /earn/links?error=invalid_code');
        exit;
    }

    $link = LinkPaysDB::getById($linkId);
    if (empty($link)) {
        header('Location: /earn/links?error=invalid_code');
        exit;
    }

    // Validate link ownership
    if ($link['user'] !== $session->getInfo(UserColumns::UUID, false)) {
        header('Location: /earn/links?error=invalid_ownership');
        exit;
    }

    // Check if link is already completed
    if ($link['completed'] == 'true') {
        header('Location: /earn/links?error=already_completed');
        exit;
    }
    // Get the time when the link was created
    $createdAt = strtotime($link['created_at']);
    $now = time();
    $timeTaken = $now - $createdAt;

    // Check if user took less time than required
    if ($timeTaken < $minToComplete) {
        LinkPaysDB::delete($linkId);
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
    LinkPaysDB::markAsCompleted($linkId);
    $session->setInfo(UserColumns::CREDITS, (int) $session->getInfo(UserColumns::CREDITS, false) + (int) $coinsPerLink, false);
    header('Location: /earn/links?success=true');
    exit;
});
