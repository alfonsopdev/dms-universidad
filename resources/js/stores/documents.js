import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useDocumentStore = defineStore('documents', () => {
    const documents   = ref([]);
    const document    = ref(null);
    const pagination  = ref({});
    const stats       = ref({});
    const loading     = ref(false);
    const error       = ref(null);
    const units       = ref([]);
    const types       = ref([]);

    async function fetchDocuments(params = {}) {
        loading.value = true;
        try {
            const res = await axios.get('/api/documents', { params });
            documents.value  = res.data.data;
            pagination.value = res.data;
        } catch (e) {
            error.value = e.message;
        } finally {
            loading.value = false;
        }
    }

    async function fetchStats() {
        const res = await axios.get('/api/documents/stats');
        stats.value = res.data;
    }

    async function fetchDocument(id) {
        loading.value = true;
        try {
            const res = await axios.get(`/api/documents/${id}`);
            document.value = res.data;
        } finally {
            loading.value = false;
        }
    }

    async function createDocument(formData) {
        const res = await axios.post('/api/documents', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        documents.value.unshift(res.data);
        return res.data;
    }

    async function updateDocument(id, data) {
        const res = await axios.put(`/api/documents/${id}`, data);
        const idx = documents.value.findIndex(d => d.id === id);
        if (idx !== -1) documents.value[idx] = res.data;
        return res.data;
    }

    async function deleteDocument(id) {
        await axios.delete(`/api/documents/${id}`);
        documents.value = documents.value.filter(d => d.id !== id);
    }

    async function toggleFavorite(id) {
        const res = await axios.post(`/api/documents/${id}/toggle-favorite`);
        const doc = documents.value.find(d => d.id === id);
        if (doc) doc.is_favorited = res.data.is_favorited;
        return res.data;
    }

    async function addVersion(documentId, formData) {
        const res = await axios.post(`/api/documents/${documentId}/versions`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        return res.data;
    }

    async function fetchUnits() {
        const res = await axios.get('/api/units');
        units.value = res.data;
    }

    async function fetchTypes() {
        const res = await axios.get('/api/document-types');
        types.value = res.data;
    }

    return {
        documents, document, pagination, stats,
        loading, error, units, types,
        fetchDocuments, fetchStats, fetchDocument,
        createDocument, updateDocument, deleteDocument,
        toggleFavorite, addVersion, fetchUnits, fetchTypes,
    };
});