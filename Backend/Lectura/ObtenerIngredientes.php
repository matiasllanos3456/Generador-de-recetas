<!-- Aqui se retornarán ingredientes comunes
 guardados en la base de datos para mostrar en 
 la interfaz para no depender de la api de USDA -->
<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

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
    // fetch_assoc() lo que hace es tomar el primer elemento que está siendo apuntado por un "puntero" en la query (guardada en $resultado)
    // y la convierte en un array/diccionario de php mas facil de leer
    // cada atributo queda como una llave y se puede acceder a su valor mas facilmente
    // luego mueve el puntero a la siguiente fila para tomar el siguiente elemento, esto se debe usar en un bucle.
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

// 4. Enviar el catálogo completo de 100 ingredientes a Vue
// Lo hace convirtiendo el array en formato json, mas facil de trabajar
echo json_encode($catálogo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);