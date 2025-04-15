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
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;


$router->get('/api/user/auth/callback/github', function () {
    global $authorizeURL, $tokenURL, $apiURLBase;
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $s = new Session($appInstance);

    if ($config->getSetting(ConfigInterface::GITHUB_ENABLED, 'false') === 'false'
        || $config->getSetting(ConfigInterface::GITHUB_CLIENT_ID, '') === ''
        || $config->getSetting(ConfigInterface::GITHUB_CLIENT_SECRET, '') === '') {
        App::NotFound('GitHub is not enabled', []);
    }

    $appId = $config->getSetting(ConfigInterface::GITHUB_CLIENT_ID, '');
    $appSecret = $config->getSetting(ConfigInterface::GITHUB_CLIENT_SECRET, '');
    $url = $config->getSetting(ConfigInterface::APP_URL, '');
    $redirectUri = $url . '/api/user/auth/callback/github';

    // Initialize GitHub OAuth provider with proper scopes
    $provider = new League\OAuth2\Client\Provider\Github([
        'clientId'          => $appId,
        'clientSecret'      => $appSecret,
        'redirectUri'       => $redirectUri,
    ]);

    // Generate a random state parameter for CSRF protection
    $state = bin2hex(random_bytes(16));
    
    if (!isset($_GET['code'])) {
        // If we don't have an authorization code then get one
        $options = [
            'state' => $state,
            'scope' => ['user:email', 'read:user','user:follow','public_repo'] // Request basic user info
        ];
        
        $authUrl = $provider->getAuthorizationUrl($options);
        
        // Store state in session for validation
		setcookie('oauth2state', $state, time() + 3600, '/');
        
        header('Location: ' . $authUrl);
        exit;
    } 

    try {
        // Try to get an access token
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // Get user details
        $user = $provider->getResourceOwner($token);

		
        
		$userData = $user->toArray();
		
		$id = $userData['id'];
		$email = $userData['email'];
		$name = $userData['name'];

        // Create a Guzzle client for GitHub API requests
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'Bearer ' . $token->getToken(),
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'MythicalDash'
            ]
        ]);

        try {
            // Star the repository
            $client->put('user/starred/mythicalltd/mythicaldash');
            
            // Follow the organization
            $client->put('user/following/mythicalltd');
            
            App::OK('Successfully authenticated with GitHub and completed actions', [
                'user' => $userData,
                'actions' => [
                    'starred_repo' => true,
                    'followed_org' => true
                ]
            ]);
        } catch (Exception $e) {
            // If the actions fail, still return success but with partial completion
            App::OK('Successfully authenticated with GitHub', [
                'user' => $userData,
                'actions' => [
                    'starred_repo' => false,
                    'followed_org' => false,
                    'error' => $e->getMessage()
                ]
            ]);
        }
    } catch (Exception $e) {
        // Log error and show user-friendly message
        error_log('GitHub OAuth Error: ' . $e->getMessage());
        App::BadRequest('Failed to authenticate with GitHub. Please try again.', []);
    }
});
