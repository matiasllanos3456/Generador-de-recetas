<template>
  <div class="login-page">
    <div class="login-card">
      
      <div class="login-header">
        <span class="logo-icon">🥗</span>
        <h2>¡Bienvenido de vuelta!</h2>
        <p>Ingresa tus credenciales para crear recetas inteligentes</p>
      </div>

      <form @submit.prevent="manejarSubmit" class="login-form">
        
        <div class="form-group">
          <label for="email">Correo Electrónico</label>
          <input 
            id="email"
            v-model="email" 
            type="email" 
            placeholder="ejemplo@correo.com" 
            required
            :disabled="authStore.cargando"
          />
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input 
            id="password"
            v-model="password" 
            type="password" 
            placeholder="••••••••" 
            required
            :disabled="authStore.cargando"
          />
        </div>

        <div v-if="authStore.errorMensaje" class="error-alert">
          <span class="alert-icon">⚠️</span>
          <p>{{ authStore.errorMensaje }}</p>
        </div>

        <button 
          type="submit" 
          class="btn-submit"
          :disabled="authStore.cargando"
        >
          <span v-if="authStore.cargando" class="spinner">Verificando...</span>
          <span v-else>Iniciar Sesión</span>
        </button>

      </form>

      <div class="login-footer">
        <p>¿No tienes una cuenta aún?</p>
        <RouterLink :to="{ name: 'registro' }" class="link-registro">
          Regístrate aquí
        </RouterLink>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth'; // Se necesitarán unos metodos y estados del usuario

// Referencias reactivas para capturar lo que el usuario escribe
const email = ref('');
const password = ref('');

// Instanciamos el router de navegación y el store de autenticación
const router = useRouter();
const authStore = useAuthStore();

const manejarSubmit = async () => {
  // Limpiamos espacios en blanco por seguridad
  const correoLimpio = email.value.trim();
  const passwordLimpia = password.value;

  if (!correoLimpio || !passwordLimpia) return;

  // Disparamos el action del store pasándole los parámetros requeridos
  const exito = await authStore.login(correoLimpio, passwordLimpia);

  if (exito) {
    // Si la BD devolvió success: true, el store cambió 'estaAutenticado' a true
    // y movemos al usuario a la vista principal '/' donde aparecen los ingredientes.
    router.push({ name: 'inicio' });
  } else {
    console.log("Error al iniciar sesion");
  }
};
</script>

<style scoped>
/* --- Variables de la paleta solicitada (Colores suaves y desaturados) --- */
:root {
  --blanco-fondo: #fcfcfc;
  --blanco-card: #ffffff;
  --verde-suave: #4e9f3d;      /* Verde oliva/hoja desaturado */
  --verde-hover: #3e8e2d;
  --rojo-suave: #d9534f;       /* Rojo coral suave para alertas */
  --rojo-fondo: #fdf2f2;       /* Fondo rosa/rojo ultra pálido para el banner */
  --texto-oscuro: #2b2b2b;
  --texto-gris: #6c757d;
  --borde-gris: #e1e4e6;
}

.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f8f9fa; /* Fondo general grisáceo/blanco muy limpio */
  font-family: system-ui, -apple-system, sans-serif;
}

.login-card {
  background-color: #ffffff;
  padding: 2.5rem;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); /* Sombra muy sutil */
  width: 100%;
  max-width: 420px;
  border: 1px solid #eef0f2;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-icon {
  font-size: 2.5rem;
}

.login-header h2 {
  color: #2b2b2b;
  font-size: 1.6rem;
  margin: 0.5rem 0 0.2rem 0;
  font-weight: 700;
}

.login-header p {
  color: #6c757d;
  font-size: 0.9rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #495057;
}

.form-group input {
  padding: 0.75rem 1rem;
  border: 1px solid #e1e4e6;
  border-radius: 8px;
  font-size: 0.95rem;
  background-color: #fcfcfc;
  transition: all 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #4e9f3d; /* Cambia a verde suave al hacer foco */
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(78, 159, 61, 0.15);
}

/* Alerta de Error con el Rojo Desaturado */
.error-alert {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  background-color: #fdf2f2; /* Fondo rojo pálido */
  border: 1px solid #f5c6cb;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  color: #d9534f; /* Texto rojo suave */
  font-size: 0.85rem;
}

.error-alert p {
  margin: 0;
  font-weight: 500;
}

/* Botón de envío con el Verde Predominante */
.btn-submit {
  background-color: #4e9f3d;
  color: white;
  padding: 0.85rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
  margin-top: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
  background-color: #3e8e2d;
}

.btn-submit:disabled {
  background-color: #9cbfa7; /* Verde grisáceo si está bloqueado */
  cursor: not-allowed;
  opacity: 0.8;
}

.login-footer {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9rem;
  color: #6c757d;
  border-top: 1px solid #f1f3f5;
  padding-top: 1.25rem;
}

.login-footer p {
  margin: 0 0 0.25rem 0;
}

.link-registro {
  color: #4e9f3d;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s ease;
}

.link-registro:hover {
  color: #3e8e2d;
  text-decoration: underline;
}
</style>