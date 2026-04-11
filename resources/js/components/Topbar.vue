<template>
    <div class="topbar">
        <button class="topbar-toggle" @click="$emit('toggle')">
            <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
        </button>
        <div class="topbar-breadcrumb">
            DMS / <span>{{ viewLabels[currentView] || 'Dashboard' }}</span>
        </div>
        <div class="topbar-search">
            <svg class="topbar-search-icon" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <input type="text" placeholder="Buscar documentos..." v-model="searchQuery" />
        </div>
        <div class="topbar-actions">
            <button class="topbar-btn">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                <span class="notif-dot"></span>
            </button>
            <div class="topbar-avatar">{{ userInitials }}</div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth.js';

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
</script>

<style scoped>
.topbar {
    height: 56px;
    background: #fff;
    border-bottom: 0.5px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 20px;
    flex-shrink: 0;
}
.topbar-toggle {
    width: 32px; height: 32px;
    border: 0.5px solid #e0e0e0;
    border-radius: 6px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    background: none; color: #666;
}
.topbar-toggle:hover { background: #f5f5f5; }
.topbar-toggle svg { width: 16px; height: 16px; fill: currentColor; }
.topbar-breadcrumb { font-size: 13px; color: #888; display: flex; align-items: center; gap: 6px; }
.topbar-breadcrumb span { color: #222; font-weight: 500; }
.topbar-search { flex: 1; max-width: 320px; margin-left: 8px; position: relative; }
.topbar-search input {
    width: 100%; height: 32px;
    border: 0.5px solid #e0e0e0; border-radius: 6px;
    padding: 0 12px 0 32px; font-size: 13px;
    background: #f5f5f5; outline: none;
}
.topbar-search-icon {
    position: absolute; left: 10px; top: 50%;
    transform: translateY(-50%);
    width: 14px; height: 14px; fill: #888;
}
.topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 8px; }
.topbar-btn {
    width: 32px; height: 32px;
    border: 0.5px solid #e0e0e0; border-radius: 6px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    background: none; color: #666; position: relative;
}
.topbar-btn svg { width: 15px; height: 15px; fill: currentColor; }
.notif-dot {
    position: absolute; top: 5px; right: 5px;
    width: 6px; height: 6px;
    background: #E24B4A; border-radius: 50%;
}
.topbar-avatar {
    width: 30px; height: 30px;
    background: #378ADD; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 11px; font-weight: 600; cursor: pointer;
}
</style>