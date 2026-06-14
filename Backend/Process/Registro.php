<?php
/*
 Este script recibirá por un metodo POST
 los datos del usario desde la interfaz (Thunder client),
 realizará las validaciones y creará la instancia en la BD 
*/
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
// 1. Cargar variables de entorno
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

// Conectar a la BD
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$user = getenv('DB_USER');
$name = getenv('DB_NAME');
$pass = getenv('DB_PASS');

// mysqli() permite establecer una conexion a la BD
// la cual será guardada en $mysqli que se utilizara
// para hacer las consultas
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = new mysqli($host, $user, $pass, $name, $port);
if ($mysqli->connect_errno) {
    echo json_encode(["sucess" => false, "error" => "Error de conexión: " . $mysqli->connect_error]);
    exit;
}
// utf8mb4 permite el rederizar caracteres especiales
$mysqli->set_charset("utf8mb4");

// Obtener los datos del usuario por metodo POST
// Campos obligatorios
$datosRecibidos = json_decode(file_get_contents("php://input"), true);

$nombre = isset($datosRecibidos['nombre']) ? trim($datosRecibidos['nombre']) : "";
$email = isset($datosRecibidos['email']) ? trim($datosRecibidos['email']) : "";
$password = isset($datosRecibidos['password']) ? trim($datosRecibidos['password']) : "";

if(strlen($password) < 8) {
    echo json_encode(["error" => "La clave debe tener al menos 8 caracteres"]);
    exit;
}

// Campos opcionales
$altura = (!empty($datosRecibidos['altura'])) ? (float)$datosRecibidos['altura'] : 1.7;
$peso   = (!empty($datosRecibidos['peso']))   ? (float)$datosRecibidos['peso']   : 58;

// Validaciones
if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(["error" => "Todos los campos obligatorios deben estar completos."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "El formato del correo electrónico no es válido."]);
    exit;
}

// Validar si el email ya existe
$checkEmail = "SELECT id_usuario FROM Usuario WHERE correo = ?";
$stmtEmail = $mysqli->prepare($checkEmail);
$stmtEmail->bind_param("s", $email);
$stmtEmail->execute();
$stmtEmail->store_result();
// Si ya existe el email se cancela la query
if($stmtEmail->num_rows > 0) {
    echo json_encode(["error" => "Este correo ya se encuentra registrado"]);
    $stmtEmail->close();
    $mysqli->close();
    exit;
} 
$stmtEmail->close();
// Encriptar la contraseña
$passwordEncriptada = password_hash($password, PASSWORD_BCRYPT);

// Guardar en la base de datos
$queryInsert = "INSERT INTO Usuario (nombre, correo, peso, altura, contrasena) VALUES (?, ?, ?, ?, ?)";
$stmtInsert = $mysqli->prepare($queryInsert);
$stmtInsert->bind_param("ssdds", $nombre, $email, $peso, $altura, $passwordEncriptada);

if($stmtInsert->execute()) {
    echo json_encode([
        "success" => true,
        "mensaje" => "Usuario registrado correctamente",
    ], JSON_UNESCAPED_UNICODE);
    exit;
} else {
    echo json_encode([
        "error" => "No se pudo registrar al usuario"
    ]);
}
$stmtInsert->close();
$mysqli->close();
?>