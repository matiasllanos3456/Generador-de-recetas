<script setup>
import HelloWorld from './components/HelloWorld.vue'
</script>

<template>
  <div class="app-container">
    <RouterView />
  </div>
</template>

<style>
/* Estilos globales para limpiar márgenes del navegador */
* {
  margin: 0;
  padding: 0;
  box-shadow: border-box;
}

body {
  background-color: #f8f9fa;
  margin: 0;
  font-family: system-ui, -apple-system, sans-serif;
}
</style>