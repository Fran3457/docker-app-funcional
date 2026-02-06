<script setup>
import { ref, onMounted, watch } from 'vue';
import Swal from 'sweetalert2';

const eventos = ref([]);
const paginaActual = ref(1);
const hayMasDatos = ref(true);
const cargando = ref(false);

const eventoSeleccionado = ref(null);

const filtroTipo = ref('todos');
const filtroFecha = ref('');
const filtroSoloLibres = ref(false);

// Asegúrate de que esta URL coincide con tu backend (XAMPP o Docker)
const API_URL = 'http://localhost:8080/backend';

// --- FUNCIÓN CARGAR EVENTOS ---
const cargarEventos = async () => {
  cargando.value = true;
  try {
    const params = new URLSearchParams({
      page: paginaActual.value,
      tipo: filtroTipo.value,
      fecha: filtroFecha.value,
      soloLibres: filtroSoloLibres.value ? '1' : '0'
    });

    const response = await fetch(`${API_URL}/events?${params.toString()}`);
    if (!response.ok) throw new Error("Error en la petición");

    const data = await response.json();
    eventos.value = data;
    hayMasDatos.value = data.length >= 9;

  } catch (error) {
    console.error("Error cargando eventos:", error);
  } finally {
    cargando.value = false;
  }
};

watch([filtroTipo, filtroFecha, filtroSoloLibres], () => {
  paginaActual.value = 1;
  cargarEventos();
});

onMounted(() => {
  cargarEventos();
});

