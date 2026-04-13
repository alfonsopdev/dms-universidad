<template>
    <div class="topbar">
        <button class="topbar-toggle" @click="$emit('toggle')">
            <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
        </button>

        <div class="topbar-breadcrumb">
            DMS / <span>{{ viewLabels[currentView] || 'Dashboard' }}</span>
        </div>

        <div class="topbar-search">
            <svg class="topbar-search-icon" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 14z"/></svg>
            <input type="text" placeholder="Buscar documentos..." v-model="searchQuery" />
        </div>

        <div class="topbar-actions">
            <button class="topbar-btn">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                <span class="notif-dot"></span>
            </button>

            <div class="topbar-avatar" @click="showMenu = !showMenu">
                {{ userInitials }}
                
                <transition name="dropdown">
                    <div v-if="showMenu" class="avatar-menu" @click.stop>
                        
                        <div class="menu-header">
                            <div class="avatar-name">{{ authStore.user?.name || 'Usuario' }}</div>
                            <div class="avatar-role">{{ authStore.user?.roles?.[0]?.Name || authStore.user?.roles?.[0]?.name || 'rol no definido' }}</div>
                        </div>

                        <div class="menu-body">
                            <button @click="logout" class="menu-item logout-btn">
                                <svg viewBox="0 0 24 24">
                                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                                </svg>
                                <span>Cerrar sesión</span>
                            </button>
                        </div>

                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth.js';

const showMenu = ref(false);

const props = defineProps({
    collapsed: Boolean,
    currentView: String,
});
defineEmits(['toggle']);

const authStore = useAuthStore();
const searchQuery = ref('');

const viewLabels = {
    dashboard: 'Dashboard', docs: 'Documentos',
    users: 'Usuarios', reports: 'Reportes',
    modules: 'Módulos', config: 'Configuración',
};

const userInitials = computed(() => {
    const name = authStore.user?.name || 'U';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

async function logout() {
    showMenu.value = false;
    await authStore.logout();
}

function handleClick(e) {
    if (!e.target.closest('.topbar-avatar')) showMenu.value = false;
}

onMounted(() => document.addEventListener('click', handleClick));
onUnmounted(() => document.removeEventListener('click', handleClick));
</script>

<style scoped>
.topbar {
    height: 56px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 20px;
    flex-shrink: 0;
}
.topbar-toggle {
    width: 32px; height: 32px;
    border: 1px solid #e5e7eb;
    border-radius: 8px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    background: none; color: #6b7280;
    transition: background 0.2s;
}
.topbar-toggle:hover { background: #f3f4f6; }
.topbar-toggle svg { width: 18px; height: 18px; fill: currentColor; }
.topbar-breadcrumb { font-size: 14px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.topbar-breadcrumb span { color: #111827; font-weight: 600; }
.topbar-search { flex: 1; max-width: 350px; margin-left: 16px; position: relative; }
.topbar-search input {
    width: 100%; height: 36px;
    border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 0 12px 0 36px; font-size: 13px;
    background: #f9fafb; outline: none;
    transition: border-color 0.2s, background 0.2s;
}
.topbar-search input:focus {
    border-color: #378ADD; background: #fff;
    box-shadow: 0 0 0 3px rgba(55, 138, 221, 0.1);
}
.topbar-search-icon {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%);
    width: 16px; height: 16px; fill: #9ca3af;
}
.topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 12px; }
.topbar-btn {
    width: 36px; height: 36px;
    border: 1px solid #e5e7eb; border-radius: 8px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    background: #fff; color: #6b7280; position: relative;
    transition: background 0.2s;
}
.topbar-btn:hover { background: #f3f4f6; }
.topbar-btn svg { width: 18px; height: 18px; fill: currentColor; }
.notif-dot {
    position: absolute; top: 6px; right: 6px;
    width: 8px; height: 8px;
    background: #ef4444; border-radius: 50%;
    border: 2px solid #fff;
}

/* --- Avatar y Menú Desplegable Profesional --- */
.topbar-avatar {
    position: relative; 
    width: 36px; height: 36px;
    background: linear-gradient(135deg, #378ADD, #2A68A6); 
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 13px; font-weight: 600; cursor: pointer;
    box-shadow: 0 2px 4px rgba(55, 138, 221, 0.2);
    transition: transform 0.2s;
}
.topbar-avatar:hover { transform: scale(1.05); }

.avatar-menu {
    position: absolute; top: 48px; right: 0;
    background: #fff; 
    border: 1px solid #e5e7eb;
    border-radius: 12px; 
    min-width: 220px; z-index: 1000;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
    text-align: left; 
    cursor: default;
    overflow: hidden; /* Para que la cabecera respete los bordes redondeados */
}

.menu-header {
    padding: 16px;
    background: #f9fafb; /* Fondo ligeramente gris para separar */
    border-bottom: 1px solid #e5e7eb;
}
.avatar-name { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 2px; }
.avatar-role { font-size: 12px; font-weight: 500; color: #6b7280; text-transform: capitalize; }

.menu-body {
    padding: 8px;
}

.menu-item {
    display: flex; align-items: center; gap: 10px;
    width: 100%; padding: 10px 12px;
    background: none; border: none; border-radius: 8px;
    cursor: pointer; font-size: 13px; font-weight: 500;
    font-family: inherit; transition: all 0.2s ease;
}
.menu-item svg { width: 16px; height: 16px; fill: currentColor; opacity: 0.8; }

/* Botón específico de Cerrar Sesión */
.logout-btn { color: #dc2626; }
.logout-btn:hover { background: #fef2f2; color: #b91c1c; }

/* --- Animaciones de Vue (<transition name="dropdown">) --- */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}
</style>