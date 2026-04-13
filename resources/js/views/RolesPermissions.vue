<template>
    <div>
        <div class="page-title">Roles y Permisos</div>
        <div class="page-sub">Gestión de roles del sistema y sus permisos asociados</div>

        <!-- Tabs -->
        <div class="tabs mb-4">
            <button class="tab" :class="{ active: tab === 'roles' }" @click="tab = 'roles'">
                Roles
            </button>
            <button class="tab" :class="{ active: tab === 'permissions' }" @click="tab = 'permissions'">
                Permisos
            </button>
        </div>

        <!-- ===== TAB ROLES ===== -->
        <div v-if="tab === 'roles'">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">{{ store.roles.length }} roles registrados</span>
                <button class="btn-primary" @click="openCreateRole">+ Nuevo rol</button>
            </div>

            <div v-if="store.loading" class="text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>

            <div v-else class="roles-grid">
                <div v-for="role in store.roles" :key="role.id" class="role-card">
                    <!-- Header -->
                    <div class="role-card-header">
                        <div class="role-icon" :style="{ background: roleColor(role.name) }">
                            {{ role.name[0].toUpperCase() }}
                        </div>
                        <div class="role-info">
                            <div class="role-name">{{ role.name }}</div>
                            <div class="role-meta">
                                {{ role.users_count }} usuario{{ role.users_count !== 1 ? 's' : '' }} ·
                                {{ role.permissions?.length || 0 }} permiso{{ role.permissions?.length !== 1 ? 's' : '' }}
                            </div>
                        </div>
                        <div class="role-actions">
                            <button class="btn-icon" @click="openEditRole(role)" title="Editar">✎</button>
                            <button
                                class="btn-icon danger"
                                @click="removeRole(role)"
                                :disabled="systemRoles.includes(role.name)"
                                title="Eliminar"
                            >✕</button>
                        </div>
                    </div>

                    <!-- Permisos del rol -->
                    <div class="role-permissions">
                        <div
                            v-for="perm in role.permissions?.slice(0, 6)"
                            :key="perm.id"
                            class="perm-chip"
                        >{{ perm.name }}</div>
                        <div v-if="role.permissions?.length > 6" class="perm-chip more">
                            +{{ role.permissions.length - 6 }} más
                        </div>
                        <div v-if="!role.permissions?.length" class="text-muted small">
                            Sin permisos asignados
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== TAB PERMISOS ===== -->
        <div v-if="tab === 'permissions'">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">{{ store.permissions.length }} permisos registrados</span>
                <button class="btn-primary" @click="showNewPermModal = true">+ Nuevo permiso</button>
            </div>

            <div class="permissions-sections">
                <div v-for="group in store.grouped" :key="group.module" class="perm-group">
                    <div class="perm-group-header">
                        <span class="perm-module-badge">{{ group.module }}</span>
                        <span class="text-muted small">{{ group.permissions.length }} permisos</span>
                    </div>
                    <div class="perm-list">
                        <div v-for="p in group.permissions" :key="p.id" class="perm-row">
                            <span class="perm-name">{{ p.name }}</span>
                            <button
                                class="btn-icon danger small"
                                @click="removePermission(p)"
                                title="Eliminar"
                            >✕</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== MODAL ROL ===== -->
        <div v-if="showRoleModal" class="modal-overlay" @click.self="showRoleModal = false">
            <div class="modal-box">
                <div class="modal-header">
                    <h5>{{ editingRole ? 'Editar rol' : 'Nuevo rol' }}</h5>
                    <button @click="showRoleModal = false" class="close-btn">✕</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del rol *</label>
                        <input
                            v-model="roleForm.name"
                            type="text"
                            placeholder="Ej: coordinador"
                            :disabled="editingRole && systemRoles.includes(editingRole.name)"
                        />
                    </div>

                    <div class="form-group">
                        <label>Permisos asignados</label>
                        <div class="permissions-checker">
                            <div v-for="group in store.grouped" :key="group.module" class="perm-check-group">
                                <div class="perm-check-module">
                                    <label class="check-all">
                                        <input
                                            type="checkbox"
                                            :checked="isGroupAllChecked(group)"
                                            :indeterminate.prop="isGroupIndeterminate(group)"
                                            @change="toggleGroup(group, $event.target.checked)"
                                        />
                                        <span class="perm-module-badge">{{ group.module }}</span>
                                    </label>
                                </div>
                                <div class="perm-check-items">
                                    <label
                                        v-for="p in group.permissions"
                                        :key="p.id"
                                        class="perm-check-item"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="p.name"
                                            v-model="roleForm.permissions"
                                        />
                                        <span>{{ p.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="roleError" class="alert-error">{{ roleError }}</div>
                </div>
                <div class="modal-footer">
                    <button @click="showRoleModal = false" class="btn-cancel">Cancelar</button>
                    <button @click="submitRole" :disabled="roleLoading" class="btn-submit">
                        {{ roleLoading ? 'Guardando...' : (editingRole ? 'Actualizar' : 'Crear rol') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== MODAL NUEVO PERMISO ===== -->
        <div v-if="showNewPermModal" class="modal-overlay" @click.self="showNewPermModal = false">
            <div class="modal-box-sm">
                <div class="modal-header">
                    <h5>Nuevo permiso</h5>
                    <button @click="showNewPermModal = false" class="close-btn">✕</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del permiso *</label>
                        <input v-model="newPermName" type="text" placeholder="modulo.accion (Ej: reportes.exportar)" />
                        <span class="field-hint">Formato recomendado: módulo.acción</span>
                    </div>
                    <div v-if="permError" class="alert-error">{{ permError }}</div>
                </div>
                <div class="modal-footer">
                    <button @click="showNewPermModal = false" class="btn-cancel">Cancelar</button>
                    <button @click="submitPermission" :disabled="permLoading" class="btn-submit">
                        {{ permLoading ? 'Guardando...' : 'Crear permiso' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoleStore } from '@/stores/roles.js';

const store = useRoleStore();
const tab   = ref('roles');

const systemRoles = ['super_admin', 'administrador', 'usuario'];

// Rol modal
const showRoleModal = ref(false);
const editingRole   = ref(null);
const roleLoading   = ref(false);
const roleError     = ref(null);
const roleForm      = reactive({ name: '', permissions: [] });

// Permiso modal
const showNewPermModal = ref(false);
const newPermName      = ref('');
const permLoading      = ref(false);
const permError        = ref(null);

onMounted(async () => {
    await Promise.all([store.fetchRoles(), store.fetchPermissions()]);
});

function openCreateRole() {
    editingRole.value = null;
    Object.assign(roleForm, { name: '', permissions: [] });
    roleError.value = null;
    showRoleModal.value = true;
}

function openEditRole(role) {
    editingRole.value = role;
    Object.assign(roleForm, {
        name:        role.name,
        permissions: role.permissions?.map(p => p.name) || [],
    });
    roleError.value = null;
    showRoleModal.value = true;
}

async function submitRole() {
    if (!roleForm.name) { roleError.value = 'El nombre es requerido.'; return; }
    roleLoading.value = true;
    roleError.value   = null;
    try {
        if (editingRole.value) {
            await store.updateRole(editingRole.value.id, roleForm);
        } else {
            await store.createRole(roleForm);
        }
        showRoleModal.value = false;
    } catch (e) {
        roleError.value = e.response?.data?.message || 'Error al guardar el rol.';
    } finally {
        roleLoading.value = false;
    }
}

async function removeRole(role) {
    if (systemRoles.includes(role.name)) return;
    if (!confirm(`¿Eliminar el rol "${role.name}"?`)) return;
    try {
        await store.deleteRole(role.id);
    } catch (e) {
        alert(e.response?.data?.message || 'Error al eliminar.');
    }
}

async function submitPermission() {
    if (!newPermName.value) { permError.value = 'El nombre es requerido.'; return; }
    permLoading.value = true;
    permError.value   = null;
    try {
        await store.createPermission(newPermName.value);
        showNewPermModal.value = false;
        newPermName.value = '';
    } catch (e) {
        permError.value = e.response?.data?.message || 'Error al crear el permiso.';
    } finally {
        permLoading.value = false;
    }
}

async function removePermission(p) {
    if (!confirm(`¿Eliminar el permiso "${p.name}"?`)) return;
    await store.deletePermission(p.id);
}

// Helpers para checkboxes de grupo
function isGroupAllChecked(group) {
    return group.permissions.every(p => roleForm.permissions.includes(p.name));
}

function isGroupIndeterminate(group) {
    const checked = group.permissions.filter(p => roleForm.permissions.includes(p.name));
    return checked.length > 0 && checked.length < group.permissions.length;
}

function toggleGroup(group, checked) {
    group.permissions.forEach(p => {
        const idx = roleForm.permissions.indexOf(p.name);
        if (checked && idx === -1) roleForm.permissions.push(p.name);
        if (!checked && idx !== -1) roleForm.permissions.splice(idx, 1);
    });
}

function roleColor(name) {
    const colors = {
        super_admin: '#A32D2D', administrador: '#185FA5',
        jefe_area: '#BA7517', secretaria: '#0F6E56',
        auditor: '#444441', usuario: '#888',
    };
    return colors[name] || '#378ADD';
}
</script>

<style scoped>
.page-title { font-size: 18px; font-weight: 500; margin-bottom: 4px; }
.page-sub   { font-size: 13px; color: #888; margin-bottom: 20px; }
.tabs { display: flex; border-bottom: 2px solid #e0e0e0; gap: 0; }
.tab  {
    background: none; border: none; padding: 10px 20px;
    font-size: 14px; cursor: pointer; color: #888;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    font-weight: 500;
}
.tab.active { color: #185FA5; border-bottom-color: #185FA5; }
.btn-primary { background: #185FA5; color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 13px; cursor: pointer; }
.roles-grid  { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
.role-card   { background: #fff; border: 0.5px solid #e0e0e0; border-radius: 10px; padding: 16px; }
.role-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.role-icon   {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 15px; font-weight: 700; flex-shrink: 0;
}
.role-name   { font-size: 14px; font-weight: 600; text-transform: capitalize; }
.role-meta   { font-size: 11px; color: #888; }
.role-actions { margin-left: auto; display: flex; gap: 4px; }
.role-permissions { display: flex; flex-wrap: wrap; gap: 4px; }
.perm-chip   {
    font-size: 10px; padding: 2px 8px; border-radius: 10px;
    background: #f0f0f0; color: #555; font-weight: 500;
}
.perm-chip.more { background: #E6F1FB; color: #185FA5; }
.permissions-sections { display: flex; flex-direction: column; gap: 12px; }
.perm-group  { background: #fff; border: 0.5px solid #e0e0e0; border-radius: 10px; overflow: hidden; }
.perm-group-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; background: #f9f9f9;
    border-bottom: 0.5px solid #e0e0e0;
}
.perm-module-badge {
    font-size: 11px; padding: 2px 10px; border-radius: 10px;
    background: #185FA5; color: white; font-weight: 600;
    text-transform: uppercase; letter-spacing: .05em;
}
.perm-list  { padding: 8px 0; }
.perm-row   {
    display: flex; align-items: center; justify-content: space-between;
    padding: 7px 16px; font-size: 13px;
    border-bottom: 0.5px solid #f5f5f5;
}
.perm-row:last-child { border-bottom: none; }
.perm-name  { color: #444; }
.btn-icon   { background: none; border: none; cursor: pointer; padding: 4px 6px; color: #888; border-radius: 4px; font-size: 14px; }
.btn-icon:hover { background: #f0f0f0; }
.btn-icon.danger:hover { background: #FCEBEB; color: #A32D2D; }
.btn-icon:disabled { opacity: 0.3; cursor: not-allowed; }
.btn-icon.small { font-size: 11px; padding: 2px 5px; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-box     { background: #fff; border-radius: 12px; width: 580px; max-width: 95vw; max-height: 90vh; overflow-y: auto; }
.modal-box-sm  { background: #fff; border-radius: 12px; width: 420px; max-width: 95vw; }
.modal-header  { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 0.5px solid #e0e0e0; position: sticky; top: 0; background: #fff; }
.modal-header h5 { margin: 0; font-size: 15px; font-weight: 600; }
.close-btn  { background: none; border: none; cursor: pointer; font-size: 16px; color: #888; }
.modal-body { padding: 20px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 0.5px solid #e0e0e0; position: sticky; bottom: 0; background: #fff; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 12px; font-weight: 500; color: #555; margin-bottom: 6px; }
.form-group input { width: 100%; height: 36px; border: 0.5px solid #ddd; border-radius: 6px; padding: 0 10px; font-size: 13px; outline: none; }
.field-hint { font-size: 11px; color: #888; }
.permissions-checker { border: 0.5px solid #e0e0e0; border-radius: 8px; max-height: 320px; overflow-y: auto; }
.perm-check-group  { border-bottom: 0.5px solid #e0e0e0; }
.perm-check-group:last-child { border-bottom: none; }
.perm-check-module { padding: 8px 12px; background: #f9f9f9; }
.check-all { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; }
.perm-check-items  { display: grid; grid-template-columns: 1fr 1fr; padding: 8px 12px; gap: 6px; }
.perm-check-item   { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #444; cursor: pointer; }
.perm-check-item input { cursor: pointer; }
.alert-error { background: #FCEBEB; color: #A32D2D; border-radius: 6px; padding: 10px 14px; font-size: 13px; }
.btn-cancel  { background: none; border: 0.5px solid #ddd; border-radius: 6px; padding: 8px 16px; font-size: 13px; cursor: pointer; }
.btn-submit  { background: #185FA5; color: white; border: none; border-radius: 6px; padding: 8px 20px; font-size: 13px; cursor: pointer; }
.btn-submit:disabled { opacity: 0.6; }
.d-flex { display: flex; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; }
.mb-3  { margin-bottom: 12px; }
.mb-4  { margin-bottom: 16px; }
.text-muted { color: #888; }
.small { font-size: 12px; }
.text-center { text-align: center; }
.py-5  { padding: 20px 0; }
</style>