const siguientePagina = () => {
  if (hayMasDatos.value) {
    paginaActual.value++;
    cargarEventos();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const anteriorPagina = () => {
  if (paginaActual.value > 1) {
    paginaActual.value--;
    cargarEventos();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const getImageUrl = (imgName) => {
  // 1. Si es null o vacío, devolvemos la default del servidor
  if (!imgName) return `${API_URL}/img/default.png`;

  // 2. Si ya es una URL completa (http...), la respetamos
  if (imgName.startsWith('http')) return imgName;

  // 3. ESTRATEGIA PARA EL SERVIDOR:
  // Siempre buscamos en la carpeta backend/img.

  // A) Si el nombre TIENE extensión (ej: "foto.jpg"), lo pedimos tal cual
  if (imgName.includes('.')) {
    return `${API_URL}/img/${imgName}`;
  }

  // B) Si el nombre NO TIENE extensión (ej: "evento1" antiguo),
  // asumimos que es .png o .jpg. Probemos con .png por defecto (o .jpg si usabas más jpg).
  // Esto arregla las imágenes viejas de la base de datos.
  return `${API_URL}/img/${imgName}.png`;
};

const formatearFecha = (fechaStr) => {
  const opciones = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(fechaStr).toLocaleDateString('es-ES', opciones);
};

const limpiarFiltros = () => {
  filtroTipo.value = 'todos';
  filtroFecha.value = '';
  filtroSoloLibres.value = false;
};

const abrirModal = (evento) => {
  eventoSeleccionado.value = evento;
};

// --- INSCRIPCIÓN ---
const toggleInscripcion = async (evento) => {
  // ATENCIÓN: Ya no usamos localStorage para saber si está logueado, sino la cookie de sesión.
  // Pero para comprobación rápida en frontend, podemos mirar si el usuario tiene datos en el store o localStorage.
  // Si tu login guarda algo en localStorage, esto vale. Si no, quítalo.

  /* const usuarioLocal = localStorage.getItem('usuario_gamefest');
  if (!usuarioLocal) {
    Swal.fire({ icon: 'warning', title: 'Acceso denegado', text: 'Debes iniciar sesión...', background: '#1f2937', color: '#fff' });
    return;
  } 
  */

  try {
    const response = await fetch(`${API_URL}/events/${evento.id}/signup`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include' // IMPORTANTE: Esto envía la cookie de sesión PHP
    });

    const data = await response.json();

    if (response.ok) {
      Swal.fire({
        icon: 'success',
        title: '¡Inscrito!',
        text: 'Te has apuntado al evento correctamente.',
        background: '#1f2937', color: '#fff', confirmButtonColor: '#db2777',
        timer: 2000, showConfirmButton: false
      });
      evento.plazasLibres--;
    } else {
      // Si el error es 401, significa que no está logueado
      if (response.status === 401) {
        Swal.fire({
          icon: 'warning',
          title: 'Inicia sesión',
          text: 'Debes registrate para apuntarte.',
          background: '#1f2937', color: '#fff', confirmButtonColor: '#db2777'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Ups...',
          text: data.error || "Ocurrió un error al inscribirte",
          background: '#1f2937', color: '#fff', confirmButtonColor: '#db2777'
        });
      }
    }

  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo conectar con el servidor',
      background: '#1f2937', color: '#fff'
    });
  }
};
</script>

<template>
  <main
    class="grow relative bg-[url('/img/fondoview.png')] bg-cover bg-center bg-no-repeat flex items-center justify-center min-h-screen"
    aria-labelledby="events-title">
    <div class="absolute inset-0 bg-gray-900/30"></div>

    <div class="relative w-full text-white z-10 px-4 py-8">
      <div class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 mt-8 gap-4">
          <div>
            <h1 id="events-title" class="text-4xl font-bold mb-2 text-pink-500 drop-shadow-md">Agenda de Eventos</h1>
            <p class="text-gray-200">Explora los próximos eventos de GameFest</p>
          </div>
          <p class="text-gray-300 text-sm italic bg-black/30 px-3 py-1 rounded-full">Página {{ paginaActual }}</p>
        </div>

        <div
          class="bg-gray-800/90 backdrop-blur-sm p-6 rounded-xl border border-gray-700 mb-10 flex flex-wrap gap-6 items-end shadow-xl">
          <div class="flex flex-col gap-2 flex-1 min-w-50">
            <label for="filtro-tipo" class="text-xs font-bold uppercase text-pink-400 tracking-wider">Tipo de
              Evento</label>
            <select id="filtro-tipo" v-model="filtroTipo"
              class="bg-gray-900 border border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-pink-500 transition-colors text-white cursor-pointer">
              <option value="todos">Todos los tipos</option>
              <option value="Presentación">Presentación</option>
              <option value="Charla">Charla</option>
              <option value="Taller">Taller</option>
              <option value="Mesa Redonda">Mesa Redonda</option>
              <option value="Exhibición">Exhibición</option>
              <option value="Torneo">Torneo</option>
              <option value="Networking">Networking</option>
              <option value="Competición">Competición</option>
            </select>
          </div>

          <div class="flex flex-col gap-2 flex-1 min-w-50">
            <label for="filtro-fecha" class="text-xs font-bold uppercase text-pink-400 tracking-wider">Fecha</label>
            <input id="filtro-fecha" type="date" v-model="filtroFecha"
              class="bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 transition-colors text-white cursor-pointer">
          </div>

          <div class="flex items-center gap-3 mb-2.5">
            <input type="checkbox" id="libres" v-model="filtroSoloLibres"
              class="w-5 h-5 accent-pink-500 cursor-pointer focus:ring-2 focus:ring-pink-500 rounded">
            <label for="libres" class="cursor-pointer text-gray-300 select-none">Solo plazas libres</label>
          </div>

          <button @click="limpiarFiltros"
            class="mb-1 px-4 py-2 text-sm text-gray-400 hover:text-pink-400 transition-colors focus:outline-none focus:underline">
            Limpiar filtros
          </button>
        </div>

        <div v-if="cargando" class="flex justify-center items-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-pink-500"></div>
        </div>

        <div v-else-if="eventos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
          <div v-for="evento in eventos" :key="evento.id"
            class="bg-gray-800/95 rounded-xl overflow-hidden shadow-lg border border-gray-700 hover:border-pink-500 hover:-translate-y-1 transition-all duration-300 flex flex-col group">
            <div class="h-48 overflow-hidden relative cursor-pointer" @click="abrirModal(evento)">
              <img :src="getImageUrl(evento.imagen)" :alt="'Portada de ' + evento.titulo"
                @error="$event.target.src = `${API_URL}/img/default.png`"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
              <div
                class="absolute top-2 right-2 bg-black/70 px-3 py-1 rounded-full text-sm font-bold text-pink-400 shadow-sm">
                {{ evento.tipo }}
              </div>
              <div
                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span
                  class="text-white font-bold border border-white px-4 py-1 rounded-full bg-black/50 backdrop-blur-sm">Ver
                  Detalles</span>
              </div>
            </div>

            <div class="p-6 grow flex flex-col">
              <div class="mb-4">
                <h2 class="text-xl font-bold mb-2 line-clamp-1 hover:text-pink-400 cursor-pointer transition-colors"
                  :title="evento.titulo" @click="abrirModal(evento)">
                  {{ evento.titulo }}
                </h2>
                <div class="flex items-center gap-4 text-sm text-gray-300">
                  <span><i class="fas fa-calendar mr-1 text-pink-500" aria-hidden="true"></i> {{
                    formatearFecha(evento.fecha) }}</span>
                  <span><i class="fas fa-clock mr-1 text-pink-500" aria-hidden="true"></i> {{ evento.hora.substring(0,
                    5) }}</span>
                </div>
              </div>

              <p class="text-gray-400 text-sm line-clamp-2 mb-4 h-10 overflow-hidden">
                {{ evento.descripcion }}
              </p>

              <div class="mt-auto pt-4 border-t border-gray-700 flex justify-between items-center">
                <span class="text-sm">
                  Plazas:
                  <span class="font-bold"
                    :class="evento.plazasLibres > 0 ? 'text-(--color-success)' : 'text-(--color-danger)'"
                    :style="{ '--color-success': '#4ade80', '--color-danger': '#ef4444' }">
                    {{ evento.plazasLibres }}
                  </span>
                </span>

                <button @click="toggleInscripcion(evento)" :disabled="evento.plazasLibres === 0"
                  class="px-4 py-2 rounded text-sm font-bold transition-all transform active:scale-95 text-white shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-pink-500"
                  :class="evento.plazasLibres === 0 ? 'bg-gray-600 cursor-not-allowed opacity-70' : 'bg-pink-600 hover:bg-pink-700 hover:shadow-pink-500/30'"
                  :aria-label="evento.plazasLibres === 0 ? 'Evento agotado' : 'Inscribirse en ' + evento.titulo">
                  {{ evento.plazasLibres === 0 ? 'Agotado' : 'Inscribirse' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else
          class="text-center py-20 bg-gray-800/80 backdrop-blur-sm rounded-xl border border-dashed border-gray-600">
          <p class="text-gray-300 text-xl">No se encontraron eventos con esos filtros.</p>
          <button @click="limpiarFiltros"
            class="mt-4 text-pink-400 underline hover:text-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-500 rounded px-2">Mostrar
            todos los eventos</button>
        </div>

        <div class="flex justify-center gap-4 mt-8 mb-8">
          <button @click="anteriorPagina" :disabled="paginaActual === 1"
            class="px-6 py-2 rounded-full bg-gray-800 hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition border border-gray-600 focus:outline-none focus:ring-2 focus:ring-pink-500"
            aria-label="Ir a la página anterior">
            &larr; Anterior
          </button>
          <button @click="siguientePagina" :disabled="!hayMasDatos"
            class="px-6 py-2 rounded-full bg-pink-600 hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg hover:shadow-pink-500/20 focus:outline-none focus:ring-2 focus:ring-white"
            aria-label="Ir a la página siguiente">
            Siguiente &rarr;
          </button>
        </div>

      </div>

      <div v-if="eventoSeleccionado" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog"
        aria-modal="true" aria-labelledby="modal-evt-title">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="eventoSeleccionado = null"></div>

        <div
          class="bg-gray-800 w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl z-10 border border-gray-600 relative transition-all duration-300 transform scale-100 opacity-100">

          <button @click="eventoSeleccionado = null"
            class="absolute top-4 right-4 z-20 bg-black/60 hover:bg-pink-600 text-white w-10 h-10 rounded-full flex items-center justify-center transition-colors text-xl font-bold focus:outline-none focus:ring-2 focus:ring-white"
            aria-label="Cerrar detalles del evento">
            ✕
          </button>

          <div class="relative h-64 md:h-72">
            <img :src="getImageUrl(eventoSeleccionado.imagen)" :alt="'Imagen de cabecera: ' + eventoSeleccionado.titulo"
              @error="$event.target.src = `${API_URL}/img/default.png`" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-linear-to-t from-gray-900 to-transparent"></div>
            <div class="absolute bottom-4 left-6 right-6">
              <span class="inline-block bg-pink-600 text-white text-xs font-bold px-2 py-1 rounded mb-2 shadow-sm">{{
                eventoSeleccionado.tipo }}</span>
              <h2 id="modal-evt-title" class="text-3xl font-bold text-white drop-shadow-lg leading-tight">{{
                eventoSeleccionado.titulo }}</h2>
            </div>
          </div>

          <div class="p-8 space-y-6">

            <div class="flex flex-wrap gap-6 text-sm text-gray-300 border-b border-gray-700 pb-4">
              <div class="flex items-center gap-2">
                <i class="fas fa-calendar text-pink-500" aria-hidden="true"></i>
                <span class="font-semibold text-white">Fecha: {{ formatearFecha(eventoSeleccionado.fecha) }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="fas fa-clock text-pink-500" aria-hidden="true"></i>
                <span class="font-semibold text-white">Hora: {{ eventoSeleccionado.hora.substring(0, 5) }}</span>
              </div>
              <div class="flex items-center gap-2 ml-auto">
                <i class="fas fa-users text-pink-500" aria-hidden="true"></i>
                <span>Plazas libres: <span class="font-bold text-green-400">{{ eventoSeleccionado.plazasLibres
                }}</span></span>
              </div>
            </div>

            <div>
              <span class="text-pink-500 font-bold tracking-wider text-sm block mb-2">DESCRIPCIÓN COMPLETA</span>
              <p class="text-gray-300 leading-relaxed text-lg">{{ eventoSeleccionado.descripcion }}</p>
            </div>

            <div class="pt-4">
              <button @click="toggleInscripcion(eventoSeleccionado)" :disabled="eventoSeleccionado.plazasLibres === 0"
                class="w-full py-3 rounded-lg font-bold text-white shadow-lg transition-transform hover:scale-[1.02] flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-pink-500"
                :class="eventoSeleccionado.plazasLibres === 0 ? 'bg-gray-600 cursor-not-allowed opacity-80' : 'bg-linear-to-r from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500'">
                {{ eventoSeleccionado.plazasLibres === 0 ? 'AGOTADO' : 'INSCRIBIRSE AHORA' }}
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</template>

<style scoped></style>