<template>
    <div>
        <div class="page-title">Dashboard</div>
        <div class="page-sub">Bienvenido, {{ authStore.user?.name || 'Usuario' }}</div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card" v-for="stat in stats" :key="stat.label">
                <div class="stat-label">{{ stat.label }}</div>
                <div class="stat-value">{{ stat.value }}</div>
                <div class="stat-delta" :class="stat.up ? 'delta-up' : 'delta-down'">
                    {{ stat.delta }}
                </div>
            </div>
        </div>

        <!-- Two columns -->
        <div class="two-col">
            <!-- Documentos recientes -->
            <div class="card">
                <div class="card-title">Documentos recientes</div>
                <div class="doc-row" v-for="doc in recentDocs" :key="doc.name">
                    <div class="doc-icon" :class="doc.type">{{ doc.type.toUpperCase() }}</div>
                    <div class="doc-name">{{ doc.name }}</div>
                    <div class="doc-date">{{ doc.date }}</div>
                    <span class="doc-badge" :class="'badge-' + doc.status">{{ doc.statusLabel }}</span>
                </div>
            </div>

            <!-- Actividad -->
            <div class="card">
                <div class="card-title">Actividad reciente</div>
                <div class="activity-item" v-for="act in activity" :key="act.text">
                    <div class="act-dot"></div>
                    <div>
                        <div class="act-text" v-html="act.text"></div>
                        <div class="act-time">{{ act.time }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth.js';
import { onMounted } from 'vue';

const authStore = useAuthStore();

onMounted(() => { authStore.fetchUser(); });

const stats = [
    { label: 'Total documentos', value: '248', delta: '+12 este mes', up: true },
    { label: 'Vigentes', value: '184', delta: '+5 esta semana', up: true },
    { label: 'En revisión', value: '23', delta: '-3 vs anterior', up: false },
    { label: 'Obsoletos', value: '41', delta: '+2 este mes', up: false },
];

const recentDocs = [
    { name: 'Reglamento de estudios 2025', type: 'pdf', date: 'Hoy', status: 'review', statusLabel: 'En revisión' },
    { name: 'Plan de trabajo Q2', type: 'doc', date: 'Ayer', status: 'approved', statusLabel: 'Aprobado' },
    { name: 'Presupuesto anual', type: 'xls', date: '28 mar', status: 'draft', statusLabel: 'Borrador' },
    { name: 'Acta de reunión #14', type: 'doc', date: '27 mar', status: 'approved', statusLabel: 'Aprobado' },
];

const activity = [
    { text: '<strong>María López</strong> subió una nueva versión de Reglamento', time: 'Hace 10 min' },
    { text: '<strong>Carlos Pérez</strong> aprobó Plan de trabajo Q2', time: 'Hace 1 hora' },
    { text: '<strong>Sistema</strong> archivó 3 documentos obsoletos', time: 'Hace 3 horas' },
    { text: '<strong>Ana Torres</strong> creó Acta de reunión #14', time: 'Ayer 4:30 pm' },
];
</script>

<style scoped>
.page-title { font-size: 18px; font-weight: 500; margin-bottom: 4px; }
.page-sub { font-size: 13px; color: #888; margin-bottom: 20px; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
.stat-card { background: #fff; border: 0.5px solid #e0e0e0; border-radius: 10px; padding: 16px; }
.stat-label { font-size: 11px; color: #888; font-weight: 500; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .05em; }
.stat-value { font-size: 24px; font-weight: 500; line-height: 1; }
.stat-delta { font-size: 11px; margin-top: 6px; }
.delta-up { color: #3B6D11; }
.delta-down { color: #A32D2D; }
.two-col { display: grid; grid-template-columns: 1.6fr 1fr; gap: 12px; }
.card { background: #fff; border: 0.5px solid #e0e0e0; border-radius: 10px; padding: 16px; }
.card-title { font-size: 13px; font-weight: 500; margin-bottom: 14px; }
.doc-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 0.5px solid #e0e0e0; }
.doc-row:last-child { border-bottom: none; }
.doc-icon { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 9px; font-weight: 700; }
.doc-icon.pdf { background: #FCEBEB; color: #A32D2D; }
.doc-icon.doc { background: #E6F1FB; color: #185FA5; }
.doc-icon.xls { background: #EAF3DE; color: #3B6D11; }
.doc-name { font-size: 12px; flex: 1; }
.doc-date { font-size: 11px; color: #888; }
.doc-badge { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 500; }
.badge-review { background: #FAEEDA; color: #633806; }
.badge-approved { background: #EAF3DE; color: #27500A; }
.badge-draft { background: #F1EFE8; color: #444441; }
.activity-item { display: flex; gap: 10px; margin-bottom: 12px; }
.act-dot { width: 8px; height: 8px; border-radius: 50%; background: #378ADD; margin-top: 4px; flex-shrink: 0; }
.act-text { font-size: 12px; color: #666; line-height: 1.5; }
.act-time { font-size: 10px; color: #999; margin-top: 2px; }
</style>