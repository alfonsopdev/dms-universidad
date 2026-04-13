<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-box">
            <div class="modal-header">
                <h5>Subir documento</h5>
                <button @click="$emit('close')" class="close-btn">✕</button>
            </div>

            <div class="modal-body">
                <!-- Drag & Drop -->
                <div
                    class="drop-zone"
                    :class="{ dragging: isDragging, 'has-file': file }"
                    @dragover.prevent="isDragging = true"
                    @dragleave="isDragging = false"
                    @drop.prevent="onDrop"
                    @click="$refs.fileInput.click()"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".pdf,.doc,.docx,.xls,.xlsx"
                        style="display:none"
                        @change="onFileSelect"
                    />
                    <div v-if="!file" class="drop-content">
                        <svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                        <p>Arrastra y suelta o haz clic para buscar</p>
                        <span>PDF, Docx, Xlsx (máx. 100MB)</span>
                    </div>
                    <div v-else class="file-selected">
                        <div class="file-icon" :class="fileExt">{{ fileExt.toUpperCase() }}</div>
                        <div>
                            <div class="file-name">{{ file.name }}</div>
                            <div class="file-size">{{ formatSize(file.size) }}</div>
                        </div>
                        <button @click.stop="file = null" class="remove-file">✕</button>
                    </div>
                </div>

                <!-- Formulario -->
                <div class="form-group">
                    <label>Nombre del documento *</label>
                    <input v-model="form.name" type="text" placeholder="Ej: Reglamento de estudios 2025" />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de documento</label>
                        <select v-model="form.document_type_id">
                            <option value="">Seleccionar tipo</option>
                            <option v-for="t in store.types" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select v-model="form.status">
                            <option value="borrador">Borrador</option>
                            <option value="activo">Activo</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Compartir con unidad</label>
                    <select v-model="form.unit_id">
                        <option value="">Sin unidad</option>
                        <option v-for="u in store.units" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>

                <!-- Error -->
                <div v-if="error" class="alert-error">{{ error }}</div>
            </div>

            <div class="modal-footer">
                <button @click="$emit('close')" class="btn-cancel">Cancelar</button>
                <button @click="submit" :disabled="loading" class="btn-submit">
                    <span v-if="loading">Subiendo...</span>
                    <span v-else>Crear documento</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useDocumentStore } from '@/stores/documents.js';

const emit  = defineEmits(['close', 'created']);
const store = useDocumentStore();

const file      = ref(null);
const isDragging = ref(false);
const loading   = ref(false);
const error     = ref(null);

const form = reactive({
    name: '',
    document_type_id: '',
    unit_id: '',
    status: 'borrador',
});

const fileExt = ref('doc');

function onDrop(e) {
    isDragging.value = false;
    const f = e.dataTransfer.files[0];
    if (f) setFile(f);
}

function onFileSelect(e) {
    const f = e.target.files[0];
    if (f) setFile(f);
}

function setFile(f) {
    file.value = f;
    fileExt.value = f.name.split('.').pop().toLowerCase();
    if (!form.name) form.name = f.name.replace(/\.[^/.]+$/, '');
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

async function submit() {
    if (!file.value) { error.value = 'Selecciona un archivo.'; return; }
    if (!form.name)  { error.value = 'El nombre es requerido.'; return; }

    loading.value = true;
    error.value   = null;

    try {
        const formData = new FormData();
        formData.append('file', file.value);
        formData.append('name', form.name);
        if (form.document_type_id) formData.append('document_type_id', form.document_type_id);
        if (form.unit_id)          formData.append('unit_id', form.unit_id);
        formData.append('status', form.status);

        await store.createDocument(formData);
        emit('created');
    } catch (e) {
        error.value = e.response?.data?.message || 'Error al subir el documento.';
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000;
}
.modal-box {
    background: #fff; border-radius: 12px;
    width: 520px; max-width: 95vw;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px; border-bottom: 0.5px solid #e0e0e0;
}
.modal-header h5 { margin: 0; font-size: 15px; font-weight: 600; }
.close-btn { background: none; border: none; cursor: pointer; font-size: 16px; color: #888; }
.modal-body  { padding: 20px; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 20px; border-top: 0.5px solid #e0e0e0;
}
.drop-zone {
    border: 2px dashed #ddd; border-radius: 10px;
    padding: 30px; text-align: center; cursor: pointer;
    transition: border-color .2s, background .2s;
    margin-bottom: 16px;
}
.drop-zone:hover, .drop-zone.dragging { border-color: #378ADD; background: #f0f7ff; }
.drop-zone.has-file { border-style: solid; border-color: #378ADD; }
.drop-content svg { width: 40px; height: 40px; fill: #ccc; margin-bottom: 8px; }
.drop-content p   { font-size: 13px; color: #555; margin: 0 0 4px; }
.drop-content span { font-size: 11px; color: #aaa; }
.file-selected {
    display: flex; align-items: center; gap: 12px;
}
.file-icon {
    width: 40px; height: 40px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
    background: #E6F1FB; color: #185FA5;
}
.file-icon.pdf { background: #FCEBEB; color: #A32D2D; }
.file-icon.xls, .file-icon.xlsx { background: #EAF3DE; color: #3B6D11; }
.file-name { font-size: 13px; font-weight: 500; }
.file-size { font-size: 11px; color: #888; }
.remove-file { margin-left: auto; background: none; border: none; cursor: pointer; color: #888; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 12px; font-weight: 500; color: #555; margin-bottom: 5px; }
.form-group input, .form-group select {
    width: 100%; height: 36px;
    border: 0.5px solid #ddd; border-radius: 6px;
    padding: 0 10px; font-size: 13px; outline: none;
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.alert-error {
    background: #FCEBEB; color: #A32D2D;
    border-radius: 6px; padding: 10px 14px; font-size: 13px;
    margin-top: 10px;
}
.btn-cancel {
    background: none; border: 0.5px solid #ddd;
    border-radius: 6px; padding: 8px 16px; font-size: 13px; cursor: pointer;
}
.btn-submit {
    background: #185FA5; color: white; border: none;
    border-radius: 6px; padding: 8px 20px; font-size: 13px; cursor: pointer;
}
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
</style>