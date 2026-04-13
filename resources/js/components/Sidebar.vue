<template>
    <aside class="sidebar" :class="{ collapsed }">
        <!-- Logo -->
        <div class="sb-logo">
            <div class="sb-logo-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
            </div>
            <div v-show="!collapsed">
                <div class="sb-logo-text">DMS</div>
                <div class="sb-logo-sub">Universidad</div>
            </div>
        </div>

        <!-- Nav -->
        <div class="sb-scroll">
            <div class="sb-section">
                <div class="sb-section-label">Principal</div>
            </div>

            <div v-for="item in menuItems" :key="item.view">
                <div class="sb-item" :class="{ active: currentView === item.view }"
                     @click="navigate(item.view)">
                    <svg viewBox="0 0 24 24" v-html="item.icon"></svg>
                    <span class="sb-item-label">{{ item.label }}</span>
                    <span v-if="item.badge && !collapsed" class="sb-item-badge">{{ item.badge }}</span>
                </div>
                <!-- Submenú documentos -->
                <div v-if="item.view === 'docs' && currentView === 'docs' && !collapsed" class="sb-sub">
                    <div class="sb-item" :class="{ active: docSubView === 'all' }" @click="docSubView = 'all'">
                        <span class="sb-item-label">Todos los documentos</span>
                    </div>
                    <div class="sb-item" :class="{ active: docSubView === 'favorites' }" @click="docSubView = 'favorites'">
                        <span class="sb-item-label">Favoritos</span>
                    </div>
                    <div class="sb-item" :class="{ active: docSubView === 'drafts' }" @click="docSubView = 'drafts'">
                        <span class="sb-item-label">Borradores</span>
                    </div>
                    <div class="sb-item" :class="{ active: docSubView === 'trash' }" @click="docSubView = 'trash'">
                        <span class="sb-item-label">Papelera</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer usuario -->
        <div class="sb-footer">
            <div class="sb-user">
                <div class="sb-avatar">{{ userInitials }}</div>
                <div class="sb-user-info" v-show="!collapsed">
                    <div class="sb-user-name">{{ authStore.user?.name || 'Usuario' }}</div>
                    <div class="sb-user-role">{{ userRole }}</div>
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth.js';
import { useRouter } from 'vue-router';

// 1. Inicializamos el Router
const router = useRouter();

const props = defineProps({
    collapsed: Boolean,
    currentView: String,
});

const emit = defineEmits(['navigate']);

const authStore = useAuthStore();
const docSubView = ref('all');

// 2. Definimos el Mapa de Rutas (Esto es lo que faltaba)
const routeMap = {
    dashboard: '/dashboard',
    docs:      '/documentos',
    users:     '/usuarios',
    reports:   '/reportes',
    modules:   '/modulos',
    config:    '/configuracion',
};

// 3. Lógica del usuario
const userInitials = computed(() => {
    const name = authStore.user?.name || 'U';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const userRole = computed(() => {
    return authStore.user?.roles?.[0]?.name || 'usuario';
});

// 4. Función de navegación integrada
function navigate(view) {
    // Avisamos al componente padre del cambio de vista
    emit('navigate', view);
    
    // Cambiamos la URL usando el router y nuestro mapa
    // Si la vista no existe en el mapa, nos manda al dashboard por defecto
    router.push(routeMap[view] || '/dashboard');
}

// 5. Estructura del Menú
const menuItems = [
    {
        view: 'dashboard', label: 'Dashboard',
        icon: '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>'
    },
    {
        view: 'docs', label: 'Documentos', badge: null,
        icon: '<path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>'
    },
    {
        view: 'users', label: 'Usuarios',
        icon: '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>'
    },
    {
        view: 'reports', label: 'Reportes',
        icon: '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H7v-2h5v2zm5-4H7v-2h10v2zm0-4H7V7h10v2z"/>'
    },
    {
        view: 'modules', label: 'Módulos',
        icon: '<path d="M4 8h4V4H4v4zm6 12h4v-4h-4v4zm-6 0h4v-4H4v4zm0-6h4v-4H4v4zm6 0h4v-4h-4v4zm6-10v4h4V4h-4zm-6 4h4V4h-4v4zm6 6h4v-4h-4v4zm0 6h4v-4h-4v4z"/>'
    },
    {
        view: 'config', label: 'Configuración',
        icon: '<path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>'
    },
];
</script>

<style scoped>
.sidebar {
    width: 240px;
    background: #0c1a2e;
    display: flex;
    flex-direction: column;
    transition: width .25s ease;
    flex-shrink: 0;
    overflow: hidden;
}
.sidebar.collapsed { width: 60px; }
.sb-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 14px;
    height: 56px;
    border-bottom: 0.5px solid #1e3a5f;
    flex-shrink: 0;
}
.sb-logo-icon {
    width: 30px; height: 30px;
    background: #378ADD;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sb-logo-icon svg { width: 16px; height: 16px; fill: white; }
.sb-logo-text { color: #fff; font-weight: 600; font-size: 15px; }
.sb-logo-sub { color: #b5c4d8; font-size: 10px; }
.sb-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 8px 0; }
.sb-section { padding: 12px 14px 4px; }
.sb-section-label {
    font-size: 9px; font-weight: 600;
    letter-spacing: .08em; color: #4a6080;
    text-transform: uppercase;
}
.sb-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 14px; cursor: pointer;
    color: #b5c4d8; font-size: 13px;
    white-space: nowrap; position: relative;
    transition: background .15s;
}
.sb-item:hover { background: #162d4a; color: #fff; }
.sb-item.active { background: #1e3a5f; color: #fff; border-left: 2px solid #378ADD; }
.sb-item svg { width: 16px; height: 16px; fill: currentColor; flex-shrink: 0; }
.sb-item-label { overflow: hidden; }
.sb-item-badge {
    margin-left: auto;
    background: #378ADD; color: white;
    font-size: 10px; font-weight: 600;
    padding: 1px 6px; border-radius: 10px;
}
.sb-sub { padding-left: 40px; }
.sb-sub .sb-item { font-size: 12px; padding: 6px 14px 6px 0; color: #6a88a8; }
.sb-sub .sb-item.active { color: #fff; border-left: none; background: none; }
.sb-footer { padding: 12px 14px; border-top: 0.5px solid #1e3a5f; flex-shrink: 0; }
.sb-user { display: flex; align-items: center; gap: 10px; }
.sb-avatar {
    width: 30px; height: 30px;
    background: #378ADD; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 11px; font-weight: 600;
    flex-shrink: 0;
}
.sb-user-name { color: #fff; font-size: 12px; font-weight: 500; }
.sb-user-role { color: #b5c4d8; font-size: 10px; }
</style>