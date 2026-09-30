<?php

require_once 'config.php';
require_once 'circuito.php';
require_once 'token.php';

$circuitoObj = new Circuito($conn);
$tokenObj = new Token($conn);

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';

header('Content-Type: application/json');

switch ($method) {

    case 'GET':

        if ($endpoint === '/circuitos/municipio') {

            $token = $_COOKIE['token'] ?? '';
//esto es mejor para validar porque puedo copiar y pegar sin pelearme con llaves mal cerradas y eso
            if (!$tokenObj->validarToken($token)) {
                http_response_code(401);
                echo json_encode(["mensaje" => "⚠️ Sesión inválida o vencida."]);
                exit;
            }
            $usuario = $tokenObj->obtenerUsuarioPorToken($token);

            if ($usuario['usr_rol'] !== 'administrador') {
                http_response_code(403);
                echo json_encode(["mensaje" => "⚠️ No tiene permisos para realizar esta acción."]);
                exit;
            }
            $circuitos = $circuitoObj->getCircuitosByMunicipio($usuario['usr_municipio']);
            echo json_encode($circuitos);
            exit;
        }else if ($endpoint === '/circuitos'){
            $circuitos = $circuitoObj->getAllCircuitos();
            echo json_encode($circuitos);
            exit;
        }
        break;
    default:
        http_response_code(405);
        echo json_encode([
            "mensaje" => "Método no permitido."
        ]);
        break;
}