<?php
/* API RESTful para gestionar usuarios
 * Permite operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * Requiere conexión a una base de datos MySQL
 */

// Importa las dependencias necesarias
require_once 'config.php';
require_once 'usuario.php';
require_once 'token.php';

$tokenObj = new Token($conn);
// Crea la instance de la clase Usuario
$usuarioObj = new Usuario($conn);
// Obtiene el método de la solicitud HTTP
$method = $_SERVER['REQUEST_METHOD'];
// Obtiene el endpoint de la solicitud y revisa que efectivamente haya un endpoint, caso contrario
//lo fija a vacío
$endpoint = $_SERVER['PATH_INFO'] ?? '';
// Establece el tipo de contenido de la respuesta (json)
header('Content-Type: application/json');
$token = $_COOKIE['token'] ?? '';


// Procesa la solicitud según el método HTTP
switch ($method) {
	case 'GET':
		//busca entre todas las cookies, aquella llamada token, si no encuentra, asigna cadena vacía
		//recordemos que la función devuelve true or false
		if ($tokenObj->validarToken($token)) {
			// El token es válido, validamos ahora el rol
			$usuario = $tokenObj->obtenerUsuarioPorToken($token);
			if ($usuario['usr_rol'] === 'administrador') {
				// Puede al método
				if ($endpoint === '/usuarios') {

					$usuarios = $usuarioObj->getAllUsuarios();
					echo json_encode($usuarios);

				} elseif (preg_match('/^\/usuarios\/(\d+)$/', $endpoint, $matches)) {
					// Obtiene un usuario por ID
					$usuarioId = $matches[1];
					$usuario = $usuarioObj->getUsuarioById($usuarioId);
					echo json_encode($usuario);
				} elseif ($endpoint === '/funcionarios') {
					// Obtiene funcionarios
					$funcionarios = $usuarioObj->getAllFuncionarios();
					echo json_encode($funcionarios);
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
	case 'POST':
		if ($endpoint === '/usuarios') {
			if ($tokenObj->validarToken($token)) {
				// El token es válido, validamos ahora el rol
				$usuario = $tokenObj->obtenerUsuarioPorToken($token);
				if ($usuario['usr_rol'] === 'administrador') {

					// Añade un nuevo usuario
					$data = json_decode(file_get_contents('php://input'), true);
					$result = $usuarioObj->addUsuario($data);
					echo $result;
				}
			} else {
				// El token no es válido, invalidez es lo que 401 significa
				http_response_code(401);
				echo json_encode([
					"mensaje" => "⚠️ Sesión inválida o vencida."
				]);
			}
		} elseif ($endpoint === '/login') {
			$data = json_decode(file_get_contents('php://input'), true);
			$result = $usuarioObj->loginUsuario($data);
			echo $result;
		} elseif ($endpoint === '/logout') {
			$data = json_decode(file_get_contents('php://input'), true);
			$result = $usuarioObj->logoutUsuario($data);
			echo $result;
		} else if ($endpoint === '/modificar') {
			if ($tokenObj->validarToken($token)) {
				// El token es válido, validamos ahora el rol
				$usuario = $tokenObj->obtenerUsuarioPorToken($token);
				if ($usuario['usr_rol'] === 'administrador') {
					$data = json_decode(file_get_contents('php://input'), true);
					$usuarioObj->modificarUsuario($data);
				}
			} else {
				// El token no es válido, invalidez es lo que 401 significa
				http_response_code(401);
				echo json_encode([
					"mensaje" => "⚠️ Sesión inválida o vencida."
				]);
			}
		} else if ($endpoint === '/registro') {
			$data = json_decode(file_get_contents('php://input'), true);
			$usuarioObj->addVecino($data);
		} else if ($endpoint === '/cambiarContraseña') {
			$token = $_COOKIE['token'] ?? '';

			if ($tokenObj->validarToken($token)) {

				$usuario = $tokenObj->obtenerUsuarioPorToken($token);

				$data = json_decode(file_get_contents("php://input"), true);

				$password_actual = $data['password_actual'] ?? '';
				$password_nueva = $data['password_nueva'] ?? '';

				$usuarioObj->cambiarContraseña(
					$usuario['usr_ci'],
					$password_actual,
					$password_nueva
				);

			} else {

				http_response_code(401);

				echo json_encode([
					"mensaje" => "⚠️ Sesión inválida o vencida."
				]);

				exit;
			}

		}
		break;
	case 'DELETE':
		if ($endpoint === '/usuarios') {
			$data = json_decode(file_get_contents('php://input'), true);

			if (isset($data['usr_ci'])) {
				$usuarioObj->deleteUsuarioByCI($data['usr_ci']);
			} else {
				http_response_code(400);
				echo json_encode(['error' => 'Cédula no proporcionada']);
			}
		}
		break;
	default:
		// Maneja métodos no permitidos
		header('Allow: GET, POST, DELETE');
		http_response_code(405);
		echo json_encode(['error' => 'Método no permitido']);
		break;
}
?>