import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  // ESTA ES LA QUE FALTABA PARA EL SERVIDOR:
  base: '/',

  plugins: [
    vue(),
    vueDevTools(),
    tailwindcss(), // Mantenemos esto para que cargue los estilos
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})