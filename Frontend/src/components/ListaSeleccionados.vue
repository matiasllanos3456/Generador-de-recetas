<script setup>
import { useIngredientesStore } from '../stores/ingredientes';
import { useRouter } from 'vue-router';

const router = useRouter();
const ingredienteStore = useIngredientesStore();

const procesarGeneracionReceta = async () => {
  if (ingredienteStore.ingredientesSeleccionados.length === 0) {
    alert("Debe seleciconar al menos un ingrediente.");
    return;
  }
  // La llamada al action de generarReceta se realizara en GeneradorView.vue
  // Se redirigirá a GeneradorView.vue
  router.push({ name: 'generador' }); 
};
</script>

<template>
  <div class="carrito-container">
    <h3>Ingredientes Seleccionados</h3>

    <div v-if="ingredienteStore.ingredientesSeleccionados.length === 0" class="carrito-vacio">
      <span class="basket-icon">🧺</span>
      <p>Tu canasta está vacía.</p>
      <small>Selecciona ingredientes del catálogo de la izquierda para comenzar.</small>
    </div>

    <div v-else class="carrito-lleno">
      <ul class="lista-seleccionados">
        <li 
          v-for="ing in ingredienteStore.ingredientesSeleccionados" 
          :key="ing.id || ing.nombre" 
          class="item-seleccionado"
        >
          <div class="item-info">
            <span class="item-name">{{ ing.nombre }}</span>
            <small class="item-kcal">{{ ing.calorias }} kcal</small>
          </div>
          <button @click="ingredienteStore.quitarIngrediente(ing.id)" class="btn-remove">
            🗑️
          </button>
        </li>
      </ul>

      <button 
        @click="procesarGeneracionReceta" 
        class="btn-generar"
        :disabled="ingredienteStore.cargando"
      >
        <span v-if="ingredienteStore.cargando">⏳ Procesando...</span>
        <span v-else>🚀 Generar Receta con IA</span>
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
  border: 1px solid #e2e8f0;
  position: sticky;
  top: 30px; /* Se queda flotando elegantemente al hacer scroll */
}

h3 { color: #064e3b; margin-top: 0; border-bottom: 2px solid #ecfdf5; padding-bottom: 10px; }

.carrito-vacio {
  text-align: center;
  padding: 40px 10px;
  color: #94a3b8;
}
.basket-icon { font-size: 40px; display: block; margin-bottom: 10px; }
.carrito-vacio p { margin: 5px 0; font-weight: bold; color: #64748b; }
.carrito-vacio small { font-size: 12px; }

.lista-seleccionados {
  list-style: none;
  padding: 0;
  margin: 15px 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 300px;
  overflow-y: auto;
}

.item-seleccionado {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  background: #f8fafc;
  border-radius: 6px;
  border: 1px solid #edf2f7;
}

.item-info { display: flex; flex-direction: column; }
.item-name { font-size: 13px; font-weight: 600; color: #334155; text-transform: capitalize; }
.item-kcal { font-size: 11px; color: #94a3b8; }

.btn-remove {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
  padding: 4px;
  border-radius: 4px;
  transition: background 0.2s;
}
.btn-remove:hover { background: #fee2e2; }

.btn-generar {
  width: 100%;
  background: #10b981;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 15px;
  cursor: pointer;
  margin-top: 15px;
  transition: background 0.2s, transform 0.1s;
  box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
}
.btn-generar:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
.btn-generar:active { transform: translateY(0); }
.btn-generar:disabled { opacity: 0.6; cursor: not-allowed; }
</style>