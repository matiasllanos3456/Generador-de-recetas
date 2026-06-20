<?php
/*
 Este script devolverá la informacion especifica de los ingredientes pedidos por el usuario 
 En principio la informacion que se manejará
 en este script vendrá de la interfaz de Vue
 El script de abajo devuelve la información del ingrediente
 por lo que funciona como api, recibe una solicitud http y devuelve json

 Este script recibira 2 valores por parte del usuario:
 el nombre del ingrediente y la confirmacion para mostrar ingredientes internacionales utilizando la api de USDA
*/


header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}
// Cargar las variables de entorno manualmente (como lo planeamos)
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

// Datos de la BD
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$name = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Obtener la API Key de la USDA 
$usda_key = getenv('USDA_API_KEY');

if (!$usda_key) {
    echo json_encode(["error" => "Falta la configuración de las API Keys en el archivo .env"]);
    exit;
}
// Obtener el ingrediente desde la interfaz (Thuder client en este caso)
$ingrediente = isset($_GET['search']) ? trim($_GET['search']) : 'milk';
// Obtener confirmacion para mostrar ingredientes internacionales (api de USDA)
$internacional = (isset($_GET['internacional']) && ($_GET['internacional'] === true || $_GET['internacional'] === 'true'));

// -----------------------------------------------------
// 1) Se consulta a la base de datos
$mysqli = new mysqli($host, $user, $pass, $name, $port);
$mysqli->set_charset("utf8mb4");

// Se realizará una consulta preparada para evitar inyecciones de sql
$query = "SELECT * FROM Ingrediente WHERE nombre LIKE ?";
$stmt = $mysqli->prepare($query);
$busqueda_termino = "%" . $ingrediente . "%";
$stmt->bind_param("s", $busqueda_termino);
$stmt->execute();
$resultado = $stmt->get_result();

$ingredientesEncontrados = [];
while ($row = $resultado->fetch_assoc()) {
    $ingredientesEncontrados[] = [
        "id"            => (int)$row['id_ingrediente'],
        "nombre"        => $row['nombre'],
        "categoria"     => $row['categoria'],
        "calorias"      => (float)$row['calorias'],
        "proteinas"     => (float)$row['proteinas'],
        "carbohidratos" => (float)$row['carbohidratos'],
        "grasas_sat"    => (float)$row['grasas_saturadas'],
        "grasas_mono"   => (float)$row['grasas_monoinsaturadas'],
        "azucares"      => (float)$row['azucares']
    ];
}
$stmt->close();


if($internacional){ // Si se habilitó el internacional se procederá a buscar con la api USDA
    // 2) Traducir y optimizar con Mymemory API
    $ingrediente_url_es = urlencode(strtolower($ingrediente));
    $traductor_url = "https://api.mymemory.translated.net/get?q={$ingrediente_url_es}&langpair=es|en";

    $ch_trans = curl_init();
    curl_setopt($ch_trans, CURLOPT_URL, $traductor_url);
    curl_setopt($ch_trans, CURLOPT_RETURNTRANSFER, true);
    $res_trans = curl_exec($ch_trans);
    curl_close($ch_trans);


    $json_trans = json_decode($res_trans, true);
    $ingrediente_en = isset($json_trans['responseData']['translatedText']) 
        ? trim($json_trans['responseData']['translatedText']) 
        : $ingrediente;

    // -----------------------------------------------------
    // 2.1) Extraer el ingrediente base
    $palabras = explode(' ', $ingrediente_en);
    $ingrediente_base = $palabras[0]; // Nos quedamos solo con "Chicken" o "Milk"

    // Quitamos comas o caracteres raros que hayan quedado
    $ingrediente_base = rtrim($ingrediente_base, ',');

    // Consultado a la USDA con el ingrediente en ingles
    $ingrediente_url = urlencode($ingrediente_base);
    // URL de la USDA filtrando por alimentos comunes (Survey Foods) y limitando a 5 resultados
    $url = "https://api.nal.usda.gov/fdc/v1/foods/search?api_key={$usda_key}&query={$ingrediente_url}&dataType=Survey%20(FNDDS)&pageSize=5";

    // 2.2) Armar el curl (similar al fetch en javascript)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if(curl_errno($ch)){
        echo json_encode(["error" => "Error de conexión con el servidor externo: " . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    $dataOriginal = json_decode($response, true);

    // 2.3) Mapear los nutrientes específicos de la USDA
    if (isset($dataOriginal['foods']) && is_array($dataOriginal['foods'])) {
        foreach ($dataOriginal['foods'] as $food) {
            // Filtro para mayor precision. No se aceptarán productos
            // que no contengan $ingrediente_base en su descripcion
            $nombreProductoClave = strtolower($food['description']);
            $palabraBuscadaClave = strtolower($ingrediente_base);

            // Si la palabra clave (ej: "fish") NO está dentro del nombre del producto, lo ignoramos y saltamos al siguiente
            if (strpos($nombreProductoClave, $palabraBuscadaClave) === false) {
                continue; 
            }

            // La USDA guarda los nutrientes en una lista, tenemos que buscar el ID de cada uno
            $calorias = 0; $proteinas = 0; $carbohidratos = 0; $grasas = 0; $azucares = 0;

            if (isset($food['foodNutrients'])) {
                foreach ($food['foodNutrients'] as $nutrient) {
                    // IDs estándar de la USDA para macros por cada 100g
                    switch ($nutrient['nutrientId']) {
                        case 1008: $calorias = $nutrient['value']; break;
                        case 1003: $proteinas = $nutrient['value']; break;
                        case 1005: $carbohidratos = $nutrient['value']; break; 
                        case 1004: $grasas = $nutrient['value']; break; 
                        case 2000: $azucares = $nutrient['value']; break; 
                    }
                }
            }

            $ingredientesEncontrados[] = [
                // Se le podría agregar un id temporal al producto
                        "id"            => null,
                        "nombre"        => $food['description'], 
                        "categoria"     => "Internacional",
                        "calorias"      => round($calorias, 1),
                        "proteinas"     => round($proteinas, 2),
                        "carbohidratos" => round($carbohidratos, 2),
                        "grasas_sat"    => round($grasas * 0.3, 2), // Estimación simple ya que la USDA separa lípidos totales
                        "grasas_mono"   => round($grasas * 0.5, 2),
                        "azucares"      => round($azucares, 2),
                        "origen"        => "usda"
                    ];
        }
    }

    if (empty($ingredientesEncontrados)) {
        echo json_encode([
            "message" => "No se encontraron alimentos para esa palabra",
            "success" => false
        ]);
        exit;
    }

}
echo json_encode($ingredientesEncontrados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>