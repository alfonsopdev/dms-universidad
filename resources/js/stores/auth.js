import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const permissions = ref([]);
    const loaded = ref(false);

    const isAuthenticated = computed(() => !!user.value);

    async function fetchUser() {
        try {
            const response = await axios.get('/api/user');
            if (response.status === 200) {
                user.value = response.data.user;
                permissions.value = response.data.permissions || [];
            }
        } catch (error) {
            user.value = null;
            permissions.value = [];
        } finally {
            loaded.value = true;
        }
    }

    function hasPermission(permission) {
        return permissions.value.includes(permission);
    }

    async function logout() {
        await axios.post('/logout');
        user.value = null;
        permissions.value = [];
        window.location.href = '/login';
    }

    return { user, permissions, isAuthenticated, loaded, fetchUser, hasPermission, logout };
});