<script setup>
defineProps({
  seleccionados: Array
});

const emit = defineEmits(['quitarIngrediente', 'generarReceta']);
</script>

<template>
    <!-- En este componente van a ir apareciendo los ingredientes seleccionados -->
  <div class="carrito-container">
    <h3>Mis Ingredientes ({{ seleccionados.length }})</h3>
    
    <div v-if="seleccionados.length === 0" class="vacio">
      <p>No has seleccionado ningún ingrediente aún.</p>
    </div>

    <div v-else class="lista-seleccionados">
      <div v-for="ing in seleccionados" :key="ing.id" class="item-seleccionado">
        <span>{{ ing.nombre }}</span>
        <button @click="emit('quitarIngrediente', ing.id)" class="btn-remove">✕</button>
      </div>

      <button @click="emit('generarReceta')" class="btn-generar">
        ✨ Generar Receta Inteligente
      </button>
    </div>
  </div>
</template>

<style scoped>
.carrito-container {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.02);
  position: sticky;
  top: 20px;
}
h3 { color: #064e3b; margin-top: 0; border-bottom: 2px solid #ecfdf5; padding-bottom: 10px; }
.vacio { text-align: center; color: #94a3b8; padding: 40px 0; }
.item-seleccionado {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
}
.btn-remove { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 14px; }
.btn-remove:hover { color: #b91c1c; font-weight: bold; }
.btn-generar {
  width: 100%;
  margin-top: 20px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  padding: 14px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
  transition: transform 0.2s;
}
.btn-generar:hover { transform: translateY(-2px); }
</style>