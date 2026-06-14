<script setup>
import { ref } from 'vue';

// Recibimos el texto de búsqueda desde el padre para filtrar
defineProps({
  filtro: String
});

// Evento para avisar al padre cuando el usuario elija un ingrediente
const emit = defineEmits(['agregarIngrediente']);

// Datos de prueba (Mañana domingo los cambiaremos por el llamado a Axios)
const ingredientesDummy = ref([
  { id: 1, nombre: 'Pechuga de Pollo', categoria: 'Carnes', caloria: 165 },
  { id: 2, nombre: 'Palta (Aguacate)', categoria: 'Verduras', caloria: 160 },
  { id: 3, nombre: 'Arroz Integral', categoria: 'Carbohidratos', caloria: 111 },
  { id: 4, nombre: 'Huevo', categoria: 'Proteínas', caloria: 155 },
  { id: 5, nombre: 'Espinaca', categoria: 'Verduras', caloria: 23 },
]);
</script>

<template>
    <!-- En este componente aparecerán los ingredientes con su boton para agregar al carrito -->
  <div class="selector-container">
    <h3>Ingredientes Disponibles</h3>
    <div class="grid-ingredientes">
      <div 
        v-for="ing in ingredientesDummy.filter(i => i.nombre.toLowerCase().includes(filtro.toLowerCase()))" 
        :key="ing.id" 
        class="tarjeta-ingrediente"
      >
        <div class="info">
          <h4>{{ ing.nombre }}</h4>
          <span class="tag">{{ ing.categoria }}</span>
        </div>
        <button @click="emit('agregarIngrediente', ing)" class="btn-add">+</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.selector-container {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.02);
}
h3 { color: #064e3b; margin-top: 0; border-bottom: 2px solid #ecfdf5; padding-bottom: 10px; }
.grid-ingredientes { display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 15px; }
.tarjeta-ingrediente {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  transition: all 0.2s;
}
.tarjeta-ingrediente:hover { border-color: #10b981; background: #f0fdf4; }
h4 { margin: 0; color: #1e293b; }
.tag { font-size: 11px; background: #e2e8f0; padding: 2px 8px; border-radius: 20px; color: #475569; font-weight: bold; }
.btn-add { background: #10b981; color: white; border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
.btn-add:hover { background: #059669; }
</style>