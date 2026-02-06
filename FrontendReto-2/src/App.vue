<script setup>
import { ref, onMounted } from 'vue';
import { RouterView } from 'vue-router'
import Cabecera from '@/components/Cabecera.vue';
import PiePagina from './components/PiePagina.vue';
import AuthModal from '@/components/AuthModal.vue';

// Variable para mostrar u ocultar el modal
const mostrarModal = ref(false);

// Variable para guardar los datos del usuario
const usuario = ref(null);

onMounted(() => {
  const usuarioGuardado = localStorage.getItem('usuario_gamefest');
  if (usuarioGuardado) {
    usuario.value = JSON.parse(usuarioGuardado);
  }
});

const alIniciarSesion = (datos) => {
  usuario.value = {
    username: datos.username,
    role: datos.role
  };
  localStorage.setItem('usuario_gamefest', JSON.stringify(usuario.value));
};

const alCerrarSesion = async () => {
  try {
    // CORRECCIÓN 1: La URL tenía "backend" dos veces.
    // CORRECCIÓN 2: Añadimos 'credentials: include' (¡OBLIGATORIO!)
    await fetch('http://localhost:8080/backend/auth/logout', {
      method: 'POST',
      credentials: 'include' // <--- Sin esto, PHP no sabe qué sesión borrar
    });
  } catch (e) {
    console.error("Error al cerrar sesión en servidor", e);
  }

  // Limpieza local (Visual)
  usuario.value = null;
  localStorage.removeItem('usuario_gamefest');

};
</script>

<template>
  <div class="flex flex-col min-h-screen bg-(--color-bg) font-sans text-(--color-text)">

    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-(--color-primary) focus:text-white focus:px-4 focus:py-2 focus:rounded-md">
      Saltar al contenido principal
    </a>

    <Cabecera :usuario="usuario" @abrirModal="mostrarModal = true" @cerrarSesion="alCerrarSesion" />

    <main id="main-content" class="grow w-full">
      <RouterView />
    </main>

    <PiePagina :usuario="usuario" @abrirModal="mostrarModal = true" />

    <AuthModal v-if="mostrarModal" @close="mostrarModal = false" @login-success="alIniciarSesion" />
  </div>
</template>

<style>
/* CSS MODERNO: 
  Definición de variables CSS (Custom Properties) 
*/
:root {
  /* Colores base extraídos de Tailwind (Pink-600 y Gray-900) */
  --color-primary: #db2777;
  --color-primary-dark: #be185d;
  --color-bg: #111827;
  --color-bg-light: #1f2937;
  --color-text: #ffffff;
  --color-text-muted: #9ca3af;
}

/* Reset básico para asegurar robustez */
html {
  scroll-behavior: smooth;
  /* Microinteracción suave al navegar */
}

body {
  margin: 0;
  background-color: var(--color-bg);
}

/* CSS MODERNO: Uso de color-mix 
  Crea un color de selección semitransparente basado en el primario
*/
::selection {
  background-color: color-mix(in srgb, var(--color-primary), transparent 20%);
  color: white;
}

/* Estilos para la barra de desplazamiento (Scrollbar) moderna */
::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: var(--color-bg);
}

::-webkit-scrollbar-thumb {
  background: var(--color-bg-light);
  border-radius: 5px;
  border: 1px solid var(--color-bg);
}

::-webkit-scrollbar-thumb:hover {
  background: var(--color-primary);
}
</style>