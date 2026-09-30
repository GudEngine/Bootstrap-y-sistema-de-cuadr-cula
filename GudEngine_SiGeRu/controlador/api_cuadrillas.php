<?php
/* API RESTful para gestionar cuadrillas 
 * Permite operaciones de registro (POST),lectura (GET) y eliminación(DELETE)
 * Requiere conexión a la base de datos MySQL
 */

// Importa las dependencias necesarias
require_once 'config.php';
require_once 'cuadrilla.php';
require_once 'token.php';

$tokenObj = new Token($conn);

// Crea la instancia de la clase Cuadrilla
$cuadrillaObj = new Cuadrilla($conn);

// Obtiene el método de la solicitud HTTP y el endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';

// Establece el tipo de contenido de la respuesta (json)
header('Content-Type: application/json');
$token = $_COOKIE['token'] ?? '';

if ($tokenObj->validarToken($token)) {
    // El token es válido, validamos ahora el rol
    $usuario = $tokenObj->obtenerUsuarioPorToken($token);
    if ($usuario['usr_rol'] === 'administrador') {
        switch ($method) {
            case 'GET':
                if ($endpoint === '/cuadrillas') {
                    $cuadrillas = $cuadrillaObj->getAllCuadrillas();
                    echo json_encode($cuadrillas);
                    exit;
                }elseif (preg_match('/^\/cuadrillas\/(\d+)$/', $endpoint, $matches)) {
					// Obtiene un usuario por ID
					$cuadrillaId = $matches[1];
					$cuadrilla = $cuadrillaObj->getCuadrillaById($cuadrillaId);
					echo json_encode($cuadrilla);
                    }
                break;

            case 'POST':

                if ($endpoint === '/cuadrillas') {
                    // Recibe los datos  en formato JSON desde el formulario del frontend
                    $data = json_decode(file_get_contents('php://input'), true);

                    // Llama a la función de la clase, que valida y guarda
                    $cuadrillaObj->addCuadrilla($data);
                }/*else if($endpoint === '/modificar'){//SOLO podrá cambiarle el camión, los recolectores son determinantes en  cuadrilla_recolector
                   $data = json_decode(file_get_contents('php://input'), true);
                   $cuadrillaObj->modificarCuadrilla($data);
                   }*/

                break;
            case 'DELETE':
                if ($endpoint === '/cuadrillas') {
                    $data = json_decode(file_get_contents('php://input'), true);

                    if (isset($data['cuad_id'])) {
                        $cuadrillaObj->deleteCuadrillaById($data['cuad_id']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Identificador no proporcionado']);
                    }
                }
            default:
                http_response_code(405);
                echo json_encode(["mensaje" => "Método no permitido en este endpoint de cuadrilla."]);
                break;
        }
    } else {
        http_response_code(403);
        echo json_encode(["mensaje" => "⚠️ No tiene permisos para realizar esta acción."]);
    }
} else {
    // El token no es válido, invalidez es lo que 401 significa
    http_response_code(401);
    echo json_encode([
        "mensaje" => "⚠️ Sesión inválida o vencida."
    ]);
}