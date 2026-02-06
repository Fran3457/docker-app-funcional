<script setup>
// No necesitamos lógica compleja aquí, es una vista de presentación.
</script>

<template>
  <div class="relative w-full h-full min-h-[80vh] flex items-center justify-center overflow-hidden">

    <div
      class="absolute inset-0 bg-[url('/img/fondo.png')] bg-cover bg-center bg-no-repeat z-0 transform hover:scale-105 transition-transform duration-[20s]"
      role="img" aria-label="Fondo decorativo de videojuegos"></div>

    <div class="absolute inset-0 bg-gray-900/30 z-10"></div>

    <div class="relative z-20 px-4 w-full flex justify-center">

      <div
        class="backdrop-blur-md p-10 rounded-2xl shadow-2xl max-w-lg text-center border border-white/10 animate-fade-in-up">

        <h1
          class="text-4xl md:text-5xl font-bold mb-6 text-transparent bg-clip-text bg-linear-to-r from-pink-400 to-purple-400 drop-shadow-sm">
          ¡Bienvenido a GameFest!
        </h1>

        <p class="text-gray-200 text-lg mb-8 leading-relaxed">
          Encuentra, organiza y participa en eventos de videojuegos y eSports.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">

          <RouterLink to="/juegos"
            class="btn-modern btn-purple px-8 py-3 rounded-xl font-bold text-white shadow-lg transition-all transform hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-400">
            Ver Juegos
          </RouterLink>

          <RouterLink to="/eventos"
            class="btn-modern btn-pink px-8 py-3 rounded-xl font-bold text-white shadow-lg transition-all transform hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-400">
            Ver Eventos
          </RouterLink>

        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
/* CSS MODERNO
  1. Definición de variables CSS locales.
  2. Uso de color-mix para generar estados hover dinámicos.
*/
.btn-modern {
  /* Definimos variables por defecto */
  --btn-color: #6366f1;
}

.btn-purple {
  --btn-color: #9333ea;
  /* Purple-600 */
  background-color: var(--btn-color);
}

.btn-pink {
  --btn-color: #db2777;
  /* Pink-600 */
  background-color: var(--btn-color);
}

/* REQUISITO: color-mix 
   Al hacer hover, mezclamos el color base con blanco (20%) para aclararlo,
   o con negro para oscurecerlo. Aquí lo aclaramos para efecto "brillo".
*/
.btn-modern:hover {
  background-color: color-mix(in srgb, var(--btn-color), white 15%);
  box-shadow: 0 10px 15px -3px color-mix(in srgb, var(--btn-color), transparent 50%);
}

/* MICROINTERACCIONES (30 pts - Parte visual) */
.animate-fade-in-up {
  animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  opacity: 0;
  transform: translateY(30px);
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>