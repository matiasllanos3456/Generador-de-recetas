<?php
/*
 Se verifica si el usuario ya ha iniciado sesion,
 esto para evitar que al recargar la pagina se tenga que
 iniciar sesion denuevo
*/
// Permitir que Vue (en localhost:5173) lea este script y comparta cookies
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
session_start();

// Verifica que el id del usuario exista
if (isset($_SESSION['id_usuario'])) {
    echo json_encode([
        "success" => true,
        "id_usuario" => $_SESSION['id_usuario'],
        "nombre"  => $_SESSION['nombre'],
        "peso"    => $_SESSION['peso'],
        "altura"  => $_SESSION['altura']
    ], JSON_UNESCAPED_UNICODE);
    exit;
} else {
    echo json_encode(["success" => false]);
    exit;
}
// Ejemplo de la lista de retorno
/*
{
  "success": true,
  "id": 5,
  "peso": 59,
  "altura": 1.82
}
*/
exit;
 ?>