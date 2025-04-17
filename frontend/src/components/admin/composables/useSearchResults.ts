import { computed, type Ref } from 'vue';

export function useSearchResults(searchQuery: Ref<string>) {
    const adminBaseUri = '/mc-admin';

    const searchResults = [
        { id: 1, name: 'Dashboard', path: `${adminBaseUri}` },
        { id: 2, name: 'Locations', path: `${adminBaseUri}/locations` },
        { id: 3, name: 'Create Location', path: `${adminBaseUri}/locations/create` },
        { id: 4, name: 'Egg Categories', path: `${adminBaseUri}/egg-categories` },
        { id: 5, name: 'Create Egg Category', path: `${adminBaseUri}/egg-categories/create` },
        { id: 6, name: 'Tickets', path: `${adminBaseUri}/tickets` },
        { id: 7, name: 'Users', path: `${adminBaseUri}/users` },
        { id: 8, name: 'Departments', path: `${adminBaseUri}/departments` },
        { id: 9, name: 'Create Department', path: `${adminBaseUri}/departments/create` },
        { id: 10, name: 'Server Queue', path: `${adminBaseUri}/server-queue` },
        { id: 11, name: 'Create Server Queue', path: `${adminBaseUri}/server-queue/create` },
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
