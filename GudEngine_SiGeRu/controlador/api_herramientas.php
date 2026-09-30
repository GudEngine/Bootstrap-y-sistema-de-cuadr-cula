<?php

require_once 'config.php';
require_once 'herramienta.php';
require_once 'token.php';

$tokenObj = new Token($conn);

$herramObj = new Herramienta($conn);

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';

header('Content-Type: application/json');
$token = $_COOKIE['token'] ?? '';
if ($tokenObj->validarToken($token)) {
			// El token es válido, validamos ahora el rol
			$usuario = $tokenObj->obtenerUsuarioPorToken($token);
			if ($usuario['usr_rol'] === 'administrador') {
switch ($method) {

    case 'GET':

        if ($endpoint === '/herramientas') {

            $herramientas = $herramObj->getAllHerramientas();

            echo json_encode($herramientas);
            exit;

        } elseif (preg_match('/^\/herramientas\/(\d+)$/', $endpoint, $matches)) {

            $herramientaId = $matches[1];

            $herramienta = $herramObj->getHerramientaById($herramientaId);

            if ($herramienta) {
                echo json_encode($herramienta);
            } else {
                http_response_code(404);
                echo json_encode(["mensaje" => "⚠️ Herramienta no encontrada."]);
            }

            exit;
        }

        break;


    case 'POST':

        if ($endpoint === '/herramientas') {
            $data = json_decode(file_get_contents('php://input'),true);
            $herramObj->addHerramienta($data);
        } elseif ($endpoint === '/modificar') {

            $data = json_decode(file_get_contents('php://input'),true);

            $herramObj->modificarHerramienta($data);
        }

        break;


    case 'DELETE':
        if ($endpoint === '/herramientas') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['herram_id'])) {
            	$herramObj->deleteHerramientaById($data['herram_id']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID no proporcionada']);
            }
        }
        break;


    default:

        header('Allow: GET, POST, DELETE');

        http_response_code(405);

        echo json_encode(['error' => 'Método no permitido']);

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