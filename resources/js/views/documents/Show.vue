<template>
    <div>
        <!-- Loading -->
        <div v-if="store.loading" class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>

        <div v-else-if="store.document">
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <button @click="router.back()" class="btn-back">← Volver</button>
                <div>
                    <div class="page-title">{{ store.document.name }}</div>
                    <div class="page-sub">
                        {{ store.document.code }} ·
                        v{{ store.document.current_version }} ·
                        {{ store.document.unit?.name || 'Sin unidad' }}
                    </div>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <span class="doc-badge" :class="'badge-' + store.document.status">
                        {{ statusLabel(store.document.status) }}
                    </span>
                    <a :href="`/api/documents/${store.document.id}/download`" class="btn-primary" target="_blank">
                        ↓ Descargar
                    </a>
                    <button class="btn-outline" @click="showVersionModal = true">
                        + Nueva versión
                    </button>
                </div>
            </div>

            <div class="detail-grid">
                <!-- Info + Versiones -->
                <div>
                    <!-- Información -->
                    <div class="card mb-3">
                        <div class="card-title">Información</div>
                        <div class="info-row">
                            <span class="info-label">Nombre</span>
                            <span>{{ store.document.name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Código</span>
                            <span>{{ store.document.code }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tipo</span>
                            <span>{{ store.document.type?.name || '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Unidad</span>
                            <span>{{ store.document.unit?.name || '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Propietario</span>
                            <span>{{ store.document.owner?.name || '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Versión actual</span>
                            <span>{{ store.document.current_version }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Estado</span>
                            <span class="doc-badge" :class="'badge-' + store.document.status">
                                {{ statusLabel(store.document.status) }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Creado</span>
                            <span>{{ formatDate(store.document.created_at) }}</span>
                        </div>
                    </div>

                    <!-- Árbol de versiones -->
                    <div class="card">
                        <div class="card-title">Versiones</div>
                        <div class="versions-tree">
                            <div
                                v-for="v in store.document.versions"
                                :key="v.id"
                                class="version-item"
                                :class="{ current: v.is_current }"
                            >
                                <div class="version-dot"></div>
                                <div class="version-info">
                                    <div class="version-num">
                                        v{{ v.version_number }}
                                        <span v-if="v.is_current" class="current-badge">Actual</span>
                                    </div>
                                    <div class="version-desc">{{ v.change_description }}</div>
                                    <div class="version-meta">
                                        {{ v.created_by?.name }} · {{ formatDate(v.created_at) }}
                                    </div>
                                </div>
                                <a :href="`/api/documents/${store.document.id}/download`" class="version-dl">↓</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad + Permisos -->
                <div>
                    <!-- Permisos -->
                    <div class="card mb-3">
                        <div class="card-title">Permisos de acceso</div>
                        <div v-if="store.document.permissions?.length === 0" class="text-muted small">
                            Sin permisos específicos asignados.
                        </div>
                        <div
                            v-for="p in store.document.permissions"
                            :key="p.id"
                            class="permission-row"
                        >
                            <span class="perm-icon">🏢</span>
                            <span class="perm-name">{{ p.unit?.name || p.user?.name }}</span>
                            <span class="perm-type">{{ p.permission_type }}</span>
                        </div>
                    </div>

                    <!-- Actividad -->
                    <div class="card">
                        <div class="card-title">Actividad reciente</div>
                        <div class="activity-list">
                            <div
                                v-for="log in store.document.audit_logs"
                                :key="log.id"
                                class="activity-item"
                            >
                                <div class="act-dot"></div>
                                <div>
                                    <div class="act-text">
                                        <strong>{{ log.performer?.name }}</strong>
                                        {{ actionLabel(log.action) }}
                                    </div>
                                    <div class="act-time">{{ formatDate(log.created_at) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal nueva versión -->
        <div v-if="showVersionModal" class="modal-overlay" @click.self="showVersionModal = false">
            <div class="modal-box-sm">
                <div class="modal-header">
                    <h5>Nueva versión</h5>
                    <button @click="showVersionModal = false" class="close-btn">✕</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Archivo *</label>
                        <input type="file" @change="versionFile = $event.target.files[0]"
                               accept=".pdf,.doc,.docx,.xls,.xlsx" />
                    </div>
                    <div class="form-group">
                        <label>Descripción del cambio *</label>
                        <textarea v-model="versionDesc" rows="3" placeholder="¿Qué cambió en esta versión?"></textarea>
                    </div>
                    <div v-if="versionError" class="alert-error">{{ versionError }}</div>
                </div>
                <div class="modal-footer">
                    <button @click="showVersionModal = false" class="btn-cancel">Cancelar</button>
                    <button @click="submitVersion" :disabled="versionLoading" class="btn-submit">
                        {{ versionLoading ? 'Subiendo...' : 'Guardar versión' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useDocumentStore } from '@/stores/documents.js';

const route  = useRoute();
const router = useRouter();
const store  = useDocumentStore();

const showVersionModal = ref(false);
const versionFile      = ref(null);
const versionDesc      = ref('');
const versionError     = ref(null);
const versionLoading   = ref(false);

onMounted(async () => {
    await store.fetchDocument(route.params.id);
});

async function submitVersion() {
    if (!versionFile.value) { versionError.value = 'Selecciona un archivo.'; return; }
    if (!versionDesc.value) { versionError.value = 'La descripción es requerida.'; return; }

    versionLoading.value = true;
    versionError.value   = null;

    try {
        const fd = new FormData();
        fd.append('file', versionFile.value);
        fd.append('description', versionDesc.value);
        await store.addVersion(route.params.id, fd);
        await store.fetchDocument(route.params.id);
        showVersionModal.value = false;
        versionDesc.value = '';
        versionFile.value = null;
    } catch (e) {
        versionError.value = e.response?.data?.message || 'Error al subir versión.';
    } finally {
        versionLoading.value = false;
    }
}

function statusLabel(status) {
    const labels = {
        borrador: 'Borrador', activo: 'Activo',
        en_revision: 'En revisión', aprobado: 'Aprobado',
        obsoleto: 'Obsoleto', anulado: 'Anulado',
    };
    return labels[status] || status;
}

function actionLabel(action) {
    const labels = {
        creado: 'creó el documento', versionado: 'subió una nueva versión',
        visualizado: 'visualizó el documento', descargado: 'descargó el documento',
        actualizado: 'actualizó el documento', eliminado: 'movió a papelera',
        restaurado: 'restauró el documento', aprobado: 'aprobó el documento',
    };
    return labels[action] || action;
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-PE', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}
</script>

<style scoped>
.page-title  { font-size: 18px; font-weight: 500; }
.page-sub    { font-size: 13px; color: #888; }
.btn-back    { background: none; border: 0.5px solid #ddd; border-radius: 6px; padding: 6px 12px; font-size: 13px; cursor: pointer; }
.btn-primary { background: #185FA5; color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 13px; cursor: pointer; text-decoration: none; }
.btn-outline { background: none; border: 0.5px solid #ddd; border-radius: 6px; padding: 7px 14px; font-size: 13px; cursor: pointer; }
.detail-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; }
.card        { background: #fff; border: 0.5px solid #e0e0e0; border-radius: 10px; padding: 16px; }
.card-title  { font-size: 13px; font-weight: 600; margin-bottom: 14px; }
.info-row    { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 0.5px solid #f0f0f0; font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-label  { color: #888; font-size: 12px; }
.doc-badge   { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 500; }
.badge-borrador    { background: #F1EFE8; color: #444441; }
.badge-activo      { background: #EAF3DE; color: #27500A; }
.badge-en_revision { background: #FAEEDA; color: #633806; }
.badge-aprobado    { background: #E6F1FB; color: #185FA5; }
.badge-obsoleto    { background: #F0F0F0; color: #888; }
.badge-anulado     { background: #FCEBEB; color: #A32D2D; }
.versions-tree { display: flex; flex-direction: column; gap: 12px; }
.version-item  { display: flex; align-items: flex-start; gap: 10px; position: relative; }
.version-item::before { content: ''; position: absolute; left: 5px; top: 16px; bottom: -12px; width: 1px; background: #e0e0e0; }
.version-item:last-child::before { display: none; }
.version-dot   { width: 12px; height: 12px; border-radius: 50%; background: #ddd; flex-shrink: 0; margin-top: 3px; }
.version-item.current .version-dot { background: #185FA5; }
.version-info  { flex: 1; }
.version-num   { font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px; }
.current-badge { background: #E6F1FB; color: #185FA5; font-size: 10px; padding: 1px 6px; border-radius: 10px; }
.version-desc  { font-size: 12px; color: #555; margin: 2px 0; }
.version-meta  { font-size: 11px; color: #888; }
.version-dl    { color: #185FA5; text-decoration: none; font-size: 16px; }
.permission-row { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 0.5px solid #f0f0f0; font-size: 13px; }
.permission-row:last-child { border-bottom: none; }
.perm-icon  { font-size: 16px; }
.perm-name  { flex: 1; }
.perm-type  { font-size: 11px; background: #f0f0f0; padding: 2px 8px; border-radius: 10px; color: #555; }
.activity-list { display: flex; flex-direction: column; gap: 12px; }
.activity-item { display: flex; gap: 10px; }
.act-dot   { width: 8px; height: 8px; border-radius: 50%; background: #378ADD; flex-shrink: 0; margin-top: 4px; }
.act-text  { font-size: 12px; color: #555; line-height: 1.5; }
.act-time  { font-size: 11px; color: #999; margin-top: 2px; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-box-sm  { background: #fff; border-radius: 12px; width: 440px; max-width: 95vw; }
.modal-header  { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 0.5px solid #e0e0e0; }
.modal-header h5 { margin: 0; font-size: 15px; font-weight: 600; }
.close-btn   { background: none; border: none; cursor: pointer; font-size: 16px; color: #888; }
.modal-body  { padding: 20px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 0.5px solid #e0e0e0; }
.form-group  { margin-bottom: 14px; }
.form-group label { display: block; font-size: 12px; font-weight: 500; color: #555; margin-bottom: 5px; }
.form-group input, .form-group textarea { width: 100%; border: 0.5px solid #ddd; border-radius: 6px; padding: 8px 10px; font-size: 13px; outline: none; }
.alert-error { background: #FCEBEB; color: #A32D2D; border-radius: 6px; padding: 10px 14px; font-size: 13px; }
.btn-cancel  { background: none; border: 0.5px solid #ddd; border-radius: 6px; padding: 8px 16px; font-size: 13px; cursor: pointer; }
.btn-submit  { background: #185FA5; color: white; border: none; border-radius: 6px; padding: 8px 20px; font-size: 13px; cursor: pointer; }
.btn-submit:disabled { opacity: 0.6; }
.mb-3 { margin-bottom: 12px; }
.ms-auto { margin-left: auto; }
.d-flex { display: flex; }
.gap-2  { gap: 8px; }
.gap-3  { gap: 12px; }
.align-items-center { align-items: center; }
.text-muted { color: #888; }
.small { font-size: 12px; }
</style>