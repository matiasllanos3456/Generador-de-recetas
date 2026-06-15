<script setup>
import { onMounted } from 'vue';
import { useIngredientesStore } from '../stores/ingredientes';

const ingredienteStore = useIngredientesStore();

// Cuando el componente se monta en pantalla, cargamos todos los ingredientes locales de la BD
onMounted(() => {
  ingredienteStore.cargarIngredientes();
});
</script>

<template>
  <!-- Contenedor de los ingredientes -->
  <div class="selector-container">
    <h3>
      {{ ingredienteStore.textoBusqueda ? 'Resultados de Búsqueda' : 'Catálogo de Alimentos' }}
    </h3>

    <div v-if="ingredienteStore.errorMensaje" class="status-msg error">
      <span>⚠️ {{ ingredienteStore.errorMensaje }}</span>
    </div>

    <div v-if="ingredienteStore.cargando" class="status-msg loading">
      <span>⏳ Consultando despensa inteligene...</span>
    </div>

    <div v-else>
      <div v-if="ingredienteStore.textoBusqueda">
        <div v-if="ingredienteStore.resultadosBusqueda.length === 0" class="status-msg">
          <span>❌ No se encontraron ingredientes para "{{ ingredienteStore.textoBusqueda }}".</span>
        </div>
        
        <div v-else class="grid-ingredientes">
          <div v-for="ing in ingredienteStore.resultadosBusqueda" :key="ing.id || ing.nombre" class="tarjeta-ingrediente">
            <div class="info">
              <h4>{{ ing.nombre }}</h4>
              <span class="tag" :class="{ 'tag-usda': ing.categoria === 'Internacional' }">{{ ing.categoria }}</span>
              <small class="kcal">{{ ing.calorias }} kcal / 100g</small>
            </div>
            <button @click="ingredienteStore.agregarIngrediente(ing)" class="btn-add">+</button>
          </div>
        </div>
      </div>

      <div v-else>
        <div v-for="cat in [...new Set(ingredienteStore.listaIngredientes.map(i => i.categoria))]" :key="cat" class="bloque-categoria">
          <h4 class="titulo-categoria">{{ cat }}</h4>
          <div class="grid-ingredientes">
            <div v-for="ing in ingredienteStore.obtenerPorCategoria(cat)" :key="ing.id" class="tarjeta-ingrediente">
              <div class="info">
                <h4>{{ ing.nombre }}</h4>
                <small class="kcal">{{ ing.calorias }} kcal / 100g</small>
              </div>
              <button @click="ingredienteStore.agregarIngrediente(ing)" class="btn-add">+</button>
            </div>
          </div>
        </div>
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
h3 { 
  color: #064e3b; 
  margin-top: 0; 
  border-bottom: 2px solid #ecfdf5; 
  padding-bottom: 10px; 
}
.bloque-categoria { 
  margin-top: 25px; 
}
.titulo-categoria { 
  color: #059669; 
  font-size: 16px; 
  margin-bottom: 10px; 
  text-transform: uppercase; 
  letter-spacing: 0.5px; 
  border-left: 4px solid #10b981; 
  padding-left: 8px; 
}
.grid-ingredientes { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
  gap: 12px; 
}
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
.tarjeta-ingrediente:hover { 
  border-color: #10b981; 
  background: #f0fdf4; 
}
h4 { 
  margin: 0; 
  color: #1e293b; 
  font-size: 14px; 
  text-transform: capitalize; 
}
.info { 
  display: flex; 
  flex-direction: column; 
  gap: 4px; 
}
.tag { 
  font-size: 10px; 
  background: #e2e8f0; 
  padding: 2px 6px; 
  border-radius: 20px; 
  color: #475569; 
  font-weight: bold; 
  width: fit-content; 
}
.tag-usda { 
  background: #e0f2fe; 
  color: #0369a1; 
}
.kcal { 
  font-size: 11px; 
  color: #64748b; 
}
.btn-add { 
  background: #10b981; 
  color: white; 
  border: none; 
  width: 30px; 
  height: 30px; 
  border-radius: 50%; 
  font-size: 16px; 
  font-weight: bold; 
  cursor: pointer; 
  transition: background 0.2s; 
}
.btn-add:hover { 
  background: #059669; 
}
.status-msg { 
  text-align: center; 
  padding: 40px 20px; 
  color: #64748b; 
  font-size: 14px; 
}
.loading { 
  color: #10b981; 
  font-weight: bold; 
}
.error { 
  color: #ef4444; 
  background: #fef2f2; 
  border-radius: 8px; 
  padding: 15px; 
}
</style>