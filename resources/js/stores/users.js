import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useUserStore = defineStore('users', () => {
    const users      = ref([]);
    const user       = ref(null);
    const pagination = ref({});
    const roles      = ref([]);
    const loading    = ref(false);
    const error      = ref(null);

    async function fetchUsers(params = {}) {
        loading.value = true;
        try {
            const res    = await axios.get('/api/users', { params });
            users.value  = res.data.data;
            pagination.value = res.data;
        } catch(e) {
            error.value = e.message;
        } finally {
            loading.value = false;
        }
    }

    async function fetchRoles() {
        const res  = await axios.get('/api/users/roles');
        roles.value = res.data;
    }

    async function createUser(data) {
        const res = await axios.post('/api/users', data);
        await fetchUsers();
        return res.data;
    }

    async function updateUser(id, data) {
        const res = await axios.put(`/api/users/${id}`, data);
        await fetchUsers();
        return res.data;
    }

    async function deleteUser(id) {
        await axios.delete(`/api/users/${id}`);
        await fetchUsers();
    }

    async function importUsers(formData) {
        const res = await axios.post('/api/users/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        await fetchUsers();
        return res.data;
    }

    return {
        users, user, pagination, roles, loading, error,
        fetchUsers, fetchRoles, createUser,
        updateUser, deleteUser, importUsers,
    };
});