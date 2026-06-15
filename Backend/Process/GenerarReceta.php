<?php
/*
 Se utilizará la api de gemini para generar la receta
 según los ingredientes que se le pasaran desde la interfaz 

 El script recibirá un array de ingredientes con su nombre y macronutrientes en formato json.
 Ademas de la altura y el peso del usuario para recomendaciones
 basadas en su IMC.
 ejemplo:
 {
  "peso": 80.0,
  "altura": 1.75,
  "ingredientes": [
    { "nombre": "Pechuga de pollo", "calorias": 165, "proteinas": 31, "carbohidratos": 0, "grasas saturadas": 3.6, "grasas monoinsaturadas": 3.42, "azucares": 1.44 },
    { "nombre": "Arroz integral", "calorias": 111, "proteinas": 2.6, "carbohidratos": 23, "grasas saturadas": 0.9, "grasas monoinsaturadas": 4.12, "azucares": 0 }
  ]
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
// Cargar variables de entorno para obtener la Api key de groq
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

// API KEY DE GEMINI
// $apiKey = getenv('GEMINI_API_KEY');
// Como la api de gemini presenta errores se utilizará la api de groq
$apiKey = getenv('GROQ_API_KEY');

if (!$apiKey) {
    echo json_encode(["error" => "No se ha configurado la API Key de Groq en el archivo .env"]);
    exit;
}
// ---------------------------------------------------------------------------
// PROCESAR DATOS

// Capturar datos del usuario enviados por el método POST
$datosRecibidos = json_decode(file_get_contents("php://input"), true);

$ingredientes = isset($datosRecibidos['ingredientes']) ? $datosRecibidos['ingredientes'] : [];
$peso         = isset($datosRecibidos['peso']) ? (float)$datosRecibidos['peso'] : 0.0;
$altura       = isset($datosRecibidos['altura']) ? (float)$datosRecibidos['altura'] : 0.0;

if (empty($ingredientes)) {
    echo json_encode(["error" => "Debes seleccionar al menos un ingrediente."]);
    exit;
}

// Procesar macronutrientes de cada ingrediente
$ingredientesTextoParaIA = "";
$totalCalorias = 0;
$totalProteinas = 0;
$totalCarbos = 0;
$totalGrasasSat = 0;
$totalGrasasMon = 0;
$totalAzucares = 0;

foreach ($ingredientes as $ing) {
    $nombre = $ing['nombre'] ?? 'Ingrediente';
    $cal    = $ing['calorias'] ?? 0;
    $prot   = $ing['proteinas'] ?? 0;
    $carb   = $ing['carbohidratos'] ?? 0;
    $fat    = $ing['grasas saturadas'] ?? 0;
    $fat2    = $ing['grasas monoinsaturadas'] ?? 0;
    $sugar  = $ing['azucares'] ?? 0;

    // Creamos una línea descriptiva que Groq pueda entender correctamente
    $ingredientesTextoParaIA .= "- {$nombre} (Macros: {$cal} kcal, Prot: {$prot}g, Carbo: {$carb}g, Grasas saturadas: {$fat}g, Grasas monoinsaturadas: {$fat2}g Azucares: {$sugar})\n";
}

// Calcular el IMC del usuario para consultas mas especificas
$contextoSalud = "";
if ($peso > 0 && $altura > 0) {
    $imc = round($peso / ($altura * $altura), 2);
    $contextoSalud = "El usuario tiene un peso de {$peso}kg, una altura de {$altura}m y un IMC de {$imc}. Adapta las porciones y el enfoque nutricional a estos datos de salud de forma sutil.";
} else {
    $contextoSalud = "No hay datos corporales del usuario, genera una receta con porciones estándar para un adulto.";
}

// ---------------------------------------------------------------------------
// DISEÑO DEL PROMPT

// El prompt debe ser estricto para que devuelva solo un formato json especifico
$prompt = "Crea una receta saludable utilizando obligatoriamente algunos o todos estos ingredientes: {$ingredientesTextoParaIA}.
Cada ingrediente viene con sus macronutrientes por cada 100 gramos para que tu no inventes ninguno.
Si no se te proporcionan los gramos de un ingrediente tu decides cuantos gramos utilizar. {$contextoSalud}. 

Tu respuesta debe ser exclusivamente un objeto JSON válido, sin textos introductorios ni bloques de código markdown como ```json. Debe tener la siguiente estructura exacta:
{
  \"titulo\": \"Nombre creativo de la receta\",
  \"tiempoPreparacion\": \"45\",
  \"macronutrientesPorPorcion\": {
    \"calorias\": 477,
    \"proteinas\": 26,
    \"grasas saturadas\": 25,
    \"grasas monoinsaturadas\": 10,
    \"azucares\": 16,
    \"carbohidratos\": 40
  },
  \"ingredientesCantidad\": {
    \"ingrediente 1\": 100, 
    \"ingrediente 2\": 43
  },
  \"instrucciones\": [
    \"Paso 1...\", 
    \"Paso 2...\"
  ],
  \"posiblesVariaciones\": \"Si no tienes x ingrediente puedes reemplazarlo por ....\",
  \"consejoNutricional\": \"Breve explicación de por qué esta receta beneficia al usuario\"
}";
// ---------------------------------------------------------------------------
// PREPARAR LLAMADA A A Groq
$urlGroq = "https://api.groq.com/openai/v1/chat/completions";

// Se le dará la estructura al prompt para pasarlo a la api
$payload = [
    "model" => "llama-3.3-70b-versatile", 
    "messages" => [
        [
            "role" => "system",
            "content" => "Eres un chef y nutricionista experto. Tus respuestas deben ser única y exclusivamente objetos JSON válidos."
        ],
        [
            "role" => "user",
            "content" => $prompt // Tu prompt con los ingredientes y macros
        ]
    ],
    // Se obliga a Groq a devolver JSON puro sin romper el formato
    "response_format" => ["type" => "json_object"],
    "temperature" => 0.3
];

// Se realiza la llamada a la api
// CONFIGURACIÓN DE CONECTIVIDAD CRÍTICA:
// Forzamos la resolución de direcciones IP a IPv4 para evitar el error "Bad IPv6 address" de entornos locales
$ch = curl_init($urlGroq);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Tu salvavidas IPv4
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . trim($apiKey)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$respuestaGroq = curl_exec($ch);
curl_close($ch);

if (empty($respuestaGroq)) {
    echo json_encode(["error" => "No se pudo establecer conexión con el motor de Inteligencia Artificial de Groq. Detalle del error cURL: " . ($ch ? curl_error($ch) : "Límite de reintentos excedido.")]);
    exit;
}
// Procesar la respuesta  de groq para trabajarla mas facilmente desde el frontend
$resultadoDecodificado = json_decode($respuestaGroq, true);
// Probamos que funcione
// echo json_encode($resultadoDecodificado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
// Contenido del mensaje en json
$contenidoRecetaTexto = $resultadoDecodificado['choices'][0]['message']['content'] ?? '';

// Convertir a array asociativo de php
$recetaObjetoPHP = json_decode($contenidoRecetaTexto, true);

// Verificar que la conversion fue exitosa y retornar
if ($recetaObjetoPHP) {
    echo json_encode([
        "success" => true,
        "receta"  => $recetaObjetoPHP
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "success" => false,
        "message" => "La IA devolvió un formato ilegible. Inténtalo de nuevo."
    ]);
}
/*
Ejemplo de retorno:
{
  "success": true,
  "receta": {
    "titulo": "Carne Mechada con Papas al Horno",
    "tiempoPreparacion": "50",
    "macronutrientesPorPorcion": { ... },
    "ingredientesCantidad": { ... },
    "instrucciones": [ ... ],
    "posiblesVariaciones": "...",
    "consejoNutricional": "..."
  }
}
*/
exit;
?>