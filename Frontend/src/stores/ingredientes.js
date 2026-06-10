/* El estado almacenado será una lista con ingredientes
la estructura de los ingrdientes será:
{
    nombre: "",
    calorias: 0.0,
    proteinas: 0.0,
    carbohidratos: 0.0,
    grasas_saturadas: 0.0,
    grasas_monoinsaturadas: 0.0,
    azucares: 0.0,
    categoria: ""
}
Se utilizará axios en vez de fetch para comuncarse con el backend
por la facilidad para detectar errores
Este estado servirá tanto para generar la receta como para guardarla 
llamando a GenerarReceta.php y GuardarReceta.php respectivamente,
luego de que se genere la receta, al darle a un boton de guardar el 
contenido del estado pasará al script de GuardarReceta.php para guardarlo en la BD.
Solo se guardará una receta a la vez
Actions: - generarReceta(); - guardarReceta(); - cargarIngredientes()
*/
import {defineStore} from 'pinia';
import {ref, computed} from 'vue';
import axios from 'axios';
import { useAuthStore } from './auth';

export const useIngredientesStore = defineStore('ingrdientes', () => {
    // Informacion del usuario
    // Para acceder a los datos: authStore.usuario.altura, authStore.usuario.peso 
    const authStore = useAuthStore();
    // Estados
    const listaIngredientes = ref([]); // Aqui se guardarán todos los ingredientes de la BD
    const ingredientesSeleccionados = ref([]);
    const recetaGenerada = ref({ // Se define la forma que tendrá la receta
        titulo: '',
        ingredientes_ia: [],
        tiempo_preparacion: 0.0,
        instrucciones: [], // El paso a paso
        posibles_variaciones: '',
        consejo_nutricional: '',
        macronutrientes: {
            calorias: "",
            proteinas: "",
            carbohidratos: "",
            grasas_saturadas: "",
            grasas_monoinsaturadas: "",
            azucares: ""
        }

    });
    const cargando = ref(false);
    const errorMensaje = ref(null);

    // Getters
    // obtenerPorCategoria(), devuelve ingredientes de listaIngredientes dada la categoria seleccionada por el usuario
    const obtenerPorCategoria = computed(() => {
        return (categoriaSeleccionada) => {
            return listaIngredientes.value.filter(
                ingrediente => ingrediente.categoria === categoriaSeleccionada
            )
        };
     });
    /* Actions
    agregarIngrediente()
    quitarIngrediente()
    cargarIngredientes(), llamará a ObtenerIngredientes y llenará al estado de listaIngredientes con todos los ingredientes de la BD
    generarReceta() necesita el peso y la altura del usuario ademas de los ingredientes que fueron seleccionados
    guardarReceta(), tomará la receta ya generada y la guardara en la BD junto con los ingredientes seleccionados previamente
    */
    const agregarIngrediente = (ingrediente) => {
        // Primero se valida si el ingrediente no ha sido seleccionado
        const yaExiste = ingredientesSeleccionados.value.some(item => item.id === ingrediente.id);
        // Si no existe, lo empujamos al array de forma segura
        if (!yaExiste) {
            ingredientesSeleccionados.value.push(ingrediente);
        }
    }
    const quitarIngrediente = (idIngrediente) => {
        // Filtramos para que queden los demás ingredientes, sin el que se quiere borrar
        ingredientesSeleccionados.value = ingredientesSeleccionados.value.filter(
            item => item.id !== idIngrediente
        );
    }
    const cargarIngredientes = async () => {
        cargando.value = true;
        errorMensaje.value = null;
        try {
            const respuesta = await axios.get(
                'http://localhost/GeneradorDeRecetas/Backend/Lectura/ObtenerIngredientes.php',
                {withCredentials: true});
            // Si devuelve un array con ingredientes está bien
            if(Array.isArray(respuesta.data)){
                listaIngredientes.value = respuesta.data;
                return true
            } else {
                errorMensaje.value = "El formato de los ingredientes no es correcto";
                return false;
            }
        } catch (error) {
            errorMensaje.value = "No se pudieron obtener los ingredientes de la BD";
            return false;
        } finally {
            cargando.value = false;
        }
    }
    // Aqui se necesitarán el peso, la altura y los ingredientesSeleccionados
    const generarReceta = async () => {
        // Validaciones por si el usuario no está logueado
        if (!authStore.estaAutenticado || !authStore.usuario) {
            errorMensaje.value = "Debes iniciar sesión para generar una receta adaptada.";
            return false;
        }
        cargando.value = true;
        errorMensaje.value = null;
        try {
            const respuesta = await axios.post(
                'http://localhost/GeneradorDeRecetas/Backend/Process/GenerarReceta.php',
                {
                    peso: authStore.usuario.peso,
                    altura: authStore.usuario.altura,
                    ingredientes: ingredientesSeleccionados.value
                },
                {withCredentials: true}
                );
            if (respuesta.data.success){
                // Guardamos la receta en su estado
                recetaGenerada.value = {
                    titulo: respuesta.data.receta.titulo,
                    ingredientes_ia: respuesta.data.receta.ingredientesCantidad || [],
                    tiempo_preparacion: parseFloat(respuesta.data.receta.tiempoPreparacion) || 0.0,
                    instrucciones: respuesta.data.receta.instrucciones || [],
                    posibles_variaciones: respuesta.data.receta.posiblesVariaciones || '',
                    consejo_nutricional: respuesta.data.receta.consejoNutricional || '',
                    macronutrientes: {
                        calorias: parseInt(respuesta.data.receta.macronutrientesPorPorcion.calorias) || 0,
                        proteinas: parseFloat(respuesta.data.receta.macronutrientesPorPorcion.proteinas) || 0,
                        carbohidratos: parseFloat(respuesta.data.receta.macronutrientesPorPorcion.carbohidratos) || 0,
                        grasas_saturadas: parseFloat(respuesta.data.receta.macronutrientesPorPorcion["grasas saturadas"]) || 0,
                        grasas_monoinsaturadas: parseFloat(respuesta.data.receta.macronutrientesPorPorcion["grasas monoinsaturadas"]) || 0,
                        azucares: parseFloat(respuesta.data.receta.macronutrientesPorPorcion.azucares) || 0
                    }
                };
                return true;
            } else {
                errorMensaje.value = respuesta.data.message || "La IA no pudo generar la receta";
                return false;
            }
        } catch (error) {
            console.error("Error al conectar con el generador: ", error);
            errorMensaje.value = "El motor de recetas está tardando más de lo normal. Inténtalo de nuevo.";
            return false;
        } finally {
            cargando.value = false;
        }
    }

    return {
        listaIngredientes,
        ingredientesSeleccionados,
        cargando,
        errorMensaje,
        recetaGenerada,
        obtenerPorCategoria,
        cargarIngredientes,
        agregarIngrediente,
        cargarIngredientes,
        generarReceta
    }
});