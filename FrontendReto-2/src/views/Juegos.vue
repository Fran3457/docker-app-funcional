<script setup>
import { ref, onMounted, watch } from 'vue';

const juegos = ref([]);
const juegoSeleccionado = ref(null);
const cargandoLista = ref(false);

// CAMBIO: Ahora solo tenemos una variable reactiva
const busquedaGlobal = ref('');

// Si estás en Docker o Servidor, asegúrate de que esta URL es correcta
const API_URL = 'http://localhost:8080/backend';

const getImageUrl = (imgName) => {
  try {
    return new URL(`/src/assets/games/${imgName}`, import.meta.url).href;
  } catch (e) {
    return '';
  }
};

const cargarJuegos = async () => {
  cargandoLista.value = true;
  try {
    // CAMBIO: Enviamos solo el parámetro 'q'
    const params = new URLSearchParams({
      q: busquedaGlobal.value
    });

    const response = await fetch(`${API_URL}/games?${params.toString()}`);
    if (!response.ok) throw new Error('Error al conectar con el servidor');

    juegos.value = await response.json();
  } catch (error) {
    console.error("Error:", error);
  } finally {
    cargandoLista.value = false;
  }
};

onMounted(() => cargarJuegos());

// CAMBIO: Vigilamos solo la variable de búsqueda
watch(busquedaGlobal, () => {
  cargarJuegos();
});

const verDetalles = (id) => {
  const juego = juegos.value.find(j => j.id === id);
  if (juego) {
    juegoSeleccionado.value = juego;
  }
};
</script>

<template>
  <main
    class="grow relative bg-[url('/img/fondoview.png')] bg-cover bg-center bg-no-repeat bg-fixed flex items-start justify-center min-h-screen pt-10"
    aria-labelledby="games-catalog-title">
    <div class="absolute inset-0 bg-gray-900/40"></div>

    <div class="relative z-10 w-full text-white px-4 py-8">
      <div class="max-w-7xl mx-auto">

        <h1 id="games-catalog-title" class="text-4xl font-bold mb-6 text-pink-500 drop-shadow-md text-center">
          Catálogo de Juegos
        </h1>

        <div class="max-w-2xl mx-auto mb-10">
          <label for="buscador-global" class="sr-only">Buscar juegos</label>
          <!--En php usamon (trim) evitar los espacios, se usa 'COLLATE utf8mb4_unicode_ci' para evitar acentos y Mayus en funciones-->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <input id="buscador-global" v-model="busquedaGlobal" type="text"
              placeholder="Escribe un título, género o plataforma"
              class="w-full pl-10 pr-4 py-4 rounded-full bg-gray-800/90 backdrop-blur-md border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent shadow-xl transition-all text-lg">
          </div>
        </div>

        <div v-if="cargandoLista" class="flex justify-center items-center py-20 text-pink-500">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-current"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
          <div v-for="juego in juegos" :key="juego.id"
            class="bg-gray-800/95 backdrop-blur-sm rounded-xl overflow-hidden shadow-lg hover:shadow-pink-500/20 hover:-translate-y-1 transition-all duration-300 group flex flex-col border border-gray-700 hover:border-pink-500/50">
            <div class="h-48 overflow-hidden relative">
              <img :src="getImageUrl(juego.imagen)" :alt="'Carátula del videojuego ' + juego.titulo"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
              <span class="absolute top-2 right-2 bg-pink-600 text-white text-xs font-bold px-2 py-1 rounded shadow-md">
                {{ juego.genero }}
              </span>
            </div>

            <div class="p-6 flex flex-col grow">
              <div class="mb-2">
                <h2 class="text-xl font-bold text-white group-hover:text-pink-400 transition-colors line-clamp-1"
                  :title="juego.titulo">{{ juego.titulo }}</h2>
              </div>

              <p class="text-gray-400 text-sm line-clamp-2 mb-4 h-10 overflow-hidden">
                {{ juego.descripcion }}
              </p>

              <div class="mt-auto">
                <button @click="verDetalles(juego.id)"
                  class="w-full border border-pink-500 text-pink-500 py-2 rounded hover:bg-pink-500 hover:text-white transition-all font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                  :aria-label="'Ver detalles de ' + juego.titulo">
                  Ver Detalles
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="juegos.length === 0 && !cargandoLista"
          class="text-center py-20 bg-gray-800/80 rounded-xl border border-dashed border-gray-600">
          <p class="text-gray-300 text-xl">No se encontraron juegos que coincidan.</p>
          <button @click="busquedaGlobal = ''"
            class="mt-4 text-pink-400 hover:text-pink-300 underline font-semibold focus:outline-none focus:ring-2 focus:ring-pink-500 rounded px-2">
            Ver todos los juegos
          </button>
        </div>
      </div>

      <div v-if="juegoSeleccionado" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog"
        aria-modal="true" aria-labelledby="modal-game-title">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="juegoSeleccionado = null"></div>

        <div
          class="bg-gray-800 w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl z-10 border border-gray-600 relative animate-fade-in-up">

          <div class="relative h-64 md:h-80">
            <img :src="getImageUrl(juegoSeleccionado.imagen)" :alt="'Imagen de cabecera de ' + juegoSeleccionado.titulo"
              class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-linear-to-t from-gray-900 to-transparent"></div>

            <button @click="juegoSeleccionado = null"
              class="absolute top-4 right-4 bg-black/50 hover:bg-pink-500 text-white w-10 h-10 rounded-full flex items-center justify-center transition-colors text-xl font-bold focus:outline-none focus:ring-2 focus:ring-white"
              aria-label="Cerrar detalles">
              ✕
            </button>
          </div>

          <div class="p-8 space-y-6">
            <h2 id="modal-game-title" class="text-3xl font-bold text-pink-500 border-b border-gray-700 pb-4">{{
              juegoSeleccionado.titulo }}</h2>

            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">GÉNERO</span>
                <span class="text-white text-lg">{{ juegoSeleccionado.genero }}</span>
              </div>

              <div v-if="juegoSeleccionado.plataformas?.length">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">PLATAFORMAS</span>
                <div class="flex flex-wrap gap-2">
                  <span v-for="(plat, index) in juegoSeleccionado.plataformas" :key="index"
                    class="bg-pink-600/20 text-pink-400 border border-pink-500/30 px-3 py-1 rounded-full text-sm font-medium">
                    {{ plat }}
                  </span>
                </div>
              </div>
            </div>

            <div>
              <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">DESCRIPCIÓN</span>
              <p class="text-gray-300 italic leading-relaxed">{{ juegoSeleccionado.descripcion }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>