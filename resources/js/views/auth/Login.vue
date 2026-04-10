<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow-sm" style="width: 400px;">
            <div class="card-body p-5">

                <!-- Logo / Título -->
                <div class="text-center mb-4">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center 
                                justify-content-center mb-3" 
                         style="width:60px; height:60px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" 
                             fill="white" viewBox="0 0 16 16">
                            <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 
                                     0 2-2V4.707L9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1z"/>
                        </svg>
                    </div>
                    <h4 class="fw-bold text-dark">DMS Universidad</h4>
                    <p class="text-muted small">Sistema de Gestión Documental</p>
                </div>

                <!-- Error message -->
                <div v-if="error" class="alert alert-danger small" role="alert">
                    {{ error }}
                </div>

                <!-- Botón Google -->
                <a href="/auth/google" class="btn btn-outline-secondary w-100 d-flex 
                                              align-items-center justify-content-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 
                                               30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 
                                               13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 
                                               2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 
                                               7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59
                                               l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 
                                               10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 
                                               1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 
                                               6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Continuar con Google
                </a>

                <hr class="my-4">
                <p class="text-center text-muted small mb-0">
                    © {{ new Date().getFullYear() }} Universidad — DMS
                </p>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const error = ref(null);
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('error')) {
    error.value = 'Error al autenticar con Google. Intenta nuevamente.';
}
</script>