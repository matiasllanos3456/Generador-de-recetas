<script setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '../stores/auth'; // Tu store de usuario
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

// Variables reactivas para el buscador y el switch
const busqueda = ref('');
const esInternacional = ref(false);

// Definimos eventos para avisarle a la Vista Principal cuando cambie algo
const emit = defineEmits(['actualizarBusqueda', 'cambiarModoApi']);

// Escuchamos cuando el usuario escribe en el buscador
watch(busqueda, (nuevoValor) => {
  emit('actualizarBusqueda', nuevoValor.trim());
});

// Escuchamos cuando el usuario activa/desactiva las búsquedas internacionales
watch(esInternacional, (nuevoValor) => {
  emit('cambiarModoApi', nuevoValor);
});

const manejarLogout = () => {
  authStore.logout();
  router.push({ name: 'login' });
};
</script>

<template>
  <nav class="navbar">
    <div class="nav-brand">
      <span class="logo-icon">🥑</span>
      <h2>RecetApp</h2>
    </div>

    <div class="nav-search-container">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input 
          type="text" 
          v-model="busqueda" 
          placeholder="Buscar ingredientes (ej: pollo, palta)..." 
          class="search-input"
        />
      </div>

      <label class="toggle-switch">
        <input type="checkbox" v-model="esInternacional" />
        <span class="slider"></span>
        <span class="toggle-label">
          {{ esInternacional ? '🌐 Internacional (USDA)' : '🏠 Local' }}
        </span>
      </label>
    </div>

    <div class="nav-user">
      <span class="user-name">Hola, <strong>{{ authStore.usuario?.nombre || 'Chef' }}</strong></span>
      <button @click="manejarLogout" class="btn-logout" title="Cerrar Sesión">
        Cerrar Sesión
      </button>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #ffffff;
  padding: 15px 30px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border-bottom: 3px solid #10b981; /* Tu verde esmeralda */
  font-family: 'Lato', sans-serif;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}
.nav-brand h2 {
  color: #064e3b;
  margin: 0;
  font-size: 22px;
}

.nav-search-container {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-grow: 0.5;
}

.search-box {
  position: relative;
  flex-grow: 1;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-input {
  width: 100%;
  padding: 10px 15px 10px 40px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: all 0.3s ease;
}

.search-input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Estilos del Toggle Switch */
.toggle-switch {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
}

.toggle-switch input {
  display: none;
}

.slider {
  width: 40px;
  height: 22px;
  background-color: #cbd5e1;
  border-radius: 20px;
  position: relative;
  transition: 0.3s;
}

.slider::before {
  content: "";
  width: 16px;
  height: 16px;
  background-color: white;
  border-radius: 50%;
  position: absolute;
  top: 3px;
  left: 3px;
  transition: 0.3s;
}

input:checked + .slider {
  background-color: #10b981;
}

input:checked + .slider::before {
  transform: translateX(18px);
}

.toggle-label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  min-width: 140px;
}

/* Sección de Usuario */
.nav-user {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-name {
  color: #334155;
  font-size: 14px;
}

.btn-logout {
  background-color: #ef4444; /* Rojo suave para alertar la salida */
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-logout:hover {
  background-color: #dc2626;
}
</style>