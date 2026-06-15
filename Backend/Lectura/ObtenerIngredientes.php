<?php
/*
 Aqui se retornarán ingredientes comunes
 guardados en la base de datos para mostrar en 
 la interfaz y no depender tanto de la api de USDA 
*/
// Permitir que el frontend de desarrollo pueda comunicarse correctamente con este archivo
header("Access-Control-Allow-Origin: http://localhost:5173");
// Permitir el paso de cookies y sesiones compartidas
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Responder inmediatamente a las peticiones pre-flight (OPTIONS) que hace Axios
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}
// 1. Cargar variables de entorno (.env)
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

// 2. Conectar a MySQL
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$name = getenv('DB_NAME');
$port = getenv('DB_PORT');

$mysqli = new mysqli($host, $user, $pass, $name, $port);

if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Falló la conexión a MySQL: " . $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");

// 3. Consultar todos los ingredientes
$query = "SELECT id_ingrediente, nombre, categoria, calorias, proteinas, carbohidratos, grasas_saturadas, grasas_monoinsaturadas, azucares FROM Ingrediente ORDER BY categoria";
$resultado = $mysqli->query($query);
// $mysqli->query($query) devuelve un objeto mysqli_result, imposible de leer por si solo
// por lo que hay que procesarlo
$catálogo = [];
while ($row = $resultado->fetch_assoc()) {
    $catálogo[] = [
        // Se agrega en forma de objeto de JS al final del array $catalogo
        "id"            => (int)$row['id_ingrediente'],
        "nombre"        => $row['nombre'],
        "categoria"     => $row['categoria'],
        "calorias"      => round((float)$row['calorias'], 1),
        "proteinas"     => round((float)$row['proteinas'], 2),
        "carbohidratos" => round((float)$row['carbohidratos'], 2),
        "grasas_sat"    => round((float)$row['grasas_saturadas'], 2),
        "grasas_mono"   => round((float)$row['grasas_monoinsaturadas'], 2),
        "azucares"      => round((float)$row['azucares'], 2)
    ];
}
// En caso de que una query sea un INSERT, UPDATE o DELETE, esta retornará un booleano

$mysqli->close();

// Se envia el catálogo completo de 100 ingredientes a Vue.
// Lo hace convirtiendo el array en formato json, mas facil de trabajar
echo json_encode($catálogo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);