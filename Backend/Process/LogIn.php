<!-- Como se está trabajando con Api rest no se utilizarán
 variables como $_SESSION para mantenerlo sin estado,
 esto hace al programa mas eficiente, escalable y seguro al no guardar
 en memoria la informacion de cientos y miles de usuarios en el mismo sitio-->
<!-- El frontend (Vue) será quien recuerde al usuario en sesion -->
<?php
// Se recibirá el correo y la contraseña para iniciar sesion
// Si se encuentra al usuario en la BD, el script devolverá los datos del usuario
// en formato json, ejemplo:
/*
{
"success": true,
"nombre": "Carlos",
"peso": 80.0,
"altura": 1.75
}.
*/
// Los cuales serán enviados al frontend y guardados
// en un estado global de pinia

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Cargar variables de entorno (subiendo un nivel para buscar el .env)
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

// Capturar los datos enviados por Vue(Thunder client)
$datosRecibidos = json_decode(file_get_contents("php://input"), true);

$email    = isset($datosRecibidos['email']) ? trim($datosRecibidos['email']) : "";
$password = isset($datosRecibidos['password']) ? trim($datosRecibidos['password']) : "";

if (empty($email) || empty($password)) {
    echo json_encode([
        "error" => "El correo y la contraseña son obligatorios"
    ]);
    exit;
}

// Contectar a la BD
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$user = getenv('DB_USER');
$name = getenv('DB_NAME');
$pass = getenv('DB_PASS');

$mysqli = new mysqli($host, $user, $pass, $name, $port);
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Error de conexión: " . $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");

// Decodificar la contraseña

// --------------------------------------------------------
// Busqueda en la BD
$query = "SELECT id_usuario, nombre, peso, altura, contrasena FROM Usuario WHERE correo = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

// Si no encuentra ninguna fila, el correo no existe
if ($resultado->num_rows === 0) {
    echo json_encode(["error" => "El correo electrónico o la contraseña son incorrectos."]);
    $stmt->close();
    $mysqli->close();
    exit;
}

// Extraemos los datos del usuario encontrado
$usuario = $resultado->fetch_assoc();
$stmt->close();

// ---------------------------------------------------------
// Verificar contraseña encriptada
// password_verify descifra el hash de la BD y lo compara con el password limpio
if (password_verify($password, $usuario['contrasena'])) {
    // Se guardará el id en una variable de sesion para utilizarla en otros scripts
    session_start();
    $_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
    $_SESSION['peso'] = $usuario['peso'] !== null ? (float)$usuario['peso'] : 58;
    $_SESSION['altura'] = $usuario['altura'] !== null ? (float)$usuario['altura'] : 1.7;
    // Se retornará la informacion del usuario en formato json
    echo json_encode([
        "success"    => true,
        "nombre"     => $usuario['nombre'],
        "peso"       => $usuario['peso'] !== null ? (float)$usuario['peso'] : 58,
        "altura"     => $usuario['altura'] !== null ? (float)$usuario['altura'] : 1.7
    ], JSON_UNESCAPED_UNICODE);

} else {
    // Nota de seguridad: Se da el mismo mensaje que si el correo no existiera 
    // para que un atacante no sepa si adivinó el correo válido.
    echo json_encode(["error" => "El correo electrónico o la contraseña son incorrectos."]);
}

$mysqli->close();
?>