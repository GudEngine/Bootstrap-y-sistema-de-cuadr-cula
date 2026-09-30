<?php
/* API RESTful para gestionar centros de acopio
 * Por ahora permite operaciones  de registro y lectura
 * Requiere conexión a una base de datos MySQL
 */

// Importa las dependencias necesarias
require_once 'config.php';
require_once 'acopio.php'; // 
require_once 'token.php';

$tokenObj = new Token($conn);

// Crea la instancia de la clase centro de acopio
$acopioObj = new Centro_acopio($conn);

// Obtiene el método de la solicitud HTTP y el endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';
// Establece el tipo de contenido de la respuesta (json)
header('Content-Type: application/json');
$token = $_COOKIE['token'] ?? '';

// Procesa la solicitud según el método HTTP
switch ($method) {
    case 'GET':
        if ($endpoint === '/acopios') {
            // Obtiene todos los centros de acopio de la base de datos
            $acopios = $acopioObj->getAllAcopios();
            echo json_encode($acopios);
            exit;
        } elseif (preg_match('/^\/acopios\/(\d+)$/', $endpoint, $matches)) {
            // Obtiene un centro de acopio específico por ID pasándole el número qie manda la URL
            $acopioId = $matches[1];
            $acopio = $acopioObj->getAcopioById($acopioId);

            if ($acopio) {
                echo json_encode($acopio);
            } else {
                http_response_code(404);
                echo json_encode(["mensaje" => "⚠️ Centro de acopio no encontrado."]);
            }
            exit;
        }
        break;
    case 'POST':
        if ($tokenObj->validarToken($token)) {
            // El token es válido, validamos ahora el rol
            $usuario = $tokenObj->obtenerUsuarioPorToken($token);
            if ($usuario['usr_rol'] === 'administrador') {
                if ($endpoint === '/acopios') {
                    // Recibe los datos en formato JSON desde el frontend
                    $data = json_decode(file_get_contents('php://input'), true);

                    // Llama directamente a la función. Ella procesa, valida, 
                    // responde con su propio echo y corta la ejecución con exit;
                    $acopioObj->addCentroAcopio($data);
                } else if ($endpoint === '/modificar') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $acopioObj->modificarAcopio($data);
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
        break;
    case 'DELETE':
        if ($tokenObj->validarToken($token)) {
            // El token es válido, validamos ahora el rol
            $usuario = $tokenObj->obtenerUsuarioPorToken($token);
            if ($usuario['usr_rol'] === 'administrador') {
                if ($endpoint === '/acopios') {
                    $data = json_decode(file_get_contents('php://input'), true);

                    if (isset($data['cent_a_id'])) {
                        $acopioObj->deleteCentroAcopioById($data['cent_a_id']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'ID no proporcionada']);
                    }
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
    default:
        // Maneja métodos no permitidos
        header('Allow: GET, POST, DELETE');
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}