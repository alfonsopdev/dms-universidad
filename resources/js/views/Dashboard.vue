<template>
    <div>
        <h1>Dashboard</h1>
        <p>Bienvenido {{ authStore.user?.name }}</p>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();

onMounted(() => {
    authStore.fetchUser();
});
</script>