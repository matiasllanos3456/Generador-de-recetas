<!-- Este script devolverá la informacion especifica de los ingredientes pedidos por el usuario -->
<!-- En principio la informacion que se manejará
en este script vendrá de la interfaz de Vue--> 
 <!-- El script de abajo devuelve la información del ingrediente
  por lo que funciona como api, recibe una solicitud http y devuelve json -->
<!-- -------------------------------------- -->

<!-- Primero se tomará en cuenta la BD para la busqueda de ingredientes,
    si no se encuentra entonces se consultará a la API de USDA como segunda opcion-->
<?php
// Cabeceras obligatorias para que Thunder Client y Vue lo lean como JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// 1. Cargar las variables de entorno manualmente (como lo planeamos)
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

// Obtener la API Key de la USDA de forma segura
$usda_key = getenv('USDA_API_KEY');

if (!$usda_key) {
    echo json_encode(["error" => "Falta la configuración de las API Keys en el archivo .env"]);
    exit;
}
// Obtener el ingrediente desde la interfaz (Thuder client en este caso)
$ingrediente = isset($_GET['search']) ? trim($_GET['search']) : 'milk';

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

if ($ingredientesEncontrados) {
    echo json_encode($ingredientesEncontrados, JSON_UNESCAPED_UNICODE);
    exit;
}
// -----------------------------------------------------
// Si $ingredientesEncontrados queda vacío (no se encontro nada en la BD)
// se pasara a buscar por las API de USDA


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
$productosLimpios = [];

// 2.3) Mapear los nutrientes específicos de la USDA
if (isset($dataOriginal['foods']) && is_array($dataOriginal['foods'])) {
    foreach ($dataOriginal['foods'] as $food) {
        
        // La USDA guarda los nutrientes en una lista, tenemos que buscar el ID de cada uno
        $calorias = 0; $proteinas = 0; $carbohidratos = 0; $grasas = 0; $azucares = 0;
        
        if (isset($food['foodNutrients'])) {
            foreach ($food['foodNutrients'] as $nutrient) {
                // IDs estándar de la USDA para macros por cada 100g
                switch ($nutrient['nutrientId']) {
                    case 1008: $calorias = $nutrient['value']; break; // Energy kcal
                    case 1003: $proteinas = $nutrient['value']; break; // Protein
                    case 1005: $carbohidratos = $nutrient['value']; break; // Carbohydrate
                    case 1004: $grasas = $nutrient['value']; break; // Total lipid (fat)
                    case 2000: $azucares = $nutrient['value']; break; // Sugars, total
                }
            }
        }

        $productosLimpios[] = [
            "nombre"        => $food['description'], // Nombre oficial del alimento
            "calorias"      => round($calorias, 1),
            "proteinas"     => round($proteinas, 2),
            "carbohidratos" => round($carbohidratos, 2),
            "grasas"        => round($grasas, 2),
            "azucares"      => round($azucares, 2)
        ];
    }
}

if (empty($productosLimpios)) {
    echo json_encode(["mensaje" => "No se encontraron alimentos para esa palabra en la USDA."]);
    exit;
}

echo json_encode($productosLimpios, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>