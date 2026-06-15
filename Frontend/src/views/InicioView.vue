<script setup>
import { ref } from 'vue';
import Navbar from '../components/Navbar.vue';
import SelectorIngredientes from '../components/SelectorIngredientes.vue';
import ListaSeleccionados from '../components/ListaSeleccionados.vue';

const textoFiltrado = ref('');
const modoInternacionalActivo = ref(false);
const ingredientesSeleccionados = ref([]);

// Lógica de interacción entre componentes
const alBuscarIngrediente = (texto) => { textoFiltrado.value = texto; };
const alCambiarModoApi = (estado) => { modoInternacionalActivo.value = estado; };

const agregarAlCarrito = (ingrediente) => {
  // Evitamos duplicados
  if (!ingredientesSeleccionados.value.some(i => i.id === ingrediente.id)) {
    ingredientesSeleccionados.value.push(ingrediente);
  }
};

const quitarDelCarrito = (id) => {
  ingredientesSeleccionados.value = ingredientesSeleccionados.value.filter(i => i.id !== id);
};

const procesarGeneracionReceta = () => {
  alert(`¡Enviando ${ingredientesSeleccionados.value.length} ingredientes al backend PHP para procesar con IA!`);
  // Aquí se hará la solicitud POST al action de generaReceta() del store del ingrediente
};
</script>

<template>
  <div class="dashboard-layout">
    <Navbar @actualizarBusqueda="alBuscarIngrediente" @cambiarModoApi="alCambiarModoApi" />

    <main class="dashboard-content">
      <div class="main-grid">
        
        <div class="col-izquierda">
          <SelectorIngredientes 
            :filtro="textoFiltrado" 
            @agregarIngrediente="agregarAlCarrito" 
          />
        </div>

        <div class="col-derecha">
          <ListaSeleccionados 
            :seleccionados="ingredientesSeleccionados" 
            @quitarIngrediente="quitarDelCarrito"
            @generarReceta="procesarGeneracionReceta"
          />
        </div>

      </div>
    </main>
  </div>
</template>

<style scoped>
.dashboard-layout { min-height: 100vh; background-color: #f8fafc; }
.dashboard-content { padding: 30px; max-width: 1200px; margin: 0 auto; }
.main-grid {
  display: grid;
  grid-template-columns: 2fr 1fr; /* 2 partes para buscar, 1 parte para el resumen */
  gap: 30px;
  margin-top: 10px;
}
@media (max-width: 768px) {
  .main-grid { grid-template-columns: 1fr; } /* Responsivo para móviles */
}
</style>