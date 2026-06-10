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
Los actions serán: AgregarIngrediente: Se utilizará un push en caso de que el ingrediente no haya sido seleccionado previamente
                  , QuitarIngrediente: Recibirá el id y el nombre del ingrediente a quitar, si no tiene id se eliminará por el nombre, 
                LimpiarIngredientes(borra todos los ingredientes una vez que el usuario guarda la receta)

Los getters serán: ObtenerIngredientes
Se utilizará axios en vez de fetch para comuncarse con el backend
por la facilidad para detectar errores
Este estado servirá tanto para generar la receta como para guardarla
Por otro lado. Se guardará la receta generada en GenerarReceta.php,
luego de que se genere la receta, al darle a un boton de guardar el 
contenido del estado pasará al script de GuardarReceta.php para guardarlo en la BD.
Solo se guardará una receta a la vez
Actions: - GenerarReceta(); - GuardarReceta(); - BorrarReceta()
*/
import {defineStore} from 'pinia';
import {ref, computed} from 'vue';
import axios from 'axios';

export const useIngredientesStore = defineStore('ingrdientes', () => {
    // Estados
    const ingredientesSeleccionados = ref([])
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

    })
    const cargando = ref(false)
    const errorMensaje = ref(null)

    // Getters
    // Devuelve los ingredientes para ser mostrados en la interfaz

    // Actions
    // GenerarReceta necesita el peso y la altura del usuario ademas de los ingredientes que fueron seleccionados
})