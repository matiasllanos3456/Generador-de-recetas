<script setup>
import { useIngredientesStore } from '../stores/ingredientes';

const ingredienteStore = useIngredientesStore();
// Se obtienen los macronutrientes de la receta guardada
const macros = ingredienteStore.recetaGenerada.macronutrientes;
</script>

<template>
  <div class="nutrition-panel">
    <h3>📊 Análisis Nutricional</h3>
    <p class="subtitle">Valores estimados por porción</p>

    <div class="macros-vertical-grid">
      
      <div class="macro-row kcal-box">
        <div class="macro-info">
          <span class="macro-icon">🔥</span>
          <div class="text-group">
            <span class="macro-name">Energía Total</span>
            <small class="macro-unit">Calorías</small>
          </div>
        </div>
        <span class="macro-value font-highlight">{{ macros.calorias }} <small>kcal</small></span>
      </div>

      <div class="macro-row protein-box">
        <div class="macro-info">
          <span class="macro-icon">💪</span>
          <div class="text-group">
            <span class="macro-name">Proteínas</span>
            <small class="macro-unit">Construcción muscular</small>
          </div>
        </div>
        <span class="macro-value">{{ macros.proteinas }} <small>g</small></span>
      </div>

      <div class="macro-row carbo-box">
        <div class="macro-info">
          <span class="macro-icon">🌾</span>
          <div class="text-group">
            <span class="macro-name">Carbohidratos</span>
            <small class="macro-unit">Fuente de energía</small>
          </div>
        </div>
        <span class="macro-value">{{ macros.carbohidratos }} <small>g</small></span>
      </div>

      <div class="macro-row sugar-box">
        <div class="macro-info">
          <span class="macro-icon">🍭</span>
          <div class="text-group">
            <span class="macro-name">Azúcares</span>
            <small class="macro-unit">Carbohidratos simples</small>
          </div>
        </div>
        <span class="macro-value" :class="{ 'sugar-warning': macros.azucares > 15 }">
          {{ macros.azucares }} <small>g</small>
        </span>
      </div>

      <div class="grasas-details">
        <h5>🥑 Desglose de Lípidos:</h5>
        <div class="sub-macro">
          <span>Grasas Saturadas:</span>
          <strong>{{ macros.grasas_saturadas }}g</strong>
        </div>
        <div class="sub-macro">
          <span>Grasas Monoinsaturadas:</span>
          <strong>{{ macros.grasas_monoinsaturadas }}g</strong>
        </div>
      </div>

    </div>

    <div v-if="ingredienteStore.recetaGenerada.consejo_nutricional" class="ai-advice-box">
      <div class="advice-header">
        <span class="sparkle-icon">✨</span>
        <h4>Consejo Nutricional Adaptado</h4>
      </div>
      <p>{{ ingredienteStore.recetaGenerada.consejo_nutricional }}</p>
    </div>
  </div>
</template>

<style scoped>
.nutrition-panel { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01); position: sticky; top: 30px; }
h3 { color: #064e3b; margin: 0; font-size: 18px; }
.subtitle { color: #64748b; font-size: 13px; margin: 4px 0 20px 0; }

.macros-vertical-grid { display: flex; flex-direction: column; gap: 12px; }
.macro-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-radius: 10px; border: 1px solid #f1f5f9; background: #f8fafc; }

.macro-info { display: flex; align-items: center; gap: 12px; }
.macro-icon { font-size: 20px; }
.text-group { display: flex; flex-direction: column; }
.macro-name { font-size: 14px; font-weight: 700; color: #334155; }
.macro-unit { font-size: 11px; color: #94a3b8; }
.macro-value { font-size: 16px; font-weight: bold; color: #334155; }
.macro-value small { font-size: 12px; font-weight: normal; color: #64748b; }

/* Enfoques de colores sutiles */
.kcal-box { background: #fff7ed; border-color: #ffedd5; }
.kcal-box .macro-value { color: #ea580c; font-size: 18px; }
.protein-box { background: #f0fdf4; border-color: #dcfce7; }
.protein-box .macro-value { color: #16a34a; }
.carbo-box { background: #f0f9ff; border-color: #e0f2fe; }
.carbo-box .macro-value { color: #0284c7; }

.sugar-warning { color: #dc2626 !important; font-weight: 800; }

.grasas-details { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 5px; }
.grasas-details h5 { margin: 0 0 8px 0; color: #475569; font-size: 12px; text-transform: uppercase; }
.sub-macro { display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 4px; }
.sub-macro:last-child { margin-bottom: 0; }

.ai-advice-box { margin-top: 25px; background: #eff6ff; border: 1px solid #dbeafe; padding: 15px; border-radius: 12px; }
.advice-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.sparkle-icon { font-size: 16px; }
.advice-header h4 { margin: 0; color: #1e40af; font-size: 14px; font-weight: bold; }
.ai-advice-box p { margin: 0; font-size: 13px; color: #1e3a8a; line-height: 1.5; text-align: justify; }
</style>