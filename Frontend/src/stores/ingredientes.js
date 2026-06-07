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
Los actions serán: AgregarIngrediente, EliminarIngrediente, 
                LimpiarIngredientes(borra todos los ingredientes una vez que el usuario guarda la receta)

Los getters serán: ObtenerIngredientes
Se utilizará axios en vez de fetch para comuncarse con el backend
por la facilidad para detectar errores
*/
import {defineStore} from 'pinia';