<?php
/* API RESTful para gestionar contenedores de residuos
 * Por ahora permite operaciones  de registro y lectura
 * Requiere conexión a una base de datos MySQL
 */

// Importa las dependencias necesarias
require_once 'config.php';
require_once 'contenedor.php'; // 
require_once 'token.php';

$tokenObj = new Token($conn);
// Crea la instancia de la clase Contenedor
$contenedorObj = new Contenedor($conn);

// Obtiene el método de la solicitud HTTP y el endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';
// Establece el tipo de contenido de la respuesta (json)
header('Content-Type: application/json');
$token = $_COOKIE['token'] ?? '';

// Procesa la solicitud según el método HTTP
switch ($method) {
    case 'GET':
        if ($endpoint === '/contenedores') {
            // Obtiene todos los contenedores de la base de datos
            $contenedores = $contenedorObj->getAllContenedores();
            echo json_encode($contenedores);
            exit;
        } elseif (preg_match('/^\/contenedores\/(\d+)$/', $endpoint, $matches)) {
            // Obtiene un contenedor específico por ID pasándole el número qie manda la URL
            $contenedorId = $matches[1];
            $contenedor = $contenedorObj->getContenedorById($contenedorId);

            if ($contenedor) {
                echo json_encode($contenedor);
            } else {
                http_response_code(404);
                echo json_encode(["mensaje" => "⚠️ Contenedor no encontrado."]);
            }
            exit;
        }
        break;

    case 'POST':
        if ($tokenObj->validarToken($token)) {
            // El token es válido, validamos ahora el rol
            $usuario = $tokenObj->obtenerUsuarioPorToken($token);
            if ($usuario['usr_rol'] === 'administrador') {
                if ($endpoint === '/contenedores') {
                    // Recibe los datos en formato JSON desde el frontend
                    $data = json_decode(file_get_contents('php://input'), true);

                    // Llama directamente a la función. Ella procesa, valida, 
                    // responde con su propio echo y corta la ejecución con exit;
                    $contenedorObj->addContenedor($data, $usuario['usr_municipio']);
                } 
                else if ($endpoint === '/modificar') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $contenedorObj->modificarContenedor($data,$usuario['usr_municipio']);
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
                if ($endpoint === '/contenedores') {
                    $data = json_decode(file_get_contents('php://input'), true);

                    if (isset($data['cont_id'])) {
                        $contenedorObj->deleteContenedorById($data['cont_id'],$usuario['usr_municipio']);
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