<?php

// API de validacion de token

require_once 'config.php';

require_once 'token.php';
// Crea la instancia de la clase Token
$tokenObj = new Token($conn);
// Obtiene el método de la solicitud HTTP y el endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';
// Establece el tipo de contenido de la respuesta
header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        if ($endpoint === '/verificar') {
            $token = $_COOKIE['token'] ?? '';
            if ($tokenObj->validarToken($token)) {
                $usuario = $tokenObj->obtenerUsuarioPorToken($token);
                echo json_encode($usuario);

            } else {
                http_response_code(401);
                echo json_encode(["mensaje" => "⚠️ Sesión inválida o vencida."]);
            }
            exit;
        }
        break;

    default:
        header('Allow: GET');
        http_response_code(405);
        echo json_encode([
            'error' => 'Método no permitido'
        ]);
        break;
}