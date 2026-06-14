// Aquí se definirán las rutas de navegacion
import { createRouter, createWebHistory } from 'vue-router';
// Del store del usuario se llamará al estado de "check"
import { useAuthStore } from '../stores/auth';

// Importación de tus vistas (asumiendo estos nombres y carpetas)
import InicioView from '../views/InicioView.vue';
import LoginView from '../views/LoginView.vue';
import RegistroView from '../views/RegistroView.vue';
import GeneradorView from '../views/GeneradorView.vue';

// Habrán rutas que requieran haber iniciado sesión primero
const routes = [
    {
        path: '/',
        name: 'inicio',
        component: InicioView,
        meta: { requiereAuth: true } // Privada
    },
    {
        path: '/generador',
        name: 'generador',
        component: GeneradorView,
        meta: { requiereAuth: true } // Privada
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { soloInvitados: true } // Publica (Solo Invitados)
    },
    {
        path: '/registro',
        name: 'registro',
        component: RegistroView,
        meta: { soloInvitados: true } // Publica (Solo Invitados)
    },
    {
        // Ruta comodín: Si escriben cualquier cosa aleatoria en la URL, los manda al inicio
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
});

// El Guardián: Corre antes de entrar a cualquier ruta para verificar la autenticacion del usuario
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    // Aquí se llamará al estado check
    if (!authStore.estaAutenticado) {
        await authStore.check();
    }

    const autenticado = authStore.estaAutenticado;

    // Caso 1: La ruta requiere autenticación y el usuario NO está logueado
    if (to.meta.requiereAuth && !autenticado) {
        return next({ name: 'login' }); // Rebotado al login
    }

    // Caso 2: La ruta es para invitados (Login/Registro) y el usuario YA está logueado
    if (to.meta.soloInvitados && autenticado) {
        return next({ name: 'inicio' }); // Redirigido a la página principal
    }

    // Caso 3: Cumple los requisitos o es una ruta libre. ¡Adelante!
    next();
});

export default router;