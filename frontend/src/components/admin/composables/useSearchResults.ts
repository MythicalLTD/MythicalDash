import { computed, type Ref } from 'vue';

export function useSearchResults(searchQuery: Ref<string>) {
    const adminBaseUri = '/mc-admin';

    const searchResults = [
        // Main Dashboard
        { id: 1, name: 'Dashboard', path: `${adminBaseUri}` },
        { id: 2, name: 'Health', path: `${adminBaseUri}/health` },

        // Locations
        { id: 3, name: 'Locations', path: `${adminBaseUri}/locations` },
        { id: 4, name: 'Create Location', path: `${adminBaseUri}/locations/create` },

        // Egg Categories
        { id: 7, name: 'Egg Categories', path: `${adminBaseUri}/egg-categories` },
        { id: 8, name: 'Create Egg Category', path: `${adminBaseUri}/egg-categories/create` },

        // Eggs
        { id: 11, name: 'Eggs', path: `${adminBaseUri}/eggs` },
        { id: 12, name: 'Create Egg', path: `${adminBaseUri}/eggs/create` },

        // Users
        { id: 15, name: 'Users', path: `${adminBaseUri}/users` },
        { id: 16, name: 'Edit User', path: `${adminBaseUri}/users/edit` },
        { id: 17, name: 'Delete User', path: `${adminBaseUri}/users/delete` },

        // Departments
        { id: 18, name: 'Departments', path: `${adminBaseUri}/departments` },
        { id: 19, name: 'Create Department', path: `${adminBaseUri}/departments/create` },

        // Tickets
        { id: 22, name: 'Tickets', path: `${adminBaseUri}/tickets` },

        // Announcements
        { id: 26, name: 'Announcements', path: `${adminBaseUri}/announcements` },
        { id: 27, name: 'Create Announcement', path: `${adminBaseUri}/announcements/create` },

        // Settings
        { id: 30, name: 'Settings', path: `${adminBaseUri}/settings` },
        { id: 31, name: 'Settings (Legacy)', path: `${adminBaseUri}/settings/old` },

        // Servers
        { id: 32, name: 'Servers', path: `${adminBaseUri}/servers` },
        { id: 33, name: 'Create Server', path: `${adminBaseUri}/servers/create` },

        // Queue
        { id: 36, name: 'Server Queue', path: `${adminBaseUri}/server-queue` },
        { id: 37, name: 'Create Server Queue', path: `${adminBaseUri}/server-queue/create` },

        // Mail Templates
        { id: 40, name: 'Mail Templates', path: `${adminBaseUri}/mail-templates` },
        { id: 41, name: 'Create Mail Template', path: `${adminBaseUri}/mail-templates/create` },

        // Redeems
        { id: 44, name: 'Redeem Codes', path: `${adminBaseUri}/redeems` },
        { id: 45, name: 'Create Redeem Code', path: `${adminBaseUri}/redeems/create` },

        // Plugins
        { id: 48, name: 'Plugins', path: `${adminBaseUri}/plugins` },

        // Backups
        { id: 52, name: 'Backups', path: `${adminBaseUri}/backups` },
        { id: 53, name: 'Create Backup', path: `${adminBaseUri}/backups/create` },

        // Images
        { id: 56, name: 'Images', path: `${adminBaseUri}/images` },
        { id: 57, name: 'Create Image', path: `${adminBaseUri}/images/create` },

        // Redirect Links
        { id: 60, name: 'Redirect Links', path: `${adminBaseUri}/redirect-links` },
        { id: 61, name: 'Create Redirect Link', path: `${adminBaseUri}/redirect-links/create` },

        // Roles
        { id: 64, name: 'Roles', path: `${adminBaseUri}/roles` },
        { id: 65, name: 'Create Role', path: `${adminBaseUri}/roles/create` },

        // J4R (Just 4 Runners) # Inside joke
        { id: 68, name: 'J4R Servers', path: `${adminBaseUri}/j4r` },
        { id: 69, name: 'Create J4R Server', path: `${adminBaseUri}/j4r/create` },
    ];

    const filteredResults = computed(() => {
        if (!searchQuery.value) return [];
        const query = searchQuery.value.toLowerCase();
        return searchResults.filter((result) => result.name.toLowerCase().includes(query));
    });

    return {
        searchResults,
        filteredResults,
    };
}
