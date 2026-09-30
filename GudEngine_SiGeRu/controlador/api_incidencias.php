<?php

require_once 'config.php';
require_once 'incidencia.php';
require_once 'token.php';
require_once 'cuadrilla.php';
require_once 'camion.php';

$tokenObj = new Token($conn);
$incidenciaObj = new Incidencia($conn);
$cuadrillaObj = new Cuadrilla($conn);
$camionObj = new Camion($conn);

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '';
$token = $_COOKIE['token'] ?? '';

header('Content-Type: application/json');

switch ($method) {

    case 'GET':
       
                if ($endpoint === '/incidencias') {
 if ($tokenObj->validarToken($token)) {
            // El token es válido, validamos ahora el rol
            $usuario = $tokenObj->obtenerUsuarioPorToken($token);
            if ($usuario['usr_rol'] === 'administrador') {

                    $incidencias = $incidenciaObj->getAllIncidencias();

                    echo json_encode($incidencias);
                    } else {
                    http_response_code(403);
                    echo json_encode(["mensaje" => "⚠️ No tiene permisos para realizar esta acción."]);
                    exit;
                }}

                } elseif (preg_match('/^\/incidencias\/(\d+)$/', $endpoint, $matches)) {
if ($tokenObj->validarToken($token)) {
            // El token es válido, validamos ahora el rol
            $usuario = $tokenObj->obtenerUsuarioPorToken($token);
            if ($usuario['usr_rol'] === 'administrador') {
                    $incidencia_id = $matches[1];

                    $incidencia = $incidenciaObj->getIncidenciaById($incidencia_id);

                    if ($incidencia) {
                        echo json_encode($incidencia);
                    } else {
                        http_response_code(404);
                        echo json_encode(["mensaje" => "Incidencia no encontrada."]);
                    }
                     } else {
                    http_response_code(403);
                    echo json_encode(["mensaje" => "⚠️ No tiene permisos para realizar esta acción."]);
                    exit;
                }}
                }
            
         elseif ($endpoint === '/mias') {
            if ($tokenObj->validarToken($token)) {
                $usuario = $tokenObj->obtenerUsuarioPorToken($token);
                $incidencias = $incidenciaObj->getMisIncidencias($usuario['usr_ci']);

                echo json_encode($incidencias);

            } else {
            http_response_code(403);
            echo json_encode([
                "mesnsaje" => "⚠️ No tiene permisos para realizar esta acción."
            ]);
        }}

        break;


    case 'POST':
        if ($tokenObj->validarToken($token)) {
            $usuario = $tokenObj->obtenerUsuarioPorToken($token);

            //REGISTRAR INCIDENCIA

            if ($endpoint === '/incidencias') {
                $data = json_decode(file_get_contents('php://input'), true);
                $incidenciaObj->addIncidencia($data, $usuario['usr_ci']);

                //ASIGNARLË CUADRILLA A LA INCIDENCIA
            } else if ($endpoint === '/asignarCuadrilla') {
                $data = json_decode(file_get_contents('php://input'), true);
                $cuadrilla = $cuadrillaObj->getCuadrillaById($data['inc_cuadrilla']);
                $camion = $camionObj->getCamionByMatricula($cuadrilla['cuad_cam']);
                $incidenciaObj->asignarCuadrilla($data, $camion);

                //RECHAZAR UNA INCIDENCIA
            } else if ($endpoint === '/rechazar') {
                $data = json_decode(file_get_contents('php://input'), true);
                $incidenciaObj->rechazarIncidencia($data);

            }
        } else {
            http_response_code(403);
            echo json_encode([
                "mensaje" => "⚠️ No tiene permisos para realizar esta acción."
            ]);
        }
        break;
    default:
        header('Allow: GET, POST, DELETE');
        http_response_code(405);
        echo json_encode([
            'mensaje' => 'Método no permitido.'
        ]);
        break;
}
