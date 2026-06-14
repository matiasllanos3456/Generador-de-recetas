<template>
  <div class="register-page">
    <div class="register-card">
      
      <div class="register-header">
        <span class="logo-icon">🍏</span>
        <h2>Crea tu Perfil</h2>
        <p>Necesitamos estos datos para que la IA ajuste tus macros</p>
      </div>

      <form @submit.prevent="manejarRegistro" class="register-form">
        
        <div class="form-row">
          <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input id="nombre" v-model="nombre" type="text" placeholder="Tu nombre" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input id="email" v-model="email" type="email" placeholder="correo@ejemplo.com" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="password">Contraseña (minimo 8 caracteres)</label>
            <input id="password" v-model="password" type="password" placeholder="••••••••" required />
          </div>
        </div>
        <!-- Los siguientes parametros no son obligatorios -->
        <div class="form-row metrics">
          <div class="form-group">
            <label for="peso">Peso (kg) (Opcional)</label>
            <input id="peso" v-model.number="peso" type="number" step="0.1" placeholder="58"/>
          </div>
          <div class="form-group">
            <label for="altura">Altura (m) (Opcional)</label>
            <input id="altura" v-model.number="altura" type="number" step="0.01" placeholder="1.72"/>
          </div>
        </div>

        <div v-if="authStore.errorMensaje" class="error-alert">
          <p>{{ authStore.errorMensaje }}</p>
        </div>

        <button type="submit" class="btn-submit" :disabled="authStore.cargando">
          <span v-if="authStore.cargando">Creando cuenta...</span>
          <span v-else>Registrarse</span>
        </button>

      </form>

      <div class="register-footer">
        <p>¿Ya tienes cuenta? <RouterLink :to="{ name: 'login' }" class="link-action">Inicia Sesión</RouterLink></p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Campos del formulario
const nombre = ref('');
const email = ref('');
const password = ref('');
const peso = ref(null);
const altura = ref(null);

const manejarRegistro = async () => {
  const exito = await authStore.registro(
    nombre.value.trim(),
    email.value.trim(),
    password.value,
    peso.value,
    altura.value
  );

  if (exito) {
    router.push({ name: 'inicio' });
  }
};
</script>

<style scoped>
/* Reutilizamos y adaptamos la estética del Login */
.register-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background-color: #f8f9fa;
}

.register-card {
  background: white;
  padding: 2.5rem;
  border-radius: 16px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.05);
  width: 100%;
  max-width: 480px;
}

.register-header { text-align: center; margin-bottom: 2rem; }
.logo-icon { font-size: 2.5rem; }
.register-header h2 { color: #2b2b2b; margin: 0.5rem 0; font-weight: 700; }
.register-header p { color: #6c757d; font-size: 0.9rem; }

.register-form { display: flex; flex-direction: column; gap: 1.2rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; flex: 1; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #495057; }

.form-group input {
  padding: 0.75rem 1rem;
  border: 1px solid #e1e4e6;
  border-radius: 8px;
  background: #fcfcfc;
}

.form-group input:focus {
  outline: none;
  border-color: #4e9f3d;
  box-shadow: 0 0 0 3px rgba(78, 159, 61, 0.1);
}

.metrics { display: flex; gap: 1rem; }

.error-alert {
  background: #fdf2f2;
  border: 1px solid #f5c6cb;
  padding: 0.75rem;
  border-radius: 8px;
  color: #d9534f;
  font-size: 0.85rem;
  text-align: center;
}

.btn-submit {
  background: #4e9f3d;
  color: white;
  padding: 0.9rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}
.btn-submit:hover { background: #3e8e2d; }

.register-footer { text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #eee; }
.link-action { color: #4e9f3d; text-decoration: none; font-weight: 600; }
</style>