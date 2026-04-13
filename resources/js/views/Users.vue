<template>
    <div>
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="page-title">Usuarios</div>
                <div class="page-sub">Gestión de usuarios y roles del sistema</div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn-outline" @click="showImportModal = true">
                    ↑ Importar Excel
                </button>
                <a href="/api/users/template" class="btn-outline" download>
                    ↓ Plantilla
                </a>
                <button class="btn-primary" @click="openCreateModal">
                    + Nuevo usuario
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="toolbar mb-3">
            <input
                v-model="search"
                @input="onSearch"
                type="text"
                placeholder="Buscar por nombre o correo..."
                class="search-input"
            />
            <select v-model="filterRole" @change="loadUsers" class="filter-select">
                <option value="">Todos los roles</option>
                <option v-for="r in store.roles" :key="r.id" :value="r.name">{{ r.name }}</option>
            </select>
            <select v-model="filterStatus" @change="loadUsers" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        <!-- Tabla -->
        <div class="users-table">
            <div v-if="store.loading" class="text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>
            <table class="table mb-0">
    <thead>
        <tr>
            <th>Usuario</th>
            <th>DNI</th>
            <th>Correo / Teléfono</th>
            <th>Cargo</th>
            <th>Área / Unidad</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <tr v-if="store.users.length === 0">
            <td colspan="8" class="text-center text-muted py-4">No hay usuarios.</td>
        </tr>
        <tr v-for="u in store.users" :key="u.id">
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="u-avatar" :style="{ background: avatarColor(u.name) }">
                        <img v-if="u.avatar" :src="u.avatar" class="avatar-img" />
                        <span v-else>{{ initials(u.name) }}</span>
                    </div>
                    <div>
                        <div class="fw-500">{{ u.name }}</div>
                        <div class="text-muted" style="font-size:11px">
                            Registrado {{ formatDate(u.created_at) }}
                        </div>
                    </div>
                </div>
            </td>
            <td class="text-muted small">{{ u.dni || '—' }}</td>
            <td>
                <div class="small">{{ u.email }}</div>
                <div class="text-muted" style="font-size:11px">{{ u.phone || '—' }}</div>
            </td>
            <td class="small">{{ u.position || '—' }}</td>
            <td>
                <span v-if="u.unit" class="unit-badge">{{ u.unit.name }}</span>
                <span v-else class="text-muted small">—</span>
            </td>
            <td>
                <span
                    v-for="role in u.roles" :key="role.id"
                    class="role-pill" :class="'role-' + role.name"
                >{{ role.name }}</span>
                <span v-if="!u.roles?.length" class="text-muted small">Sin rol</span>
            </td>
            <td>
                <span class="status-badge" :class="u.is_active ? 'active' : 'inactive'">
                    <span class="status-dot"></span>
                    {{ u.is_active ? 'Activo' : 'Inactivo' }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn-icon" @click="openEditModal(u)" title="Editar">✎</button>
                    <button
                        class="btn-icon danger"
                        @click="deactivateUser(u)"
                        :disabled="u.id === authStore.user?.id"
                        title="Desactivar"
                    >✕</button>
                </div>
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
                @click="loadUsers(page)"
            >{{ page }}</button>
        </div>

        <!-- Modal Crear/Editar -->
        <div v-if="showFormModal" class="modal-overlay" @click.self="showFormModal = false">
            <div class="modal-box">
                <div class="modal-header">
                    <h5>{{ editingUser ? 'Editar usuario' : 'Nuevo usuario' }}</h5>
                    <button @click="showFormModal = false" class="close-btn">✕</button>
                </div>
                 <div class="modal-body">
    <div class="form-row">
        <div class="form-group">
            <label>Nombre completo *</label>
            <input v-model="form.name" type="text" placeholder="Juan Pérez" />
            <span v-if="formErrors.name" class="field-error">{{ formErrors.name }}</span>
        </div>
        <div class="form-group">
            <label>DNI</label>
            <input v-model="form.dni" type="text" placeholder="12345678" maxlength="8" />
            <span v-if="formErrors.dni" class="field-error">{{ formErrors.dni }}</span>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Correo electrónico *</label>
            <input v-model="form.email" type="email" placeholder="juan@universidad.edu.pe" />
            <span v-if="formErrors.email" class="field-error">{{ formErrors.email }}</span>
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input v-model="form.phone" type="text" placeholder="999 999 999" />
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Cargo / Puesto</label>
            <input v-model="form.position" type="text" placeholder="Ej: Jefe de Área" />
        </div>
        <div class="form-group">
            <label>Área / Unidad</label>
            <select v-model="form.unit_id">
                <option value="">Sin área</option>
                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Rol</label>
            <select v-model="form.role">
                <option value="">Sin rol</option>
                <option v-for="r in store.roles" :key="r.id" :value="r.name">{{ r.name }}</option>
            </select>
        </div>
        <div class="form-group">
            <label>Estado</label>
            <select v-model="form.is_active">
                <option :value="true">Activo</option>
                <option :value="false">Inactivo</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label>{{ editingUser ? 'Nueva contraseña (vacío = no cambia)' : 'Contraseña' }}</label>
        <input v-model="form.password" type="password" placeholder="Mínimo 8 caracteres" />
    </div>
    <div v-if="formError" class="alert-error">{{ formError }}</div>
</div>
                <div class="modal-footer">
                    <button @click="showFormModal = false" class="btn-cancel">Cancelar</button>
                    <button @click="submitForm" :disabled="formLoading" class="btn-submit">
                        {{ formLoading ? 'Guardando...' : (editingUser ? 'Actualizar' : 'Crear usuario') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Importar -->
        <div v-if="showImportModal" class="modal-overlay" @click.self="showImportModal = false">
            <div class="modal-box">
                <div class="modal-header">
                    <h5>Importar usuarios desde Excel</h5>
                    <button @click="showImportModal = false" class="close-btn">✕</button>
                </div>
                <div class="modal-body">
                    <!-- Instrucciones -->
                    <div class="import-info mb-3">
                        <p class="small">El archivo debe tener las siguientes columnas:</p>
                        <table class="template-table">
                            <thead>
                                <tr>
                                    <th>name</th>
                                    <th>email</th>
                                    <th>role</th>
                                    <th>is_active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Juan Pérez</td>
                                    <td>juan@uni.edu.pe</td>
                                    <td>usuario</td>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="/api/users/template" download class="download-template">
                            ↓ Descargar plantilla CSV
                        </a>
                    </div>

                    <!-- Upload -->
                    <div
                        class="drop-zone"
                        :class="{ dragging: isDragging, 'has-file': importFile }"
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onImportDrop"
                        @click="$refs.importInput.click()"
                    >
                        <input
                            ref="importInput"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            style="display:none"
                            @change="importFile = $event.target.files[0]"
                        />
                        <div v-if="!importFile" class="drop-content">
                            <p>Arrastra el archivo o haz clic para buscar</p>
                            <span>XLSX, XLS o CSV</span>
                        </div>
                        <div v-else class="file-selected">
                            <span>📄 {{ importFile.name }}</span>
                            <button @click.stop="importFile = null" class="remove-file">✕</button>
                        </div>
                    </div>

                    <!-- Resultado importación -->
                    <div v-if="importResult" class="import-result mt-3">
                        <div class="import-success">
                            ✅ {{ importResult.imported }} usuarios importados correctamente.
                        </div>
                        <div v-if="importResult.errors?.length" class="import-errors mt-2">
                            <div class="fw-500 small mb-1">⚠️ Errores:</div>
                            <div
                                v-for="(err, i) in importResult.errors"
                                :key="i"
                                class="import-error-item"
                            >{{ err }}</div>
                        </div>
                    </div>

                    <div v-if="importError" class="alert-error mt-2">{{ importError }}</div>
                </div>
                <div class="modal-footer">
                    <button @click="showImportModal = false" class="btn-cancel">Cerrar</button>
                    <button
                        @click="submitImport"
                        :disabled="!importFile || importLoading"
                        class="btn-submit"
                    >
                        {{ importLoading ? 'Importando...' : 'Importar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useUserStore }  from '@/stores/users.js';
import { useAuthStore }  from '@/stores/auth.js';

const store     = useUserStore();
const authStore = useAuthStore();

const units = ref([]);
const search       = ref('');
const filterRole   = ref('');
const filterStatus = ref('');
let   searchTimeout = null;

// Modales
const showFormModal   = ref(false);
const showImportModal = ref(false);
const editingUser     = ref(null);

// Formulario
// Actualiza el form reactive para incluir nuevos campos
const form = reactive({
    name: '', email: '', password: '',
    dni: '', phone: '', position: '',
    unit_id: '', role: '', is_active: true,
});
const formErrors = reactive({});
const formError  = ref(null);
const formLoading = ref(false);

// Importación
const importFile    = ref(null);
const isDragging    = ref(false);
const importResult  = ref(null);
const importError   = ref(null);
const importLoading = ref(false);

onMounted(async () => {
    await Promise.all([store.fetchUsers(), store.fetchRoles()]);
    const res = await axios.get('/api/units');
    units.value = res.data;
});
 

function loadUsers(page = 1) {
    store.fetchUsers({
        page,
        search: search.value,
        role:   filterRole.value,
        status: filterStatus.value,
    });
}

function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadUsers(), 400);
}

function openCreateModal() {
    editingUser.value = null;
    Object.assign(form, { name: '', email: '', password: '', role: '', is_active: true });
    Object.keys(formErrors).forEach(k => delete formErrors[k]);
    formError.value = null;
    showFormModal.value = true;
}

function openEditModal(u) {
    editingUser.value = u;
    Object.assign(form, {
        name:      u.name,
        email:     u.email,
        password:  '',
        dni:       u.dni       || '',
        phone:     u.phone     || '',
        position:  u.position  || '',
        unit_id:   u.unit_id   || '',
        role:      u.roles?.[0]?.name || '',
        is_active: u.is_active,
    });
    Object.keys(formErrors).forEach(k => delete formErrors[k]);
    formError.value = null;
    showFormModal.value = true;
}

async function submitForm() {
    formLoading.value = true;
    formError.value   = null;
    Object.keys(formErrors).forEach(k => delete formErrors[k]);

    try {
        if (editingUser.value) {
            await store.updateUser(editingUser.value.id, form);
        } else {
            await store.createUser(form);
        }
        showFormModal.value = false;
    } catch (e) {
        if (e.response?.data?.errors) {
            Object.assign(formErrors, e.response.data.errors);
        } else {
            formError.value = e.response?.data?.message || 'Error al guardar.';
        }
    } finally {
        formLoading.value = false;
    }
}

async function deactivateUser(u) {
    if (confirm(`¿Desactivar a "${u.name}"?`)) {
        await store.deleteUser(u.id);
    }
}

function onImportDrop(e) {
    isDragging.value = false;
    importFile.value = e.dataTransfer.files[0];
}

async function submitImport() {
    importLoading.value = true;
    importError.value   = null;
    importResult.value  = null;

    try {
        const fd = new FormData();
        fd.append('file', importFile.value);
        importResult.value = await store.importUsers(fd);
    } catch (e) {
        importError.value = e.response?.data?.message || 'Error al importar.';
    } finally {
        importLoading.value = false;
    }
}

function initials(name) {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U';
}

function avatarColor(name) {
    const colors = ['#185FA5','#378ADD','#0F6E56','#639922','#BA7517','#993556'];
    const idx    = (name?.charCodeAt(0) || 0) % colors.length;
    return colors[idx];
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-PE', {
        day: '2-digit', month: 'short', year: 'numeric'
    });
}
</script>

<style scoped>
.page-title  { font-size: 18px; font-weight: 500; margin-bottom: 4px; }
.page-sub    { font-size: 13px; color: #888; margin-bottom: 20px; }
.btn-primary { background: #185FA5; color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 13px; cursor: pointer; }
.btn-outline { background: none; border: 0.5px solid #ddd; border-radius: 6px; padding: 7px 14px; font-size: 13px; cursor: pointer; text-decoration: none; color: #555; }
.toolbar     { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.search-input { height: 34px; border: 0.5px solid #ddd; border-radius: 6px; padding: 0 12px; font-size: 13px; width: 260px; outline: none; }
.filter-select { height: 34px; border: 0.5px solid #ddd; border-radius: 6px; padding: 0 8px; font-size: 13px; outline: none; background: #fff; }
.users-table { background: #fff; border: 0.5px solid #e0e0e0; border-radius: 10px; overflow: hidden; }
.table       { width: 100%; border-collapse: collapse; font-size: 13px; }
.table th    { font-size: 11px; font-weight: 500; color: #888; padding: 10px 16px; text-align: left; border-bottom: 0.5px solid #e0e0e0; background: #f9f9f9; }
.table td    { padding: 12px 16px; border-bottom: 0.5px solid #f0f0f0; color: #222; }
.table tr:last-child td { border-bottom: none; }
.unit-badge {font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 500; background: #E6F1FB; color: #185FA5; display: inline-block;}
.avatar-img {width: 100%; height: 100%; border-radius: 50%; object-fit: cover;}
.u-avatar    { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600; flex-shrink: 0; }
.role-pill   { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 500; display: inline-block; text-transform: capitalize; }
.role-super_admin   { background: #FCEBEB; color: #A32D2D; }
.role-administrador { background: #E6F1FB; color: #0C447C; }
.role-jefe_area     { background: #FAEEDA; color: #633806; }
.role-secretaria    { background: #E1F5EE; color: #085041; }
.role-auditor       { background: #F1EFE8; color: #444441; }
.role-usuario       { background: #F0F0F0; color: #555; }
.status-badge  { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; }
.status-dot    { width: 6px; height: 6px; border-radius: 50%; }
.status-badge.active   .status-dot { background: #639922; }
.status-badge.inactive .status-dot { background: #888; }
.status-badge.active   { color: #3B6D11; }
.status-badge.inactive { color: #888; }
.btn-icon  { background: none; border: none; cursor: pointer; padding: 4px 6px; color: #888; border-radius: 4px; font-size: 14px; }
.btn-icon:hover { background: #f0f0f0; }
.btn-icon.danger:hover { background: #FCEBEB; color: #A32D2D; }
.btn-icon:disabled { opacity: 0.3; cursor: not-allowed; }
.pagination-bar { display: flex; gap: 4px; }
.page-btn { width: 32px; height: 32px; border: 0.5px solid #ddd; border-radius: 6px; background: #fff; cursor: pointer; font-size: 13px; }
.page-btn.active { background: #185FA5; color: white; border-color: #185FA5; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-box { background: #fff; border-radius: 12px; width: 480px; max-width: 95vw; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 0.5px solid #e0e0e0; }
.modal-header h5 { margin: 0; font-size: 15px; font-weight: 600; }
.close-btn  { background: none; border: none; cursor: pointer; font-size: 16px; color: #888; }
.modal-body { padding: 20px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 0.5px solid #e0e0e0; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 12px; font-weight: 500; color: #555; margin-bottom: 5px; }
.form-group input, .form-group select { width: 100%; height: 36px; border: 0.5px solid #ddd; border-radius: 6px; padding: 0 10px; font-size: 13px; outline: none; }
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field-error { font-size: 11px; color: #A32D2D; }
.alert-error { background: #FCEBEB; color: #A32D2D; border-radius: 6px; padding: 10px 14px; font-size: 13px; }
.btn-cancel  { background: none; border: 0.5px solid #ddd; border-radius: 6px; padding: 8px 16px; font-size: 13px; cursor: pointer; }
.btn-submit  { background: #185FA5; color: white; border: none; border-radius: 6px; padding: 8px 20px; font-size: 13px; cursor: pointer; }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.drop-zone { border: 2px dashed #ddd; border-radius: 10px; padding: 24px; text-align: center; cursor: pointer; transition: border-color .2s; }
.drop-zone:hover, .drop-zone.dragging { border-color: #378ADD; background: #f0f7ff; }
.drop-zone.has-file { border-style: solid; border-color: #378ADD; }
.drop-content p    { font-size: 13px; color: #555; margin: 0 0 4px; }
.drop-content span { font-size: 11px; color: #aaa; }
.file-selected { display: flex; align-items: center; justify-content: space-between; font-size: 13px; }
.remove-file { background: none; border: none; cursor: pointer; color: #888; }
.import-info { background: #f9f9f9; border-radius: 8px; padding: 12px; }
.template-table { width: 100%; border-collapse: collapse; font-size: 11px; margin: 8px 0; }
.template-table th, .template-table td { border: 0.5px solid #ddd; padding: 4px 8px; }
.template-table th { background: #f0f0f0; }
.download-template { font-size: 12px; color: #185FA5; text-decoration: none; }
.import-success { background: #EAF3DE; color: #27500A; border-radius: 6px; padding: 10px 14px; font-size: 13px; }
.import-errors  { background: #FAEEDA; border-radius: 6px; padding: 10px 14px; }
.import-error-item { font-size: 12px; color: #633806; padding: 2px 0; }
.fw-500 { font-weight: 500; }
.d-flex { display: flex; }
.gap-1  { gap: 4px; }
.gap-2  { gap: 8px; }
.align-items-center { align-items: center; }
.justify-content-between { justify-content: space-between; }
.mb-3   { margin-bottom: 12px; }
.mt-2   { margin-top: 8px; }
.mt-3   { margin-top: 12px; }
.text-center { text-align: center; }
.text-muted  { color: #888; }
.small  { font-size: 12px; }
.py-4   { padding: 16px 0; }
.py-5   { padding: 20px 0; }
</style>