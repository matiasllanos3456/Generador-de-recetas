/*
Se guardarán ciertos datos del usuario como el id, el peso y la altura
{
    id: 1,
    nombre: "Juan",
    peso: 40.9,
    altura: 1.64
}
Cuando se llame al LogIn.php se obtendran los siguientes datos: 
{"success":true,"id_usuario":5,"nombre":"Matias Perez","peso":59,"altura":1.82}
*/

import { defineStore } from "pinia";
import {ref, computed} from 'vue';

export const user = defineStore('usuario', () => {
    const usuario = ref(null)
    // Si el usuario se encontro en la BD esta constante pasará a true,
    // esto servirá para rederigir al usuario a la pagina principal
    const estaAutenticado = ref(false)
    
    // Estados de control
    const cargando = ref(false)
    const errorMensaje = ref(null)
    // Getters: ObtenerDatosIMC(): Devuelve la altura y el peso para ser utilizados en el storage de ingredientes.js

    /*
    Actions: - IniciarSesion(correo, contraseña)
    - Registrarse(nombre, correo, contraseña, peso, altura). Al registrarse se iniciará sesion automaticamente
    - ChequearSesion() 
    */
})