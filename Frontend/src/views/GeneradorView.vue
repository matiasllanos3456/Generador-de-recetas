<script setup>
import { onMounted } from 'vue';
import { useIngredientesStore } from '../stores/ingredientes';
import { useRouter } from 'vue-router';
import Navbar from '../components/Navbar.vue';
import ResumenNutricional from '../components/ResumenNutricional.vue';
import DetalleReceta from '../components/DetalleReceta.vue';

const router = useRouter();
const ingredienteStore = useIngredientesStore();

onMounted(async () => {
  // Si por accidente recargan la página y el carrito quedó vacío, los echamos atrás
  if (ingredienteStore.ingredientesSeleccionados.length === 0) {
    router.push({ name: 'inicio' });
    return;
  }

  // 🔥 Aquí es donde realmente se cocina la receta con PHP
  const exito = await ingredienteStore.generarReceta();
  
  if (!exito) {
    console.error("Error al procesar la IA:", ingredienteStore.errorMensaje);
  }
});
</script>

<template>
    <!-- Se utilizará la barra de navegación,
     el ResumenNutricional.vue y
     DetallesReceta.vue -->
  <div class="generador-page">
    <Navbar />

    <div class="generador-content">
      <div v-if="!ingredienteStore.recetaGenerada.titulo" class="error-no-recipe animate-fade">
        <span class="cookie-icon">🍪</span>
        <h3>¡No hay ninguna receta en el horno!</h3>
        <p>Parece que recargaste la página o no has seleccionado ingredientes todavía.</p>
        <button @click="volverAlInicio" class="btn-back-home">⬅️ Volver a la Alacena</button>
      </div>

      <div v-else class="recipe-grid animate-slide-up">
        
        <div class="col-main">
          <DetalleReceta />
        </div>

        <div class="col-sidebar">
          <ResumenNutricional />
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
.generador-page { min-height: 100vh; background-color: #f8fafc; }
.generador-content { padding: 30px; max-width: 1240px; margin: 0 auto; }

.recipe-grid {
  display: grid;
  grid-template-columns: 2fr 1fr; /* 2 partes para la receta, 1 parte para los macros */
  gap: 30px;
  align-items: start;
}

/* Estado de error por si entran directo a la URL */
.error-no-recipe {
  text-align: center; background: white; padding: 60px 40px; border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02); max-width: 500px; margin: 50px auto;
}
.cookie-icon { font-size: 50px; display: block; margin-bottom: 15px; }
.error-no-recipe h3 { color: #064e3b; margin: 0 0 10px 0; font-size: 20px; }
.error-no-recipe p { color: #64748b; font-size: 14px; margin-bottom: 25px; }
.btn-back-home { background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
.btn-back-home:hover { background: #059669; }

/* Animaciones fluidas */
.animate-slide-up { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

@media (max-width: 900px) { .recipe-grid { grid-template-columns: 1fr; } }
</style>