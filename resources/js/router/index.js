import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth.js';
import AppLayout from '@/layouts/AppLayout.vue';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            { path: '', redirect: '/dashboard' },
            { path: '/dashboard', name: 'dashboard', component: () => import('@/views/Dashboard.vue') },
            { path: '/documentos', name: 'documentos', component: () => import('@/views/documents/Index.vue') },
            { path: '/usuarios', name: 'usuarios', component: () => import('@/views/Users.vue') },
            { path: '/reportes', name: 'reportes', component: () => import('@/views/Reports.vue') },
            { path: '/modulos', name: 'modulos', component: () => import('@/views/Modules.vue') },
            { path: '/configuracion', name: 'config', component: () => import('@/views/Config.vue') },
        ]
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