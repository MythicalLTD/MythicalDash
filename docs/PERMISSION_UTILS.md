# Permission Utilities Documentation

This document explains how to use the permission utility system in MythicalDash for checking user permissions throughout the application.

## Overview

The permission system consists of two main components:

1. **Session Class Methods** - Instance methods for checking permissions of the current user
2. **PermissionUtils Class** - Static utility methods for permission checking and management

## Session Class Methods

The `Session` class now includes several methods for permission checking:

### Basic Permission Check

```php
$session = new MythicalDash\Chat\User\Session($app);

// Check if user has a specific permission
if ($session->hasPermission('admin.users.create')) {
    // User has permission
} else {
    // User does not have permission
}
```

### Multiple Permission Checks

```php
// Check if user has any of the specified permissions
$permissions = ['admin.users.create', 'admin.users.edit', 'admin.users.delete'];
if ($session->hasAnyPermission($permissions)) {
    // User has at least one permission
}

// Check if user has all of the specified permissions
if ($session->hasAllPermissions($permissions)) {
    // User has all permissions
}
```

### Get User Information

```php
// Get all permissions for the current user's role
$userPermissions = $session->getUserPermissions();

// Get the user's role information
$userRole = $session->getUserRole();

// Check admin access with specific permission
if ($session->canAccessAdminWithPermission('admin.users.create')) {
    // User has admin access and the specific permission
}
```

## PermissionUtils Class

The `PermissionUtils` class provides static methods for permission checking and management:

### Basic Permission Checking

```php
use MythicalDash\Chat\User\PermissionUtils;

$userToken = 'user_token_here';

// Check if a user has a specific permission
if (PermissionUtils::userHasPermission($userToken, 'admin.users.create')) {
    // User has permission
}

// Check if user has any of multiple permissions
$permissions = ['admin.users.create', 'admin.users.edit'];
if (PermissionUtils::userHasAnyPermission($userToken, $permissions)) {
    // User has at least one permission
}

// Check if user has all permissions
if (PermissionUtils::userHasAllPermissions($userToken, $permissions)) {
    // User has all permissions
}
```

### Complex Permission Checks

```php
// Check if user can perform an action with multiple conditions
$canPerform = PermissionUtils::userCanPerformAction(
    $userToken,
    ['admin.users.list'], // Required permissions (all must be true)
    ['admin.users.create', 'admin.users.edit'], // Optional permissions (at least one must be true)
    true // Require admin access
);
```

### User Information

```php
// Get all permissions for a user
$userPermissions = PermissionUtils::getUserPermissions($userToken);

// Get user's role information
$userRole = PermissionUtils::getUserRole($userToken);

// Get detailed permission summary
$summary = PermissionUtils::getUserPermissionSummary($userToken);
```

### Role Management

```php
// Check if a role has a specific permission
if (PermissionUtils::roleHasPermission($roleId, 'admin.users.create')) {
    // Role has permission
}

// Get all permissions for a role
$rolePermissions = PermissionUtils::getRolePermissions($roleId);

// Create a permission for a role
PermissionUtils::createRolePermission($roleId, 'admin.users.create', 'true');

// Delete a permission
PermissionUtils::deletePermission($permissionId);
```

### Utility Methods

```php
// Validate if a permission string is valid
if (PermissionUtils::isValidPermission('admin.users.create')) {
    // Permission is valid
}

// Get all available permissions grouped by category
$groupedPermissions = PermissionUtils::getAllPermissionsGrouped();
```

## Using Permission Constants

Always use the permission constants from the `Permissions` class:

```php
use MythicalDash\Permissions;

// Instead of hardcoding permission strings
if ($session->hasPermission('admin.users.create')) {
    // Use constants
}

// Use constants for better maintainability
if ($session->hasPermission(Permissions::ADMIN_USERS_CREATE)) {
    // Better approach
}
```

## API Endpoint Examples

### Simple Permission Check

```php
$router->get('/api/admin/users', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    
    $session = new MythicalDash\Chat\User\Session($appInstance);
    
    if ($session->hasPermission(Permissions::ADMIN_USERS_LIST)) {
        // Return users list
        $appInstance->OK('Users retrieved successfully', ['users' => $users]);
    } else {
        $appInstance->Forbidden('Insufficient permissions');
    }
});
```

