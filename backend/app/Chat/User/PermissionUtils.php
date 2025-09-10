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

use MythicalDash\Chat\columns\UserColumns;

/**
 * Permission Utilities Class.
 *
 * This class provides static methods for permission checking and management
 * throughout the MythicalDash application.
 */
class PermissionUtils
{
    /**
     * Check if a user has a specific permission by their token.
     *
     * @param string $userToken The user's authentication token
     * @param string $permission The permission to check (e.g., 'admin.users.create')
     *
     * @return bool True if the user has the permission, false otherwise
     */
    public static function userHasPermission(string $userToken, string $permission): bool
    {
        try {
            // Get the user's role ID
            $roleId = (int) User::getInfo($userToken, UserColumns::ROLE_ID, false);

            // Check if the role has the specific permission
            return Permissions::hasPermission($roleId, $permission);
        } catch (\Exception $e) {
            // Log the error but don't expose it
            error_log('Failed to check user permission: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a user has any of the specified permissions.
     *
     * @param string $userToken The user's authentication token
     * @param array $permissions Array of permissions to check
     *
     * @return bool True if the user has at least one permission, false otherwise
     */
    public static function userHasAnyPermission(string $userToken, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::userHasPermission($userToken, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user has all of the specified permissions.
     *
     * @param string $userToken The user's authentication token
     * @param array $permissions Array of permissions to check
     *
     * @return bool True if the user has all permissions, false otherwise
     */
    public static function userHasAllPermissions(string $userToken, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::userHasPermission($userToken, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for a user by their token.
     *
     * @param string $userToken The user's authentication token
     *
     * @return array Array of permissions with their granted status
     */
    public static function getUserPermissions(string $userToken): array
    {
        try {
            $roleId = (int) User::getInfo($userToken, UserColumns::ROLE_ID, false);

            return Permissions::getPermissionsByRole($roleId);
        } catch (\Exception $e) {
            error_log('Failed to get user permissions: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get a user's role information by their token.
     *
     * @param string $userToken The user's authentication token
     *
     * @return array|null Role information or null if not found
     */
    public static function getUserRole(string $userToken): ?array
    {
        try {
            $roleId = (int) User::getInfo($userToken, UserColumns::ROLE_ID, false);

            return Roles::getRole($roleId);
        } catch (\Exception $e) {
            error_log('Failed to get user role: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Check if a user has admin access with a specific permission.
     *
     * @param string $userToken The user's authentication token
     * @param string $permission The permission to check
     *
     * @return bool True if user has admin access and the permission, false otherwise
     */
    public static function userCanAccessAdminWithPermission(string $userToken, string $permission): bool
    {
        try {
            $roleId = (int) User::getInfo($userToken, UserColumns::ROLE_ID, false);

            // Check if the role has the specific permission (admin role check is now handled in Permissions::hasPermission)
            return Permissions::hasPermission($roleId, $permission);
        } catch (\Exception $e) {
            error_log('Failed to check admin permission: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Validate if a permission string is valid (exists in the permissions constants).
     *
     * @param string $permission The permission to validate
     *
     * @return bool True if the permission is valid, false otherwise
     */
    public static function isValidPermission(string $permission): bool
    {
        // Get all available permissions from the Permissions class
        $allPermissions = \MythicalDash\Permissions::getAll();

        foreach ($allPermissions as $perm) {
            if ($perm['value'] === $permission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all available permissions grouped by category.
     *
     * @return array Array of permissions grouped by category
     */
    public static function getAllPermissionsGrouped(): array
    {
        $allPermissions = \MythicalDash\Permissions::getAll();
        $grouped = [];

        foreach ($allPermissions as $permission) {
            $category = $permission['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $permission;
        }

        return $grouped;
    }

    /**
     * Check if a role has a specific permission.
     *
     * @param int $roleId The role ID
     * @param string $permission The permission to check
     *
     * @return bool True if the role has the permission, false otherwise
     */
    public static function roleHasPermission(int $roleId, string $permission): bool
    {
        return Permissions::hasPermission($roleId, $permission);
    }

    /**
     * Get all permissions for a specific role.
     *
     * @param int $roleId The role ID
     *
     * @return array Array of permissions for the role
     */
    public static function getRolePermissions(int $roleId): array
    {
        return Permissions::getPermissionsByRole($roleId);
    }

    /**
     * Create a permission for a role.
     *
     * @param int $roleId The role ID
     * @param string $permission The permission name
     * @param string $granted Whether the permission is granted ('true' or 'false')
     *
     * @return bool True if the permission was created, false otherwise
     */
    public static function createRolePermission(int $roleId, string $permission, string $granted = 'true'): bool
    {
        return Permissions::createPermission($roleId, $permission, $granted);
    }

    /**
     * Delete a permission.
     *
     * @param int $permissionId The permission ID
     *
     * @return bool True if the permission was deleted, false otherwise
     */
    public static function deletePermission(int $permissionId): bool
    {
        return Permissions::deletePermission($permissionId);
    }

    /**
     * Check if a user can perform an action based on multiple conditions.
     * This is useful for complex permission checks that require multiple conditions.
     *
     * @param string $userToken The user's authentication token
     * @param array $requiredPermissions Array of required permissions (all must be true)
     * @param array $optionalPermissions Array of optional permissions (at least one must be true)
     * @param bool $requireAdmin Whether admin access is required
     *
     * @return bool True if the user meets all conditions, false otherwise
     */
    public static function userCanPerformAction(
        string $userToken,
        array $requiredPermissions = [],
        array $optionalPermissions = [],
        bool $requireAdmin = false,
    ): bool {
        try {
            $roleId = (int) User::getInfo($userToken, UserColumns::ROLE_ID, false);

            // Check required permissions (all must be true)
            foreach ($requiredPermissions as $permission) {
                if (!Permissions::hasPermission($roleId, $permission)) {
                    return false;
                }
            }

            // Check optional permissions (at least one must be true)
            if (!empty($optionalPermissions)) {
                $hasOptional = false;
                foreach ($optionalPermissions as $permission) {
                    if (Permissions::hasPermission($roleId, $permission)) {
                        $hasOptional = true;
                        break;
                    }
                }
                if (!$hasOptional) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log('Failed to check user action permissions: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a summary of user permissions for debugging or logging purposes.
     *
     * @param string $userToken The user's authentication token
     *
     * @return array Summary of user permissions
     */
    public static function getUserPermissionSummary(string $userToken): array
    {
        try {
            $roleId = (int) User::getInfo($userToken, UserColumns::ROLE_ID, false);
            $role = Roles::getRole($roleId);
            $permissions = Permissions::getPermissionsByRole($roleId);

            $grantedPermissions = [];
            $deniedPermissions = [];

            foreach ($permissions as $permission) {
                if ($permission['granted'] === 'true') {
                    $grantedPermissions[] = $permission['permission'];
                } else {
                    $deniedPermissions[] = $permission['permission'];
                }
            }

            return [
                'role_id' => $roleId,
                'role_name' => $role['name'] ?? 'Unknown',
                'role_display_name' => $role['real_name'] ?? 'Unknown',
                'total_permissions' => count($permissions),
                'granted_permissions' => $grantedPermissions,
                'denied_permissions' => $deniedPermissions,
                'granted_count' => count($grantedPermissions),
                'denied_count' => count($deniedPermissions),
            ];
        } catch (\Exception $e) {
            error_log('Failed to get user permission summary: ' . $e->getMessage());

            return [
                'error' => 'Failed to get permission summary',
                'role_id' => null,
                'role_name' => 'Unknown',
                'role_display_name' => 'Unknown',
                'total_permissions' => 0,
                'granted_permissions' => [],
                'denied_permissions' => [],
                'granted_count' => 0,
                'denied_count' => 0,
            ];
        }
    }
}
