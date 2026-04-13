<template>
    <div>
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="page-title">Documentos</div>
                <div class="page-sub">Gestión documental del sistema</div>
            </div>
            <button class="btn-primary" @click="showCreateModal = true">
                <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Subir documento
            </button>
        </div>

        <!-- Filtros y toggle vista -->
        <div class="docs-toolbar mb-3">
            <input
                v-model="search"
                @input="onSearch"
                type="text"
                placeholder="Buscar documentos..."
                class="search-input"
            />
            <select v-model="filterStatus" @change="loadDocuments" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="activo">Activo</option>
                <option value="en_revision">En revisión</option>
                <option value="aprobado">Aprobado</option>
                <option value="obsoleto">Obsoleto</option>
            </select>
            <select v-model="filterType" @change="loadDocuments" class="filter-select">
                <option value="">Todos los tipos</option>
                <option v-for="t in store.types" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
            <div class="view-toggle ms-auto">
                <button class="vt-btn" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'">
                    <svg viewBox="0 0 24 24"><path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z"/></svg>
                </button>
                <button class="vt-btn" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
                    <svg viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="store.loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <!-- Sin resultados -->
        <div v-else-if="store.documents.length === 0" class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5z"/></svg>
            <p>No hay documentos. ¡Sube el primero!</p>
            <button class="btn-primary" @click="showCreateModal = true">Subir documento</button>
        </div>

        <!-- Vista tarjetas -->
        <div v-else-if="viewMode === 'grid'" class="docs-grid">
            <div
                v-for="doc in store.documents"
                :key="doc.id"
                class="doc-card"
                @click="goToDetail(doc.id)"
            >
                <div class="dc-header">
                    <div class="dc-icon" :class="getIconClass(doc)">
                        {{ getFileExt(doc) }}
                    </div>  
                    <div class="dc-name">{{ doc.name }}</div>
                    <button
                        class="fav-btn"
                        :class="{ active: doc.is_favorited }"
                        @click.stop="toggleFav(doc)"
                    >★</button>
                </div>
                <div class="dc-meta">
                    {{ doc.unit?.name || 'Sin unidad' }} · v{{ doc.current_version }}
                </div>
                <div class="dc-footer">
                    <span class="doc-badge" :class="'badge-' + doc.status">
                        {{ statusLabel(doc.status) }}
                    </span>
                    <span class="doc-date">{{ formatDate(doc.created_at) }}</span>
                </div>
            </div>
        </div>

        <!-- Vista lista -->
        <div v-else class="docs-list card">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Unidad</th>
                        <th>Versión</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="doc in store.documents"
                        :key="doc.id"
                        @click="goToDetail(doc.id)"
                        style="cursor:pointer"
                    >
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="dc-icon sm" :class="getIconClass(doc)">
                                    {{ getFileExt(doc) }}
                                </div>
                                <div>
                                    <div class="fw-500">{{ doc.name }}</div>
                                    <div class="text-muted small">{{ doc.code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small">{{ doc.type?.name || '—' }}</td>
                        <td class="text-muted small">{{ doc.unit?.name || '—' }}</td>
                        <td class="text-muted small">v{{ doc.current_version }}</td>
                        <td>
                            <span class="doc-badge" :class="'badge-' + doc.status">
                                {{ statusLabel(doc.status) }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ formatDate(doc.created_at) }}</td>
                        <td>
                            <button class="btn-icon" @click.stop="toggleFav(doc)" :class="{ active: doc.is_favorited }">★</button>
                            <button class="btn-icon" @click.stop="downloadDoc(doc)">↓</button>
                            <button class="btn-icon danger" @click.stop="deleteDoc(doc)">✕</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div v-if="store.pagination.last_page > 1" class="pagination-bar mt-3">
            <button
                v-for="page in store.pagination.last_page"
                :key="page"
                class="page-btn"
                :class="{ active: page === store.pagination.current_page }"
                @click="loadDocuments(page)"
            >{{ page }}</button>
        </div>

        <!-- Modal Crear -->
        <CreateDocumentModal
            v-if="showCreateModal"
            @close="showCreateModal = false"
            @created="onDocumentCreated"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useDocumentStore } from '@/stores/documents.js';
import CreateDocumentModal from './Create.vue';

const store  = useDocumentStore();
const router = useRouter();

const viewMode      = ref('grid');
const showCreateModal = ref(false);
const search        = ref('');
const filterStatus  = ref('');
const filterType    = ref('');
let searchTimeout   = null;

onMounted(async () => {
    await Promise.all([
        store.fetchDocuments(),
        store.fetchTypes(),
        store.fetchUnits(),
    ]);
});

function loadDocuments(page = 1) {
    store.fetchDocuments({
        page,
        search:    search.value,
        status:    filterStatus.value,
        type_id:   filterType.value,
    });
}

function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadDocuments(), 400);
}

