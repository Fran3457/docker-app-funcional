<script setup>
import { ref, reactive } from 'vue';

const emit = defineEmits(['close', 'login-success']);

const isLoginMode = ref(true);
const errorMsg = ref('');
const successMsg = ref('');
// UX: Estado de carga (Loading)
const isLoading = ref(false);

const form = reactive({
    username: '',
    email: '',
    password: ''
});

const API_URL = 'http://localhost:8080/backend';

const toggleMode = () => {
    isLoginMode.value = !isLoginMode.value;
    errorMsg.value = '';
    successMsg.value = '';
    form.username = '';
    form.email = '';
    form.password = '';
};

const handleSubmit = async () => {
    errorMsg.value = '';
    successMsg.value = '';
    // UX: Activamos estado de carga
    isLoading.value = true;

    const endpoint = isLoginMode.value ? '/auth/login' : '/auth/register';

    try {
        const response = await fetch(`${API_URL}${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(form)
        });

        const data = await response.json();

        if (response.ok) {
            if (isLoginMode.value) {
                emit('login-success', data);
                emit('close');
            } else {
                successMsg.value = '¡Cuenta creada! Ahora inicia sesión.';
                setTimeout(() => {
                    isLoginMode.value = true;
                    successMsg.value = '';
                }, 1500);
            }
        } else {
            errorMsg.value = data.error || 'Ocurrió un error';
        }
    } catch (e) {
        errorMsg.value = 'Error de conexión con el servidor';
    } finally {
        // UX: Desactivamos carga siempre (éxito o error)
        isLoading.value = false;
    }
};
</script>

<template>
    <div role="dialog" aria-modal="true" aria-labelledby="modal-title"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity"
        @click.self="$emit('close')">

        <div
            class="bg-gray-800 w-full max-w-md rounded-2xl shadow-2xl border border-gray-700 relative overflow-hidden animate-fade-in-up">

            <button @click="$emit('close')"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded"
                aria-label="Cerrar ventana modal">
                <span class="text-xl font-bold" aria-hidden="true">✕</span>
            </button>

            <div class="bg-linear-to-r from-pink-600 to-purple-600 p-6 text-center">
                <h2 id="modal-title" class="text-2xl font-bold text-white tracking-wide">
                    {{ isLoginMode ? 'Bienvenido de nuevo' : 'Únete a GameFest' }}
                </h2>
                <p class="text-pink-100 text-sm mt-1">
                    {{ isLoginMode ? 'Accede a tu cuenta para gestionar eventos' : 'Crea tu cuenta en segundos' }}
                </p>
            </div>

            <div class="p-8">

                <div v-if="errorMsg" role="alert"
                    class="mb-4 p-3 bg-red-500/20 border border-red-500 rounded text-red-200 text-sm text-center">
                    {{ errorMsg }}
                </div>
                <div v-if="successMsg" role="alert"
                    class="mb-4 p-3 bg-green-500/20 border border-green-500 rounded text-green-200 text-sm text-center">
                    {{ successMsg }}
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4">

                    <div v-if="!isLoginMode">
                        <label for="username" class="block text-sm text-gray-400 mb-1">Nombre de usuario</label>
                        <input id="username" v-model="form.username" type="text" required
                            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 outline-none transition disabled:opacity-50"
                            placeholder="Ej: Gamer123" :disabled="isLoading">
                    </div>

                    <div>
                        <label for="email" class="block text-sm text-gray-400 mb-1">Correo Electrónico</label>
                        <input id="email" v-model="form.email" type="email" required
                            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 outline-none transition disabled:opacity-50"
                            placeholder="tu@email.com" :disabled="isLoading">
                    </div>

                    <div>
                        <label for="password" class="block text-sm text-gray-400 mb-1">Contraseña</label>
                        <input id="password" v-model="form.password" type="password" required
                            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 outline-none transition disabled:opacity-50"
                            placeholder="••••••••" :disabled="isLoading">
                    </div>

                    <button type="submit" :disabled="isLoading"
                        class="btn-primary w-full text-white font-bold py-2 rounded-lg transition-all transform hover:scale-[1.01] shadow-lg shadow-pink-600/30 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                        <span v-if="isLoading"
                            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>{{ isLoading ? 'Procesando...' : (isLoginMode ? 'Iniciar Sesión' : 'Registrarse')
                        }}</span>
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-400">
                    <p v-if="isLoginMode">
                        ¿Aún no tienes cuenta?
                        <button @click="toggleMode"
                            class="text-pink-400 hover:text-pink-300 font-semibold hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-400 rounded">
                            Regístrate gratis
                        </button>
                    </p>
                    <p v-else>
                        ¿Ya tienes cuenta?
                        <button @click="toggleMode"
                            class="text-pink-400 hover:text-pink-300 font-semibold hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-400 rounded">
                            Inicia sesión
                        </button>
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
/* CSS MODERNO: Definición de variables locales para este componente */
.btn-primary {
    /* Usamos la variable global definida en App.vue */
    background-color: var(--color-primary, #db2777);
}

.btn-primary:hover:not(:disabled) {
    /* CSS MODERNO (REQUISITO EXPLICITO): color-mix
       Mezclamos el color primario con negro al 10% para oscurecerlo en hover.
       Esto evita tener que adivinar el código hexadecimal más oscuro.
    */
    background-color: color-mix(in srgb, var(--color-primary, #db2777), black 10%);
}

.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>