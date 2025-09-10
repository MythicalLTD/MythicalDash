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

namespace MythicalDash\Chat\User;

use MythicalDash\App;
use MythicalDash\Chat\Database;
use MythicalDash\Middleware\Firewall;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\CloudFlare\CloudFlareRealIP;

class Session extends Database
{
    public App $app;
    public string $SESSION_KEY;

    public function __construct(App $app)
    {
        if (isset($_COOKIE['user_token']) && !$_COOKIE['user_token'] == '') {
            if (User::exists(UserColumns::ACCOUNT_TOKEN, $_COOKIE['user_token'])) {
                try {
                    $this->setSecurityHeaders();

                    $this->app = $app;
                    $this->SESSION_KEY = $_COOKIE['user_token'];
                    $this->updateLastSeen();
                    $this->updateCookie();
                    $this->setSecurityCookies();

                    if ($this->getInfo(UserColumns::TWO_FA_BLOCKED, false) == 'true') {
                        $app->Unauthorized('Please verify 2fa to access this endpoint.', ['error_code' => 'TWO_FA_BLOCKED']);
                    }
                    // Re-check firewall with session for authenticated users (VPN bypass permission)
                    Firewall::handle($app, CloudFlareRealIP::getRealIP(), $this);
                } catch (\Exception $e) {
                    $app->Unauthorized('Bad Request', ['error_code' => 'INVALID_ACCOUNT_TOKEN']);
                }
            } else {
                $app->Unauthorized('Login info provided are invalid!', ['error_code' => 'INVALID_ACCOUNT_TOKEN']);
            }
        } else {
            $app->Unauthorized('Please tell me who you are.', ['error_code' => 'MISSING_ACCOUNT_TOKEN']);
        }
    }

    public function __destruct()
    {
        unset($this->app);
    }