function goToDetail(id) {
    router.push(`/documentos/${id}`);
}

async function toggleFav(doc) {
    await store.toggleFavorite(doc.id);
}

async function downloadDoc(doc) {
    window.open(`/api/documents/${doc.id}/download`, '_blank');
}

async function deleteDoc(doc) {
    if (confirm(`¿Mover "${doc.name}" a la papelera?`)) {
        await store.deleteDocument(doc.id);
    }
}

function onDocumentCreated() {
    showCreateModal.value = false;
    loadDocuments();
}

function getFileExt(doc) {
    const ext = doc.current_version_file?.file_type || 'doc';
    return ext.toUpperCase();
}

function getIconClass(doc) {
    const ext = doc.currentVersion?.file_type || '';
    if (ext === 'pdf') return 'pdf';
    if (['xls','xlsx'].includes(ext)) return 'xls';
    return 'doc';
}

function statusLabel(status) {
    const labels = {
        borrador: 'Borrador', activo: 'Activo',
        en_revision: 'En revisión', aprobado: 'Aprobado',
        obsoleto: 'Obsoleto', anulado: 'Anulado',
    };
    return labels[status] || status;
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-PE', {
        day: '2-digit', month: 'short', year: 'numeric'
    });
}
</script>

<style scoped>
.page-title { font-size: 18px; font-weight: 500; margin-bottom: 4px; }
.page-sub   { font-size: 13px; color: #888; margin-bottom: 20px; }
.btn-primary {
    background: #185FA5; color: white; border: none;
    border-radius: 6px; padding: 7px 14px; font-size: 13px;
    cursor: pointer; display: flex; align-items: center; gap: 6px;
}
.btn-primary svg { width: 13px; height: 13px; fill: white; }
.docs-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.search-input {
    height: 34px; border: 0.5px solid #ddd; border-radius: 6px;
    padding: 0 12px; font-size: 13px; width: 220px; outline: none;
}
.filter-select {
    height: 34px; border: 0.5px solid #ddd; border-radius: 6px;
    padding: 0 8px; font-size: 13px; outline: none; background: #fff;
}
.view-toggle { display: flex; border: 0.5px solid #ddd; border-radius: 6px; overflow: hidden; }
.vt-btn {
    width: 32px; height: 32px; display: flex; align-items: center;
    justify-content: center; cursor: pointer; background: none;
    border: none; color: #888;
}
.vt-btn.active { background: #E6F1FB; color: #185FA5; }
.vt-btn svg    { width: 14px; height: 14px; fill: currentColor; }
.docs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
}
.doc-card {
    background: #fff; border: 0.5px solid #e0e0e0;
    border-radius: 10px; padding: 14px; cursor: pointer;
    transition: border-color .15s;
}
.doc-card:hover { border-color: #378ADD; }
.dc-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.dc-icon {
    width: 32px; height: 32px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0;
}
.dc-icon.sm { width: 26px; height: 26px; font-size: 9px; }
.dc-icon.pdf { background: #FCEBEB; color: #A32D2D; }
.dc-icon.doc { background: #E6F1FB; color: #185FA5; }
.dc-icon.xls { background: #EAF3DE; color: #3B6D11; }
.dc-name { font-size: 13px; font-weight: 500; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dc-meta { font-size: 11px; color: #888; margin-bottom: 8px; }
.dc-footer { display: flex; align-items: center; justify-content: space-between; }
.doc-badge { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 500; }
.badge-borrador    { background: #F1EFE8; color: #444441; }
.badge-activo      { background: #EAF3DE; color: #27500A; }
.badge-en_revision { background: #FAEEDA; color: #633806; }
.badge-aprobado    { background: #E6F1FB; color: #185FA5; }
.badge-obsoleto    { background: #F0F0F0; color: #888; }
.badge-anulado     { background: #FCEBEB; color: #A32D2D; }
.doc-date { font-size: 11px; color: #888; }
.fav-btn { background: none; border: none; cursor: pointer; color: #ccc; font-size: 16px; padding: 0; }
.fav-btn.active { color: #f59e0b; }
.btn-icon { background: none; border: none; cursor: pointer; padding: 2px 6px; color: #888; border-radius: 4px; }
.btn-icon:hover { background: #f0f0f0; }
.btn-icon.danger:hover { background: #FCEBEB; color: #A32D2D; }
.empty-state {
    text-align: center; padding: 60px 20px; color: #888;
}
.empty-state svg { width: 48px; height: 48px; fill: #ccc; margin-bottom: 12px; }
.empty-state p { margin-bottom: 16px; }
.pagination-bar { display: flex; gap: 4px; }
.page-btn {
    width: 32px; height: 32px; border: 0.5px solid #ddd;
    border-radius: 6px; background: #fff; cursor: pointer; font-size: 13px;
}
.page-btn.active { background: #185FA5; color: white; border-color: #185FA5; }
.fw-500 { font-weight: 500; }
</style>