import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
// librerias para utilizar vuedevtools
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
})
