<!-- Se verifica si el usuario ya ha iniciado sesion,
 esto para evitar que al recargar la pagina se tenga que
 iniciar sesion denuevo-->
 <?php
// Permitir que Vue (en localhost:5173) lea este script y comparta cookies
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Iniciar o reanudar la sesión existente
session_start();

// Crear el arreglo de respuesta por defecto
$response = [
    "success" => false,
    "usuario" => null
];

// Preguntar si el ID del usuario ya está guardado en la sesión del servidor
if (isset($_SESSION['id_usuario'])) {
    $response["success"] = true;
    $response["usuario"] = [
        "id" => $_SESSION['id_usuario'],
        "peso" => $_SESSION['peso_usuario'],   // Trae el peso real o el valor por defecto
        "altura" => $_SESSION['altura_usuario'] // Trae la altura real o el valor por defecto
    ];
}

// 5. Escupir la respuesta en formato JSON para que Axios la reciba
echo json_encode($response);
exit;
 ?>