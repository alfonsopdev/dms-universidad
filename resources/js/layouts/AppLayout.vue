<template>
    <div class="app-wrapper">
        <Sidebar :collapsed="sidebarCollapsed" @navigate="currentView = $event" :currentView="currentView" />
        <div class="main">
            <Topbar :collapsed="sidebarCollapsed" :currentView="currentView" @toggle="sidebarCollapsed = !sidebarCollapsed" />
            <div class="content">
                <router-view />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from '@/components/Sidebar.vue';
import Topbar from '@/components/Topbar.vue';

const sidebarCollapsed = ref(false);
const currentView = ref('dashboard');
</script>

<style scoped>
.app-wrapper {
    display: flex;
    height: 100vh;
    overflow: hidden;
    background: #f4f5f7;
}
.main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.content {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}
.content::-webkit-scrollbar { width: 4px; }
.content::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
</style>