<?php
/*
 Si un ingrediente no tiene id, por que viene de la
 api de USDA primero se ingresará el ingrediente en la tabla
 Ingrediente y posteriormente se creará una instancia en la tabla intermedia IngredienteReceta-->
 El id del usuario debe quedar guardado en una variable $_SESSION 
*/
// Se obtendrán los siguientes datos
/*
{
    ingredientes: [(aquí se incluiran los gramos)],
    macronutrientes (de la receta): {
        calorias: 0,
        proteinas: 0,
        .....
    },
    pasos: "(Se incluirán los ingredientes con su respectiva cantidad, el paso a paso, posibles variaciones y consejos nutricionales)",
    tiempoPreparacion: 0.0,
    nombre: ""
}
*/
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}
function cargarEnv($ruta) {
    if (!file_exists($ruta)) return;
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0) continue;
        list($nombre, $valor) = explode('=', $linea, 2);
        putenv(trim($nombre) . "=" . trim($valor));
    }
}
cargarEnv(__DIR__ . '/../.env');

// Contectar a la BD
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$user = getenv('DB_USER');
$name = getenv('DB_NAME');
$pass = getenv('DB_PASS');

session_start();

// Validamos si el usuario realmente inició sesión antes de hacer algo
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "message" => "No autorizado. Inicia sesión primero."]);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];
// Dejar el id_usuario como 1 para las pruebas y comentar la validacion de arriba
// $id_usuario = 1;

// -------------------------------------------------------------------------------------------
// Primero, obtener los datos de la interfaz
$jsonRecibido = file_get_contents("php://input");
$data = json_decode($jsonRecibido, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Datos de receta inválidos."]);
    exit;
}

// Extraemos los datos principales, se guardarán como longtext
$nombre_receta = $data['nombre']; 
$tiempo = (float)$data['tiempodepreparacion']; // float
$contenido_pasos = [
    "ingredientes_completos_ia" => $data['ingredientes_ia'], // Los ingredientes con gramos de la IA en formato JSON
    "procedimiento" => $data['pasos'],   // Los pasos vienen en un array                     
    "variaciones" => $data['posiblesVariaciones'] ?? '', // String
    "consejo" => $data['consejoNutricional'] ?? '' // String
];
$atributo_pasos_final = json_encode($contenido_pasos, JSON_UNESCAPED_UNICODE); // Informacion completa de la receta

// macronutrientes de la receta (texto plano)
$macronutrientes = json_encode($data['macronutrientes']); 

// Se recibirán tambien los ingredientes seleccionados previamente por el usuario guardados en un estado global.
// Estos si se almacenarán en la BD ya que se tiene la información completa de estos
$ingredientes_usuario = $data['ingredientes_usuario']; // Array

// -------------------------------------------------------------------------------------------
// Segundo, registrar los ingredientes que no tienen id (los de la api de USDA, si es que hay). Los ingredientes estarán guardados previamente en un estado de Pinia.

$lista_ids_productos = [];
// Los ingredientes guardados en $ingredientes_usuario vendrán con su id
// excepto los ingredientes de la USDA que tendrán que ser insertados en la BD

// Query de insercion en caso de que el ingrediente no exista, posteriormente tomamos su id 
$query_insercion = "INSERT INTO ingrediente (nombre, calorias, proteinas, carbohidratos, grasas_saturadas,
                    grasas_monoinsaturadas, azucares, categoria) VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)";

$mysqli = new mysqli($host, $user, $pass, $name, $port);
if ($mysqli->connect_errno) {
    echo json_encode(["success" => false, "message" => "Error de conexión: " . $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");
$stmt_insertar = $mysqli->prepare($query_insercion);

foreach($ingredientes_usuario as $ingrediente){
    // Si el ingrediente no tiene un entero de id debe tener null
    if(!is_null($ingrediente['id'])){
        $lista_ids_productos[] = (int)$ingrediente['id'];
    } else {
        // El ingrediente debe ser insertado
        $nombre_revisado = trim($ingrediente['nombre']);
        $calorias = (float)($ingrediente['calorias']);
        $proteinas = (float)($ingrediente['proteinas']);
        $carbohidratos = (float)($ingrediente['carbohidratos']);
        $grasas_mon = (float)($ingrediente['grasas_monoinsaturadas']);
        $grasas_sat = (float)($ingrediente['grasas_saturadas']);
        $azucares = (float)($ingrediente['azucares']);
        $categoria = ($ingrediente['categoria']);

        $stmt_insertar->bind_param("sdddddds", $nombre_revisado, $calorias,
                                    $proteinas, $carbohidratos, $grasas_sat,
                                    $grasas_mon, $azucares, $categoria);
        if(!$stmt_insertar->execute()){
            echo json_encode([
                "success" => false,
                "message" => "No se pudo registrar el ingrediente " . $nombre_revisado
            ]);
            exit;
        } else {
            // El indice del ingrediente recien guardado queda en una propiedad de mysqli
            $id_recien_insertado = $mysqli->insert_id;
            $lista_ids_productos[] = (int)$id_recien_insertado;
        }
    }
}
$stmt_insertar->close();
// -------------------------------------------------------------------------------------------
// Tercero, insertar la receta(id_usuario, nombre, tiempo_preparacion, macronutrientes(longtext), pasos(longtext))
$query_receta = "INSERT INTO receta(id_usuario, nombre, tiempo_preparacion, pasos, macronutrientes) VALUES(?, ?, ?, ?, ?)";
$stmt_receta = $mysqli->prepare($query_receta);
$stmt_receta->bind_param("isdss", $id_usuario, $nombre_receta, $tiempo, $atributo_pasos_final, $macronutrientes);
if(!$stmt_receta->execute()){
    echo json_encode([
        "success" => false,
        "message" => "No se pudo guardar la receta de " . $nombre_receta
    ]);
    exit;
} else {
    // La receta se guardó exitosamente, ahora tomamos su id
    $id_receta_actual = $mysqli->insert_id;
}
$stmt_receta->close();

// -------------------------------------------------------------------------------------------
// Cuarto, obtener el id de la receta recien creada
$id_receta_int = (int)$id_receta_actual;

// -------------------------------------------------------------------------------------------
// Quinto, insertar los ingredientes en la tabla intermedia IngredientePorReceta(id_receta, id_ingrediente)
// Variables: $lista_ids_productos y $id_receta_int

$query_intermedia = "INSERT INTO ingredienteporreceta(id_ingrediente, id_receta) VALUES (?, ?)";
$stmt_intermedio = $mysqli->prepare($query_intermedia);
// Recorremos los ids de los ingredientes
foreach($lista_ids_productos as $id_prod){
    $stmt_intermedio->bind_param("ii", $id_prod, $id_receta_int);
    // Se notifiacará si hay un error al insertar
    if(!$stmt_intermedio->execute()){
        echo json_encode([
            "success" => false,
            "message" => "No se pudo guardar el indrediente de id " . $id_prod
    ]);
    exit;
}
}
$stmt_intermedio->close();
$mysqli->close();

// Respuesta de exito para el frontend
echo json_encode([
    "success" => true,
    "message" => "¡La receta '" . $nombre_receta . "' se ha guardado perfectamente en tu perfil!",
    "id_receta" => $id_receta_int
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>