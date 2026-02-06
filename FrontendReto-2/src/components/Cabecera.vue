<script setup>
import { ref } from 'vue';

defineProps({
  usuario: Object
});

const emit = defineEmits(['abrirModal', 'cerrarSesion']);
const menuAbierto = ref(false);

const toggleMenu = () => {
  menuAbierto.value = !menuAbierto.value;
};
</script>

<template>
  <header role="banner" class="w-full py-4 px-8 text-white shadow-lg sticky top-0 z-40 header-gradient">

    <div class="container mx-auto flex flex-wrap items-center justify-between">

      <router-link to="/"
        class="group flex items-center transition-opacity hover:opacity-80 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-300"
        aria-label="Ir a inicio GameFest">
        <img src="/img/logo.png" alt="Logotipo GameFest" class="mr-4 h-10 w-auto object-contain" />
        <span class="text-xl font-bold tracking-wide">GameFest</span>
      </router-link>

      <button @click="toggleMenu" type="button"
        class="inline-flex items-center p-2 ml-3 text-sm text-pink-200 rounded-lg sm:hidden hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-pink-300"
        aria-controls="navbar-default" :aria-expanded="menuAbierto">
        <span class="sr-only">Abrir menú principal</span>

        <svg class="w-8 h-8" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg">
          <path v-if="!menuAbierto" fill-rule="evenodd"
            d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
            clip-rule="evenodd"></path>
          <path v-else fill-rule="evenodd"
            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
            clip-rule="evenodd"></path>
        </svg>
      </button>

      <nav class="w-full sm:block sm:w-auto transition-all duration-300 ease-in-out"
        :class="menuAbierto ? 'block' : 'hidden'" id="navbar-default" aria-label="Menú principal">

        <div
          class="flex flex-col sm:flex-row items-center gap-4 sm:gap-4 mt-4 sm:mt-0 font-semibold text-sm sm:text-base p-4 sm:p-0 bg-black/20 sm:bg-transparent rounded-lg sm:rounded-none border border-white/10 sm:border-none">

          <router-link to="/juegos"
            class="block py-2 pl-3 pr-4 w-full sm:w-auto text-center font-semibold hover:text-pink-300 transition-colors duration-300 rounded focus:outline-none focus-visible:text-pink-300 focus-visible:underline">
            Juegos
          </router-link>

          <router-link to="/eventos"
            class="block py-2 pl-3 pr-4 w-full sm:w-auto text-center font-semibold hover:text-pink-300 transition-colors duration-300 rounded focus:outline-none focus-visible:text-pink-300 focus-visible:underline">
            Eventos
          </router-link>

          <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto justify-center">

            <div v-if="usuario" class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">

              <router-link v-if="usuario.role === 'ADMIN'" to="/crear-evento"
                class="block py-2 w-full sm:w-auto text-center font-semibold hover:text-pink-300 transition-colors border-b-2 border-transparent hover:border-pink-300 rounded focus:outline-none focus-visible:text-pink-300">
                Crear Evento
              </router-link>

              <router-link to="/mis-eventos"
                class="block py-2 w-full sm:w-auto text-center font-semibold hover:text-pink-300 transition-colors border-b-2 border-transparent hover:border-pink-300 rounded focus:outline-none focus-visible:text-pink-300">
                Mis Eventos
              </router-link>

              <div
                class="flex items-center justify-center gap-2 bg-black/20 px-4 py-2 rounded-full border border-white/10 w-full sm:w-auto mt-2 sm:mt-0">
                <span class="text-pink-200 text-sm">
                  Hola, <span class="font-bold text-white">{{ usuario.username }}</span>
                </span>

                <button @click="$emit('cerrarSesion')"
                  class="ml-2 flex items-center justify-center bg-red-500/80 hover:bg-red-600 text-white w-6 h-6 rounded transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-300"
                  title="Cerrar sesión" aria-label="Cerrar sesión">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                  </svg>
                </button>
              </div>
            </div>

            <button v-else @click="$emit('abrirModal')"
              class="w-full sm:w-auto justify-center bg-white/10 px-4 py-2 rounded-full hover:bg-white/20 hover:text-pink-200 transition-all duration-300 cursor-pointer border border-transparent hover:border-pink-300/30 flex items-center gap-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-white mt-2 sm:mt-0">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
              </svg>
              Iniciar sesión
            </button>

          </div>

        </div>
      </nav>

    </div>
  </header>
</template>

<style scoped>
.header-gradient {
  --grad-start: #d63384;
  --grad-mid: #4b1d3f;
  --grad-end: #1a0b2e;
  background: linear-gradient(90deg, var(--grad-start) 0%, var(--grad-mid) 50%, var(--grad-end) 100%);
}
</style>