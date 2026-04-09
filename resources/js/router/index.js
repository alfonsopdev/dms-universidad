import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth.js';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/views/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/documentos',
        name: 'documentos',
        component: () => import('@/views/documents/Index.vue'),
        meta: { requiresAuth: true, permission: 'documentos.ver' }
    },
    {
        path: '/',
        redirect: '/dashboard'
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;