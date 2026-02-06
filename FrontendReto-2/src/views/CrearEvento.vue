<script setup>
import { ref, reactive } from 'vue';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

const router = useRouter();
const API_URL = 'http://localhost:8080/backend';

// UX: Estado de carga
const cargando = ref(false);

// Variable para almacenar el archivo seleccionado
const archivoSeleccionado = ref(null);

const form = reactive({
  titulo: '',
  tipo: 'Torneo',
  fecha: '',
  hora: '',
  plazasLibres: 10,
  descripcion: ''
});

// Función que detecta cuando el usuario elige un archivo
const procesarArchivo = (event) => {
  archivoSeleccionado.value = event.target.files[0];
};

const crearEvento = async () => {
  // Validación básica
  if (!form.titulo || !form.fecha || !form.hora) {
    Swal.fire('Error', 'Por favor rellena los campos obligatorios', 'error');
    return;
  }

  cargando.value = true;

  try {
    // CAMBIO IMPORTANTE: Usamos FormData para enviar archivos
    const formData = new FormData();
    formData.append('titulo', form.titulo);
    formData.append('tipo', form.tipo);
    formData.append('fecha', form.fecha);
    formData.append('hora', form.hora);
    formData.append('plazasLibres', form.plazasLibres);
    formData.append('descripcion', form.descripcion);

    // Solo añadimos la imagen si el usuario seleccionó una
    if (archivoSeleccionado.value) {
      formData.append('imagen', archivoSeleccionado.value);
    }

    const response = await fetch(`${API_URL}/events`, {
      method: 'POST',
      // NOTA: Al usar FormData, NO se pone el header 'Content-Type': 'application/json'
      // El navegador lo pone automáticamente como 'multipart/form-data'
      credentials: 'include',
      body: formData
    });

    const data = await response.json();

    if (response.ok) {
      await Swal.fire({
        icon: 'success',
        title: 'Evento Creado',
        text: 'Imagen subida y evento publicado correctamente',
        background: '#1f2937',
        color: '#fff',
        confirmButtonColor: '#db2777'
      });
      router.push('/eventos');
    } else {
      Swal.fire('Error', data.error || 'No se pudo crear el evento', 'error');
    }
  } catch (error) {
    console.error(error);
    Swal.fire('Error', 'Fallo de conexión', 'error');
  } finally {
    cargando.value = false;
  }
};
</script>

<template>
  <main
    class="grow relative bg-[url('/img/fondoview.png')] bg-cover bg-center bg-no-repeat flex items-center justify-center min-h-screen">

    <div class="absolute inset-0 bg-gray-900/40"></div>

    <div class="relative z-10 p-8 text-white w-full max-w-4xl">
      <div class="bg-gray-800/90 backdrop-blur-md p-8 rounded-xl shadow-2xl border border-gray-700 animate-fade-in-up">

        <h1 class="text-3xl font-bold text-pink-500 mb-6 border-b border-gray-700 pb-4 drop-shadow-sm">
          Crear Nuevo Evento
        </h1>

        <form @submit.prevent="crearEvento" class="space-y-6 form-modern">

          <div>
            <label for="titulo" class="block text-gray-300 mb-2 font-medium">Título del Evento</label>
            <input id="titulo" v-model="form.titulo" type="text" required placeholder="Ej: Torneo de Valorant 2025"
              class="w-full bg-gray-900/50 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="tipo" class="block text-gray-300 mb-2 font-medium">Tipo</label>
              <select id="tipo" v-model="form.tipo"
                class="w-full bg-gray-900/50 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all cursor-pointer">
                <option>Presentación</option>
                <option>Charla</option>
                <option>Taller</option>
                <option>Mesa Redonda</option>
                <option>Exhibición</option>
                <option>Torneo</option>
                <option>Networking</option>
                <option>Competición</option>
              </select>
            </div>
            <div>
              <label for="plazas" class="block text-gray-300 mb-2 font-medium">Plazas Disponibles</label>
              <input id="plazas" v-model="form.plazasLibres" type="number" min="1"
                class="w-full bg-gray-900/50 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="fecha" class="block text-gray-300 mb-2 font-medium">Fecha</label>
              <input id="fecha" v-model="form.fecha" type="date" required
                class="w-full bg-gray-900/50 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none text-white cursor-pointer">
            </div>
            <div>
              <label for="hora" class="block text-gray-300 mb-2 font-medium">Hora</label>
              <input id="hora" v-model="form.hora" type="time" required
                class="w-full bg-gray-900/50 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none text-white cursor-pointer">
            </div>
          </div>

          <div>
            <label for="imagen" class="block text-gray-300 mb-2 font-medium">Imagen del Evento</label>
            <input id="imagen" type="file" accept="image/*" @change="procesarArchivo"
              class="w-full bg-gray-900/50 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-600 file:text-white hover:file:bg-pink-700">
            <p class="text-xs text-gray-500 mt-1 italic">* Se aceptan JPG, PNG, WEBP. Máx 2MB recomendado.</p>
          </div>

          <div>
            <label for="descripcion" class="block text-gray-300 mb-2 font-medium">Descripción</label>
            <textarea id="descripcion" v-model="form.descripcion" rows="4"
              class="w-full bg-gray-900/50 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all resize-none"
              placeholder="Describe de qué trata el evento..."></textarea>
          </div>

          <button type="submit" :disabled="cargando"
            class="w-full btn-submit text-white font-bold py-3 rounded-lg transition-all transform hover:scale-[1.01] shadow-lg flex justify-center items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">
            <span v-if="cargando"
              class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
            <span>{{ cargando ? 'Publicando Evento...' : 'Publicar Evento' }}</span>
          </button>

        </form>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* REQUISITO: Variables CSS y color-mix */
.form-modern {
  --color-input-border: #4b5563;
  --color-focus: #db2777;
}

.btn-submit {
  --btn-bg: #db2777;
  background-color: var(--btn-bg);
}

.btn-submit:hover:not(:disabled) {
  background-color: color-mix(in srgb, var(--btn-bg), black 10%);
}

.animate-fade-in-up {
  animation: fadeInUp 0.4s ease-out;
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