### Complex Permission Check

```php
$router->post('/api/admin/users/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    
    $session = new MythicalDash\Chat\User\Session($appInstance);
    $userToken = $session->SESSION_KEY;
    
    // Check if user can perform the action
    $canCreate = PermissionUtils::userCanPerformAction(
        $userToken,
        [Permissions::ADMIN_USERS_CREATE], // Required
        [], // Optional
        true // Admin required
    );
    
    if ($canCreate) {
        // Create user logic
        $appInstance->OK('User created successfully');
    } else {
        $appInstance->Forbidden('Insufficient permissions');
    }
});
```

### Multiple Permission Check

```php
$router->get('/api/admin/users/(.*)', function (int $userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    
    $session = new MythicalDash\Chat\User\Session($appInstance);
    
    // Check if user has any user management permission
    $permissions = [
        Permissions::ADMIN_USERS_VIEW,
        Permissions::ADMIN_USERS_EDIT,
        Permissions::ADMIN_USERS_DELETE
    ];
    
    if ($session->hasAnyPermission($permissions)) {
        // Return user details
        $appInstance->OK('User details retrieved', ['user' => $user]);
    } else {
        $appInstance->Forbidden('Insufficient permissions');
    }
});
```

## Frontend Integration

### Vue.js Permission Checking

```javascript
// Check permissions on the frontend
const checkPermission = async (permission) => {
    try {
        const response = await fetch(`/api/admin/example/check-permission/${permission}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            return data.granted;
        }
        return false;
    } catch (error) {
        console.error('Error checking permission:', error);
        return false;
    }
};

// Usage in Vue component
const canCreateUsers = ref(false);

onMounted(async () => {
    canCreateUsers.value = await checkPermission('admin.users.create');
});
```

### Conditional Rendering

```vue
<template>
    <div>
        <!-- Show create button only if user has permission -->
        <button 
            v-if="canCreateUsers" 
            @click="createUser"
            class="btn btn-primary"
        >
            Create User
        </button>
        
        <!-- Show edit button only if user has permission -->
        <button 
            v-if="canEditUsers" 
            @click="editUser"
            class="btn btn-secondary"
        >
            Edit User
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const canCreateUsers = ref(false);
const canEditUsers = ref(false);

onMounted(async () => {
    canCreateUsers.value = await checkPermission('admin.users.create');
    canEditUsers.value = await checkPermission('admin.users.edit');
});
</script>
```

## Best Practices

1. **Always use permission constants** instead of hardcoded strings
2. **Check permissions early** in your API endpoints
3. **Use appropriate HTTP status codes** (403 Forbidden for permission denied)
4. **Log permission failures** for security monitoring
5. **Cache permission results** when possible to improve performance
6. **Use the most specific permission** for each action
7. **Combine permissions logically** using the utility methods

## Security Considerations

1. **Never trust client-side permission checks** - always verify on the server
2. **Use HTTPS** for all permission-related requests
3. **Log permission failures** for security auditing
4. **Implement rate limiting** on permission check endpoints
5. **Validate permission strings** before using them in queries
6. **Use prepared statements** to prevent SQL injection

## Error Handling

```php
try {
    if ($session->hasPermission(Permissions::ADMIN_USERS_CREATE)) {
        // Perform action
    } else {
        $appInstance->Forbidden('Insufficient permissions');
    }
} catch (\Exception $e) {
    // Log the error
    $appInstance->getLogger()->error('Permission check failed: ' . $e->getMessage());
    
    // Return a generic error to the client
    $appInstance->InternalServerError('Permission check failed');
}
```

## Performance Optimization

1. **Cache permission results** in memory when possible
2. **Use database indexes** on permission tables
3. **Batch permission checks** when checking multiple permissions
4. **Use connection pooling** for database queries
5. **Implement permission caching** with TTL (Time To Live)

This permission system provides a robust, secure, and flexible way to manage user permissions throughout the MythicalDash application. 