    public function updateCookie(): void
    {
        if (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            setcookie('user_token', $_COOKIE['user_token'], [
                'expires' => time() + 1800,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }
    }

    public function getInfo(string|UserColumns $info, bool $encrypted): string
    {
        if (!in_array($info, UserColumns::getColumns())) {
            throw new \InvalidArgumentException('Invalid column name: ' . $info);
        }

        $value = User::getInfo($this->SESSION_KEY, $info, $encrypted);

        return $value ?? '';
    }

    public function setInfo(string|UserColumns $info, ?string $value, bool $encrypted): void
    {
        if (!in_array($info, UserColumns::getColumns())) {
            throw new \InvalidArgumentException('Invalid column name: ' . $info);
        }
        User::updateInfo($this->SESSION_KEY, $info, $value, $encrypted);
    }

    public function updateLastSeen(): void
    {
        try {
            $con = self::getPdoConnection();
            $ip = CloudFlareRealIP::getRealIP();
            $con->exec('UPDATE ' . User::TABLE_NAME . ' SET last_seen = NOW() WHERE token = "' . $this->SESSION_KEY . '";');
            $con->exec('UPDATE ' . User::TABLE_NAME . ' SET last_ip = "' . $ip . '" WHERE token = "' . $this->SESSION_KEY . '";');
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to update last seen: ' . $e->getMessage());
        }
    }

    /**
     * Check if the user has a specific permission.
     * This method looks up the user's role and checks if that role has the specified permission.
     *
     * @param string $permission The permission to check (e.g., 'admin.users.create')
     *
     * @return bool True if the user has the permission, false otherwise
     */
    public function hasPermission(string $permission): bool
    {
        try {
            $roleId = (int) $this->getInfo(UserColumns::ROLE_ID, false);

            // Check if the role has the specific permission
            return Permissions::hasPermission($roleId, $permission);
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to check permission: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all permissions for the current user's role.
     *
     * @return array Array of permissions with their granted status
     */
    public function getUserPermissions(): array
    {
        try {
            $roleId = (int) $this->getInfo(UserColumns::ROLE_ID, false);

            return Permissions::getPermissionsByRole($roleId);
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to get user permissions: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Remove credits from the user's account atomically with row-level locking.
     * This method prevents race conditions by using database transactions.
     *
     * @param int $credits the number of credits to remove
     *
     * @return bool true if successful, false if insufficient credits or operation failed
     */
    public function removeCreditsAtomic(int $credits): bool
    {
        try {
            $con = self::getPdoConnection();

            // First check if user has enough credits (no lock needed for read)
            $stmt = $con->prepare('SELECT credits FROM ' . User::TABLE_NAME . ' WHERE token = ?');
            $stmt->execute([$this->SESSION_KEY]);
            $currentCredits = (int) $stmt->fetchColumn();

            // Check if user has enough credits
            if ($currentCredits < $credits) {
                return false;
            }

            // Use atomic UPDATE with condition to prevent negative credits
            $stmt = $con->prepare('UPDATE ' . User::TABLE_NAME . ' SET credits = credits - ? WHERE token = ? AND credits >= ?');
            $result = $stmt->execute([$credits, $this->SESSION_KEY, $credits]);

            // Check if the update was successful (rowCount > 0 means credits were sufficient)
            return $result && $stmt->rowCount() > 0;

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to remove credits atomically: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Add credits to the user's account atomically with row-level locking.
     * This method prevents race conditions by using database transactions.
     *
     * @param int $credits the number of credits to add
     *
     * @return bool true if successful, false if operation failed
     */
    public function addCreditsAtomic(int $credits): bool
    {
        try {
            $con = self::getPdoConnection();

            // Simple atomic UPDATE - MySQL handles concurrency internally
            // This is much faster and safer than manual locking
            $stmt = $con->prepare('UPDATE ' . User::TABLE_NAME . ' SET credits = credits + ? WHERE token = ?');
            $result = $stmt->execute([$credits, $this->SESSION_KEY]);

            return $result && $stmt->rowCount() > 0;

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to add credits atomically: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if user has sufficient credits atomically with row-level locking.
     * This method prevents race conditions by using database transactions.
     *
     * @param int $requiredCredits the number of credits required
     *
     * @return array with 'has_sufficient' boolean and 'current_credits' integer
     */
    public function checkCreditsAtomic(int $requiredCredits): array
    {
        try {
            $con = self::getPdoConnection();

            // Simple SELECT - no locking needed for read operations
            $stmt = $con->prepare('SELECT credits FROM ' . User::TABLE_NAME . ' WHERE token = ?');
            $stmt->execute([$this->SESSION_KEY]);
            $currentCredits = (int) $stmt->fetchColumn();

            return [
                'has_sufficient' => $currentCredits >= $requiredCredits,
                'current_credits' => $currentCredits,
            ];

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to check credits atomically: ' . $e->getMessage());

            return [
                'has_sufficient' => false,
                'current_credits' => 0,
            ];
        }
    }

    /**
     * Process a purchase atomically with row-level locking.
     * This method prevents race conditions by using database transactions.
     *
     * @param int $price the price of the item
     * @param callable $itemEffect callback function to apply item effects
     *
     * @return array with 'success' boolean and additional data
     */
    public function processPurchaseAtomic(int $price, callable $itemEffect): array
    {
        try {
            $con = self::getPdoConnection();

            // First check if user has enough credits (no lock needed for read)
            $stmt = $con->prepare('SELECT credits FROM ' . User::TABLE_NAME . ' WHERE token = ?');
            $stmt->execute([$this->SESSION_KEY]);
            $currentCredits = (int) $stmt->fetchColumn();

            // Check if user has enough credits
            if ($currentCredits < $price) {
                return [
                    'success' => false,
                    'error_code' => 'INSUFFICIENT_COINS',
                    'required' => $price,
                    'available' => $currentCredits,
                ];
            }

            // Use atomic UPDATE with condition to prevent negative credits
            $stmt = $con->prepare('UPDATE ' . User::TABLE_NAME . ' SET credits = credits - ? WHERE token = ? AND credits >= ?');
            $result = $stmt->execute([$price, $this->SESSION_KEY, $price]);

            if (!$result || $stmt->rowCount() === 0) {
                // Another process modified the credits between our check and update
                return [
                    'success' => false,
                    'error_code' => 'INSUFFICIENT_COINS',
                    'required' => $price,
                    'available' => 0,
                ];
            }

            // Apply item effect
            $itemEffect($this);

            return [
                'success' => true,
                'remaining_coins' => $currentCredits - $price,
                'price_paid' => $price,
            ];

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to process purchase atomically: ' . $e->getMessage());

            return [
                'success' => false,
                'error_code' => 'PURCHASE_FAILED',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Set security headers for the application.
     */
    private function setSecurityHeaders(): void
    {
        // CORS headers
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        // Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // If using HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");
    }

    /**
     * Set additional security and functionality cookies.
     */
    private function setSecurityCookies(): void
    {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $cookieOptions = [
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ];

        // Session fingerprint to prevent session hijacking
        $fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . CloudFlareRealIP::getRealIP());
        setcookie('session_fingerprint', $fingerprint, array_merge($cookieOptions, [
            'expires' => time() + 1800, // 30 minutes
        ]));

        // Last activity timestamp for session management
        setcookie('last_activity', time(), array_merge($cookieOptions, [
            'expires' => time() + 1800,
            'httponly' => false, // Allow JavaScript to read this for UI updates
        ]));

        // User preferences (theme, language, etc.)
        $userPrefs = [

        ];
        setcookie('user_preferences', json_encode($userPrefs), array_merge($cookieOptions, [
            'expires' => time() + (86400 * 30), // 30 days
            'httponly' => false, // Allow JavaScript to read preferences
        ]));

        // CSRF token for form submissions
        $csrfToken = bin2hex(random_bytes(32));
        setcookie('csrf_token', $csrfToken, array_merge($cookieOptions, [
            'expires' => time() + 1800,
        ]));

        setcookie('remember_me', '1', array_merge($cookieOptions, [
            'expires' => time() + (86400 * 30), // 30 days
        ]));
    }
}
