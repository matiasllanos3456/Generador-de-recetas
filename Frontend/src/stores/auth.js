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
import axios from 'axios';

export const useAuthStore = defineStore('usuario', () => {
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
//    Se manejaran las solicitudes de manera asincrona para evitar
//    que se realentice la aplicación
   const login = async (email, password) => {
        cargando.value = true;
        errorMensaje.value = null;
        try {
            const respuesta = await axios.post(
                'http://localhost/GeneradorDeRecetas/Backend/Process/LogIn.php',
            {email, password}, // Datos que recibirá el php
            {withCredentials: true});
            if (respuesta.data.success){
                // Si la respuesta es exitosa guardamos al usuario en formato json
                usuario.value = {
                    nombre: respuesta.data.nombre,
                    peso: respuesta.data.peso,
                    altura: respuesta.data.altura
                };
                estaAutenticado.value = true;
                return true;
            } else {
                // Si el PHP dijo success: false (contraseña incorrecta, etc.)
                errorMensaje.value = respuesta.data.message || "Credenciales incorrectas";
                return false;
            }
        } catch (error) {
            // Si el PHP dijo success: false (contraseña incorrecta, etc.)
            console.error("Error al obtener los datos: ", error);
            errorMensaje.value = "Error de conexión con el servidor";
            return false;
        } finally {
            cargando.value = false;
        }
   }
//    Este metodo permite recuperar la sesion del usuario en caso de que recargue la pagina
   const check = async () => {
        cargando.value = true;
        errorMensaje.value = null;
        try {
            const respuesta = await axios.get(
                "http://localhost/GeneradorDeRecetas/Backend/Check/CheckSession.php",
                {withCredentials: true}
            // Si el usuario está en sesion se obtendrá su id, peso y altura
            );
            if (respuesta.data.success){
                usuario.value = {
                    nombre: respuesta.data.nombre,
                    peso: respuesta.data.peso,
                    altura: respuesta.data.altura
                };
                estaAutenticado.value = true;
                return true;
            } else {
                usuario.value = null;
                estaAutenticado.value = false;
                return false;
            }
        } catch (error) {
            console.error("Error al chequear la sesion: ", error);
            errorMensaje.value = "Error de sesión";
            usuario.value = null;
            return false;
        } finally {
            cargando.value = false;
        }
   }
   
   const registro = async (nombre, email, password, peso, altura) => {
        cargando.value = true;
        errorMensaje.value = null;
        try {
            const respuesta = await axios.post(
                'http://localhost/GeneradorDeRecetas/Backend/Process/Registro.php',
                {nombre, email, password, peso, altura}, // Datos que recibirá el php
                {withCredentials: true});
            if (respuesta.data.success){
                // Si la respuesta es exitosa procedemos a iniciar sesion con el metodo antes definido
                const exitologin = await login(email, password);
                estaAutenticado.value = true;
                // Deberia retornar true
                return exitologin;
            } else {
                errorMensaje.value = respuesta.data.message || "Error al registrar el usuario";
                return false;
            }
        } catch (error) {
            console.error("Error en el registro: ", error);
            errorMensaje.value = "Error de conexión al registrarse";
            return false;
        } finally {
            cargando.value = false;
        }
   }
//    Retornar los estados y metodos para usarlos desde la vista
   return {
    usuario,
    estaAutenticado,
    cargando,
    errorMensaje,
    login,
    check,
    registro
   }
})