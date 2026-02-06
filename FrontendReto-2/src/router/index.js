// router/index.js
import { createRouter, createWebHistory } from 'vue-router'

//Importar las views
import HomeView from '@/views/HomeView.vue'
import JuegosView from '@/views/Juegos.vue'
import EventosView from '@/views/Eventos.vue'
import MisEventos from '@/views/MisEventos.vue'
import CrearEvento from '@/views/CrearEvento.vue'

//?Crear la vista pronto o ya existe¿
//import LoginView from '@/views/LoginView.vue'
//import { name } from '@vue/eslint-config-prettier/skip-formatting'

const routes = [
  {
  path: '/crear-evento',
  name: 'CrearEvento',
  component: CrearEvento
  },

  {
    path: '/mis-eventos',
    name: 'MisEventos',
    component: MisEventos
  },

  {
    path: '/',
    name: 'Home',
    component: HomeView
  },

  {
    path: '/juegos',
    name: 'Juegos',
    component: JuegosView
  },

  {
    path: '/eventos',
    name: 'Eventos',
    component: EventosView
  },

  {
    // Esto significa: "Cualquier cosa que no coincida con lo anterior"
    path: '/:pathMatch(.*)*',
    redirect: '/'  // Redirige al Home automáticamente //esto evita que metas num/letras en la url y te mantiene en lo anterior
    //junto con el .htaccess en /public protege la URL 
  }

]

const router = createRouter({
  // CAMBIO IMPORTANTE: Ponemos la ruta base con la tilde explícitamente
  // Esto hace que al recargar la página en el servidor no te dé error 404
  history: createWebHistory('/'),
  
  routes,

  // --- SOLUCIÓN AL SCROLL ---
  scrollBehavior(to, from, savedPosition) {
    // 1. Si el usuario le da al botón "Atrás" del navegador, volvemos donde estaba
    if (savedPosition) {
      return savedPosition
    }
    // 2. Si es una navegación normal (clicks en menú), vamos arriba del todo
    else {
      return { top: 0, behavior: 'smooth' }
    }
  }
})

export default router
