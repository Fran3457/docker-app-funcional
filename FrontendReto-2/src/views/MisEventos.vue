<script setup>
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';

const misEventos = ref([]);
const cargando = ref(true);
const API_URL = 'http://localhost:8080/backend';

const cargarMisEventos = async () => {
    try {
        const response = await fetch(`${API_URL}/users/me/events`, {
            credentials: 'include'
        });

        if (response.ok) {
            misEventos.value = await response.json();
        }
    } catch (error) {
        console.error(error);
    } finally {
        cargando.value = false;
    }
};

const desapuntarse = async (evento) => {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: `Vas a liberar tu plaza en "${evento.titulo}"`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desapuntarme',
        cancelButtonText: 'Cancelar',
        background: '#1f2937',
        color: '#fff'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`${API_URL}/events/${evento.id}/signup?action=delete`, {
            method: 'POST',
            credentials: 'include'
        });

        // Intentamos leer la respuesta JSON del servidor
        const data = await response.json().catch(() => null);

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Hecho',
                text: 'Te has desapuntado correctamente',
                background: '#1f2937',
                color: '#fff',
                timer: 1500,
                showConfirmButton: false
            });
            cargarMisEventos();
        } else {
            // Si el servidor envía un error específico, lo usamos. Si no, uno genérico.
            const mensajeError = data?.error || 'No se pudo completar la acción';
            throw new Error(mensajeError);
        }
    } catch (e) {
        console.error("Error al desapuntarse:", e);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            // Aquí mostramos el mensaje real del servidor
            text: e.message,
            background: '#1f2937',
            color: '#fff'
        });
    }
};

onMounted(() => {
    cargarMisEventos();
});

// --- CORRECCIÓN AQUÍ: Usamos la lógica del servidor ---
const getImageUrl = (imgName) => {
    // 1. Si no hay imagen, usamos la default del backend
    if (!imgName) return `${API_URL}/img/default.png`;

    // 2. Si es URL externa, la dejamos tal cual
    if (imgName.startsWith('http')) return imgName;

    // 3. Si tiene extensión (ej: foto.jpg), buscamos en backend/img
    if (imgName.includes('.')) {
        return `${API_URL}/img/${imgName}`;
    }

    // 4. Si es un nombre antiguo sin extensión, le añadimos .png y buscamos en backend
    return `${API_URL}/img/${imgName}.png`;
};

const formatearFecha = (fecha) => new Date(fecha).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
</script>

<template>
    <main
        class="grow relative bg-[url('/img/fondoview.png')] bg-cover bg-center bg-no-repeat flex items-center justify-center min-h-screen">
        <div class="absolute inset-0 bg-gray-900/30"></div>

        <div class="relative z-10 w-full min-h-screen p-8 text-white">
            <div class="max-w-5xl mx-auto">

                <h1 class="text-3xl font-bold text-pink-500 mb-6 border-b border-gray-700 pb-4 drop-shadow-md">
                    Mis Inscripciones
                </h1>

                <div v-if="cargando" class="flex justify-center items-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-pink-500"></div>
                </div>

                <div v-else-if="misEventos.length === 0"
                    class="text-center py-20 bg-gray-800/80 backdrop-blur-sm rounded-xl border border-dashed border-gray-600 animate-fade-in-up">
                    <p class="text-xl text-gray-300 mb-4 ml-8 mr-8">No estás apuntado a ningún evento aún.</p>
                    <router-link to="/eventos"
                        class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-2 rounded-full font-bold transition shadow-lg hover:shadow-pink-500/20 inline-block focus:outline-none focus:ring-2 focus:ring-white">
                        Ver Eventos Disponibles
                    </router-link>
                </div>

                <div v-else class="space-y-4" aria-live="polite">

                    <transition-group name="list" tag="div" class="space-y-4">
                        <div v-for="evento in misEventos" :key="evento.id"
                            class="bg-gray-800/90 backdrop-blur-md rounded-lg overflow-hidden flex flex-col md:flex-row shadow-lg border border-gray-700 hover:border-pink-500/50 transition-all duration-300 hover:transform hover:translate-x-1">

                            <div class="md:w-48 h-48 md:h-auto relative shrink-0">
                                <img :src="getImageUrl(evento.imagen)" :alt="'Portada del evento ' + evento.titulo"
                                    class="w-full h-full object-cover">
                            </div>

                            <div class="p-6 grow flex flex-col justify-center">
                                <div class="flex justify-between items-start">
                                    <h2 class="text-xl font-bold mb-2 text-white">{{ evento.titulo }}</h2>
                                    <span
                                        class="bg-pink-900/80 text-pink-200 text-xs px-2 py-1 rounded shadow-sm border border-pink-500/20">{{
                                            evento.tipo }}</span>
                                </div>

                                <p class="text-gray-300 text-sm mb-4 line-clamp-2">{{ evento.descripcion }}</p>

                                <div class="flex items-center gap-4 text-sm text-gray-400 mb-4">
                                    <span><i class="fas fa-calendar mr-1 text-pink-500" aria-hidden="true"></i> {{
                                        formatearFecha(evento.fecha) }}</span>
                                    <span><i class="fas fa-clock mr-1 text-pink-500" aria-hidden="true"></i> {{
                                        evento.hora.substring(0, 5) }}</span>
                                </div>

                                <div class="mt-auto">
                                    <button @click="desapuntarse(evento)"
                                        class="btn-danger-outline px-4 py-2 rounded text-sm font-bold transition-all flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-red-400"
                                        aria-label="Cancelar inscripción en este evento">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i> Cancelar Inscripción
                                    </button>
                                </div>
                            </div>
                        </div>
                    </transition-group>
                </div>

            </div>
        </div>
    </main>
</template>

<style scoped>
/* CSS MODERNO: Variables locales para el botón de peligro */
.btn-danger-outline {
    --color-danger: #f87171;
    /* red-400 */
    color: var(--color-danger);
    border: 1px solid var(--color-danger);
    background-color: transparent;
}

.btn-danger-outline:hover {
    background-color: #dc2626;
    /* red-600 */
    border-color: #dc2626;
    color: white;
}

/* MICROINTERACCIONES (30 pts - Animaciones de lista) */
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

.list-leave-active {
    position: absolute;
    /* Asegura que al irse deje espacio suavemente */
    width: 100%;
    /* Mantiene el ancho al salir */
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
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