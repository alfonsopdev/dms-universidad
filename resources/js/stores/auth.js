import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const permissions = ref([]);

    const isAuthenticated = computed(() => !!user.value);

    async function fetchUser() {
        try {
            const response = await axios.get('/api/user');
            user.value = response.data.user;
            permissions.value = response.data.permissions;
        } catch {
            user.value = null;
            permissions.value = [];
        }
    }

    function hasPermission(permission) {
        return permissions.value.includes(permission);
    }

    async function logout() {
        await axios.post('/logout');
        user.value = null;
        permissions.value = [];
    }

    return { user, permissions, isAuthenticated, fetchUser, hasPermission, logout };
});