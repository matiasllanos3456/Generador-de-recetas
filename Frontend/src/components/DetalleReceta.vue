<script setup>
import { ref } from 'vue';
import { useIngredientesStore } from '../stores/ingredientes';
import { useRouter } from 'vue-router';

const ingredienteStore = useIngredientesStore();
const router = useRouter();
const guardadoConExito = ref(false);

const ejecutarGuardado = async () => {
  const respuestaOk = await ingredienteStore.guardarReceta();
  if (respuestaOk) {
    guardadoConExito.value = true;
    
    // Dejamos el aviso de éxito en pantalla 2.5 segundos y devolvemos al usuario a la alacena
    setTimeout(() => {
      // Limpiamos la receta del store para dejar el sistema listo para una nueva consulta
      ingredienteStore.recetaGenerada.titulo = '';
      ingredienteStore.ingredientesSeleccionados = [];
      router.push({ name: 'inicio' });
    }, 2500);
  }
};

const volverAtras = () => {
  router.push({ name: 'inicio' });
};
</script>

<template>
  <div class="recipe-card">
    <header class="recipe-header">
      <div class="title-container">
        <span class="main-badge">✨ Menú Personalizado IA</span>
        <h2>{{ ingredienteStore.recetaGenerada.titulo }}</h2>
      </div>
      <div class="time-meta">
        <span class="icon">⏱️</span>
        <div class="time-text">
          <span class="label">Preparación</span>
          <span class="value">{{ ingredienteStore.recetaGenerada.tiempo_preparacion }} mins</span>
        </div>
      </div>
    </header>

    <section class="body-section">
      <h3>📋 Estructura de porciones e ingredientes</h3>
      <p class="section-intro">La IA ha calculado las siguientes proporciones óptimas basadas en tu perfil físico:</p>
      <div class="ingredients-chip-grid">
        <div 
          v-for="(cantidad, nombreIng) in ingredienteStore.recetaGenerada.ingredientes_ia" 
          :key="nombreIng"
          class="ingredient-chip"
        >
          <span class="ing-name">{{ nombreIng }}</span>
          <span class="ing-qty">{{ cantidad }}</span>
        </div>
      </div>
    </section>

    <section class="body-section">
      <h3>👨‍🍳 Modo de Preparación</h3>
      <div class="steps-timeline">
        <div 
          v-for="(paso, index) in ingredienteStore.recetaGenerada.instrucciones" 
          :key="index"
          class="step-item"
        >
          <div class="step-number">{{ index + 1 }}</div>
          <div class="step-content">
            <p>{{ paso }}</p>
          </div>
        </div>
      </div>
    </section>

    <section v-if="ingredienteStore.recetaGenerada.posibles_variaciones" class="body-section variations-section">
      <h3>🔄 Alternativas y Cambios</h3>
      <p class="variations-text">{{ ingredienteStore.recetaGenerada.posibles_variaciones }}</p>
    </section>

    <footer class="recipe-actions-footer">
      <div v-if="ingredienteStore.errorMensaje" class="error-banner">
        ⚠️ {{ ingredienteStore.errorMensaje }}
      </div>

      <div class="btn-group">
        <button @click="volverAtras" class="btn-outline" :disabled="ingredienteStore.cargando">
          🍳 Cambiar Ingredientes
        </button>

        <button 
          @click="ejecutarGuardado" 
          class="btn-submit"
          :class="{ 'btn-success': guardadoConExito }"
          :disabled="ingredienteStore.cargando || guardadoConExito"
        >
          <span v-if="ingredienteStore.cargando">⏳ Guardando en tu perfil de recetas...</span>
          <span v-else-if="guardadoConExito">🎉 ¡Receta Archivada con Éxito!</span>
          <span v-else>💾 Guardar Receta en mi Bitácora</span>
        </button>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.recipe-card { background: white; padding: 30px; border-radius: 16px; border: 1px solid #e2e8f0; }

.recipe-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 25px; }
.main-badge { display: inline-block; background: #e6f4ea; color: #137333; font-size: 11px; font-weight: bold; padding: 4px 10px; border-radius: 20px; margin-bottom: 8px; text-transform: uppercase; }
.recipe-header h2 { margin: 0; color: #064e3b; font-size: 24px; font-weight: 800; line-height: 1.2; }

.time-meta { display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 10px; min-width: 140px; }
.time-meta .icon { font-size: 22px; }
.time-text { display: flex; flex-direction: column; }
.time-text .label { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: bold; }
.time-text .value { font-size: 14px; font-weight: bold; color: #1e293b; }

.body-section { margin-bottom: 30px; }
.body-section h3 { font-size: 16px; color: #1e293b; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; font-weight: 700; }
.section-intro { color: #64748b; font-size: 14px; margin: 0 0 15px 0; }

/* Chips de Ingredientes */
.ingredients-chip-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.ingredient-chip { display: flex; align-items: center; background: #f1f5f9; border-radius: 8px; padding: 8px 14px; font-size: 13px; border: 1px solid #e2e8f0; transition: all 0.2s; }
.ingredient-chip:hover { border-color: #10b981; background: #f0fdf4; }
.ing-name { font-weight: 600; color: #334155; text-transform: capitalize; margin-right: 8px; }
.ing-qty { color: #059669; font-weight: bold; background: white; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0; }

/* Línea de tiempo para los pasos */
.steps-timeline { display: flex; flex-direction: column; gap: 16px; position: relative; }
.step-item { display: flex; gap: 15px; position: relative; }
.step-number { width: 28px; height: 28px; background: #10b981; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 13px; flex-shrink: 0; box-shadow: 0 3px 6px rgba(16,185,129,0.2); }
.step-content { background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #f1f5f9; flex-grow: 1; }
.step-content p { margin: 0; font-size: 14px; color: #334155; line-height: 1.5; text-align: justify; }

.variations-section { background: #fffbeb; border: 1px solid #fef3c7; padding: 20px; border-radius: 12px; }
.variations-text { margin: 0; font-size: 13.5px; color: #92400e; line-height: 1.5; }

/* Botones finales */
.recipe-actions-footer { border-top: 2px solid #f1f5f9; padding-top: 20px; margin-top: 20px; }
.btn-group { display: flex; justify-content: space-between; gap: 15px; }

.btn-outline { background: white; border: 1px solid #cbd5e1; color: #475569; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-outline:hover:not(:disabled) { background: #f8fafc; border-color: #94a3b8; }

.btn-submit { background: #10b981; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.2s; flex-grow: 1; box-shadow: 0 4px 6px rgba(16,185,129,0.1); }
.btn-submit:hover:not(:disabled) { background: #059669; }
.btn-success { background: #059669 !important; animation: pulse 0.5s ease-in-out; }

.error-banner { background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 15px; text-align: center; }
button:disabled { opacity: 0.6; cursor: not-allowed; }

@keyframes pulse {
  0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); }
}
</style>