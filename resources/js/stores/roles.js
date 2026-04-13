import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useRoleStore = defineStore('roles', () => {
    const roles       = ref([]);
    const permissions = ref([]);
    const grouped     = ref([]);
    const loading     = ref(false);

    async function fetchRoles() {
        loading.value = true;
        try {
            const res   = await axios.get('/api/roles');
            roles.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchPermissions() {
        const res         = await axios.get('/api/permissions');
        permissions.value = res.data.all;
        grouped.value     = res.data.grouped;
    }

    async function createRole(data) {
        const res = await axios.post('/api/roles', data);
        await fetchRoles();
        return res.data;
    }

    async function updateRole(id, data) {
        const res = await axios.put(`/api/roles/${id}`, data);
        await fetchRoles();
        return res.data;
    }

    async function deleteRole(id) {
        await axios.delete(`/api/roles/${id}`);
        await fetchRoles();
    }

    async function assignRolesToUser(userId, roles) {
        const res = await axios.post('/api/roles/assign', {
            user_id: userId,
            roles,
        });
        return res.data;
    }

    async function createPermission(name) {
        const res = await axios.post('/api/permissions', { name });
        await fetchPermissions();
        return res.data;
    }

    async function deletePermission(id) {
        await axios.delete(`/api/permissions/${id}`);
        await fetchPermissions();
    }

    return {
        roles, permissions, grouped, loading,
        fetchRoles, fetchPermissions,
        createRole, updateRole, deleteRole,
        assignRolesToUser, createPermission, deletePermission,
    };
});