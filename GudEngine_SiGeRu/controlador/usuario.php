<?php
/* Clase usuario para gestionar con API RESTful
 * Permite operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * Requiere conexión a una 
 * ase de datos MySQL
 */

// Configuracion del reporte de errores
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class Usuario
{
	private $conn;

	// Constructor que recibe la conexión a la base de datos
	public function __construct($conn)
	{
		$this->conn = $conn;
	}

	// Métodos para manejar usuarios
	// Obtener todos los usuarios (método GET, endopoint: /usuarios)

	public function getAllFuncionarios(){
		$query = "SELECT * FROM usuario where usr_rol != 'vecino'";
		$result = mysqli_query($this->conn, $query);
		$usuarios = [];
		while($row = mysqli_fetch_assoc($result)) {
			$usuarios[] = $row;
		}
		return $usuarios;
	}
	public function getAllUsuarios()
	{
		$query = "SELECT * FROM usuario";
		$result = mysqli_query($this->conn, $query);
		$usuarios = [];
		while($row = mysqli_fetch_assoc($result)) {
			$usuarios[] = $row;
		}
		return $usuarios;
	}
	// Obtener un usuario por ID  (método GET, endopoint: /usuarios/<id>)
	public function getUsuarioById($cedula){
		$query = "SELECT * FROM usuario WHERE usr_ci = $cedula ";
		$result = mysqli_query($this->conn, $query);
		$usuario = mysqli_fetch_assoc($result);
		return $usuario;
	}
	//asesinar una cuenta por e-mail
	public function deleteUsuarioByCI($cedula){
		
   // 1. Limpieza y validación para asegurarnos de que sea un número entero positivo
        $cedula = filter_var($cedula, FILTER_VALIDATE_INT);

        if ($cedula === false || $cedula <= 0) {
            http_response_code(400); 
            echo json_encode(["mensaje" => "⚠️ Error: El ID del contenedor no es válido."]);
            exit;
        }
		try {
			$query = "UPDATE usuario SET 
			usr_activo = false
			WHERE usr_ci = '$cedula'";
			mysqli_query($this->conn, $query);

			//me gusta mucho esto de affected_rows
			if (mysqli_affected_rows($this->conn) > 0) {
				http_response_code(200); // 200 significa É.X.I.T.O
				echo json_encode((["mensaje" => "Funcionario eliminado con éxito"]));
				exit;
			} else {
            // El query funcionó pero la cédula no existía en la tabla
            http_response_code(404); 
            echo json_encode(["mensaje" => "⚠️ Error: Cédula no registrada."]);
			exit;
        }
        

		} catch (mysqli_sql_exception $e) {
			http_response_code(500); 
			echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
			exit;
		}
		 
	}
	//registro del vecinirijillo
	public function addVecino($data) {
		// 1. Verificación de existencia de campos obligatorios para el vecino
		if (
			empty($data['usr_ci']) || trim($data['usr_ci']) === "" || 
			empty($data['usr_name']) || trim($data['usr_name']) === "" || 
			empty($data['usr_apellido']) || trim($data['usr_apellido']) === "" || 
			empty($data['usr_email']) || trim($data['usr_email']) === "" || 
			empty($data['usr_password']) || trim($data['usr_password']) === ""
		) {
			http_response_code(400);
			echo json_encode(["mensaje" => "🙅 Error: Todos los campos son obligatorios (Cédula, Nombre, Apellido, Email y Contraseña)."]);
			exit;
		}

		// 2. Limpieza de espacios con trim
		$usr_ci       = trim($data['usr_ci']);
		$usr_name     = trim($data['usr_name']);
		$usr_apellido = trim($data['usr_apellido']);
		$usr_email    = trim($data['usr_email']);
		$usr_password = trim($data['usr_password']);
		
		// Se hardcodea el rol vecino
		$usr_rol      = 'vecino';

		// 3. Validación de Cédula de Identidad (8 dígitos numéricos)
		if (!ctype_digit($usr_ci) || strlen($usr_ci) !== 8) {
			http_response_code(400);
			echo json_encode(["mensaje" => " Error: La Cédula de Identidad debe contener únicamente 8 números, sin puntos ni guiones."]);
			exit;
		}

		// 3.1. Validación de Nombre y Apellido (solo letras y espacios, admite tildes y Ñ)
		if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u', $usr_name) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u', $usr_apellido)) {
			http_response_code(400);
			echo json_encode(["mensaje" => " Error: El nombre y el apellido solo pueden contener letras y espacios."]);
			exit;
		}

		// 3.2. Validación de formato de correo electrónico
		if (!filter_var($usr_email, FILTER_VALIDATE_EMAIL)) {
			http_response_code(400);
			echo json_encode(["mensaje" => " Error: El formato del correo electrónico no es válido."]);
			exit;
		}

		// 4. Escape de caracteres para la consulta MySQL
		$usr_ci       = (int)$usr_ci;
		$usr_name     = mysqli_real_escape_string($this->conn, $usr_name);
		$usr_apellido = mysqli_real_escape_string($this->conn, $usr_apellido);
		$usr_email    = mysqli_real_escape_string($this->conn, $usr_email);
		$usr_password = password_hash($usr_password, PASSWORD_DEFAULT);
		$usr_password = mysqli_real_escape_string($this->conn, $usr_password);


		try {
			$query = "INSERT INTO usuario (usr_ci, usr_name, usr_apellido, usr_email, usr_password, usr_rol) 
					VALUES ($usr_ci, '$usr_name', '$usr_apellido', '$usr_email', '$usr_password', '$usr_rol')";
			
			mysqli_query($this->conn, $query);

			http_response_code(201); // 201 = Creado con éxito
			echo json_encode(["mensaje" => "Vecino registrado con éxito."]);
			exit;

		} catch (mysqli_sql_exception $e) {
			$codigo_error_mysql = $e->getCode();
			
			if ($codigo_error_mysql === 1062) {
				http_response_code(400);
				if (strpos($e->getMessage(), 'usr_email') !== false) {
					echo json_encode(["mensaje" => "⚠️ Error: El correo electrónico ya se encuentra registrado."]);
				} else {
					echo json_encode(["mensaje" => "⚠️ Error: La Cédula de Identidad ya se encuentra registrada en el sistema."]);
				}
			} else {
				http_response_code(500);
				echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
			}
			exit;
		}
	}

	// Agregar un nuevo usuario (método POST, endopoint: /usuarios)
	public function addUsuario($data) {
		// 1. Verificación de campos obligatorios (los 7 que requiere la alta de usuario operativo)
		if (
			empty($data['usr_ci'])       || trim($data['usr_ci']) === "" ||
			empty($data['usr_name'])     || trim($data['usr_name']) === "" ||
			empty($data['usr_apellido']) || trim($data['usr_apellido']) === "" ||
			empty($data['usr_email'])    || trim($data['usr_email']) === "" ||
			empty($data['usr_edad'])     || trim($data['usr_edad']) === "" ||
			empty($data['usr_rol'])      || trim($data['usr_rol']) === "" ||
			empty($data['usr_telefono']) || trim($data['usr_telefono']) === ""
		) {
			http_response_code(400);
			echo json_encode(["mensaje" => " Error: Todos los campos (Cédula, Nombre, Apellido, Email, Edad, Rol y Teléfono) son obligatorios."]);
			exit;
		}

		// 2. Limpieza de espacios con trim
		$usr_ci       = trim($data['usr_ci']);
		$usr_name     = trim($data['usr_name']);
		$usr_apellido = trim($data['usr_apellido']);
		$usr_email    = trim($data['usr_email']);
		$usr_edad     = trim($data['usr_edad']);
		$usr_rol      = trim($data['usr_rol']);
		$usr_telefono = trim($data['usr_telefono']);

		// 3. Validaciones de ints (Cédula, Teléfono y Edad)
		if (!ctype_digit($usr_ci) || strlen($usr_ci) !== 8) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: La Cédula de Identidad debe contener exactamente 8 números, sin puntos ni guiones."]);
			exit;
		}

		if (!ctype_digit($usr_telefono) || strlen($usr_telefono) < 8 || strlen($usr_telefono) > 9) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El teléfono debe ser numérico y contener entre 8 y 9 dígitos."]);
			exit;
		}

		if (!ctype_digit($usr_edad) || (int)$usr_edad < 16 || (int)$usr_edad > 100) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: La edad debe ser un número entero válido (mayor o igual a 16 años)."]);
			exit;
		}

		// 4. Validación de texto (Nombre y Apellido)
		if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u', $usr_name) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u', $usr_apellido)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El nombre y el apellido solo pueden incluir letras y espacios."]);
			exit;
		}

		// 5. Validación de formato de Email
		//filter_var es para usar una libreria de validaciones, email es una opción de validación
		if (!filter_var($usr_email, FILTER_VALIDATE_EMAIL)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El formato del correo electrónico no es válido."]);
			exit;
		}

		// 6. Validación contra el CHECK de Roles de MySQL
		$roles_permitidos = ['vecino', 'administrador', 'operario_vertedero', 'operario_taller', 'operario_acopio', 'recolector'];
		if (!in_array($usr_rol, $roles_permitidos, true)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El rol seleccionado no es válido dentro del sistema."]);
			exit;
		}

		// 7. Preparación y escape de datos para la Query
		$usr_ci        = (int)$usr_ci;
		$usr_edad      = (int)$usr_edad;
		$usr_telefono  = (int)$usr_telefono;
		$usr_name      = mysqli_real_escape_string($this->conn, $usr_name);
		$usr_apellido  = mysqli_real_escape_string($this->conn, $usr_apellido);
		$usr_email     = mysqli_real_escape_string($this->conn, $usr_email);
		$usr_rol       = mysqli_real_escape_string($this->conn, $usr_rol);
		$usr_password = password_hash("contraseña", PASSWORD_DEFAULT);
		try {
			//después preguntar si vale la pena pasar municipio
			// Y usr_password tomará 'contraseña' por DEFAULT en MySQL.
			$query = "INSERT INTO usuario (usr_ci, usr_name, usr_apellido, usr_email, usr_edad, usr_rol, usr_password, usr_telefono) 
					VALUES ($usr_ci, '$usr_name', '$usr_apellido', '$usr_email', $usr_edad, '$usr_rol', '$usr_password', $usr_telefono)";
			
			mysqli_query($this->conn, $query);

			http_response_code(201); // 201 = Creado con éxito
			echo json_encode(["mensaje" => "Usuario registrado con éxito."]);
			exit;

		} catch (mysqli_sql_exception $e) {
			$codigoErrorMySQL = $e->getCode();
			
			if ($codigoErrorMySQL === 1062) { // Llave duplicada 
				http_response_code(400);
				if (strpos($e->getMessage(), 'usr_email') !== false) {
					echo json_encode(["mensaje" => "⚠️ Error: El correo electrónico ya se encuentra registrado."]);
				} else {
					echo json_encode(["mensaje" => "⚠️ Error: La Cédula de Identidad ya se encuentra registrada en el sistema."]);
				}
			} else {
				http_response_code(500);
				echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
			}
			exit;
		}
	}
	public function modificarUsuario($data) {
		// 1. Verificación de campos obligatorios (los 7 requeridos)
		if (
			empty($data['usr_ci'])       || trim($data['usr_ci']) === "" ||
			empty($data['usr_name'])     || trim($data['usr_name']) === "" ||
			empty($data['usr_apellido']) || trim($data['usr_apellido']) === "" ||
			empty($data['usr_email'])    || trim($data['usr_email']) === "" ||
			empty($data['usr_edad'])     || trim($data['usr_edad']) === "" ||
			empty($data['usr_rol'])      || trim($data['usr_rol']) === "" ||
			empty($data['usr_telefono']) || trim($data['usr_telefono']) === ""
		) {
			http_response_code(400);
			echo json_encode(["mensaje" => "🙅 Error: Todos los campos (Cédula, Nombre, Apellido, Email, Edad, Rol y Teléfono) son obligatorios."]);
			exit;
		}

		// 2. Limpieza de espacios con trim
		$usr_ci       = trim($data['usr_ci']);
		$usr_name     = trim($data['usr_name']);
		$usr_apellido = trim($data['usr_apellido']);
		$usr_email    = trim($data['usr_email']);
		$usr_edad     = trim($data['usr_edad']);
		$usr_rol      = trim($data['usr_rol']);
		$usr_telefono = trim($data['usr_telefono']);

		// 3. Validaciones numéricas (Cédula, Teléfono y Edad)
		if (!ctype_digit($usr_ci) || strlen($usr_ci) !== 8) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: La Cédula de Identidad debe contener exactamente 8 números, sin puntos ni guiones."]);
			exit;
		}

		if (!ctype_digit($usr_telefono) || strlen($usr_telefono) < 8 || strlen($usr_telefono) > 9) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El teléfono debe ser numérico y contener entre 8 y 9 dígitos."]);
			exit;
		}

		if (!ctype_digit($usr_edad) || (int)$usr_edad < 18 || (int)$usr_edad > 100) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: La edad debe ser un número entero válido (mayor o igual a 18 años)."]);
			exit;
		}

		// 4. Validación de texto (Nombre y Apellido)
		if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u', $usr_name) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u', $usr_apellido)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El nombre y el apellido solo pueden incluir letras y espacios."]);
			exit;
		}

		// 5. Validación de formato de Email
		if (!filter_var($usr_email, FILTER_VALIDATE_EMAIL)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El formato del correo electrónico no es válido."]);
			exit;
		}

		// 6. Validación contra el CHECK de Roles de MySQL
		$roles_permitidos = ['vecino', 'administrador', 'operario_vertedero', 'operario_taller', 'operario_acopio', 'recolector'];
		if (!in_array($usr_rol, $roles_permitidos, true)) {
			http_response_code(400);
			echo json_encode(["mensaje" => "⚠️ Error: El rol seleccionado no es válido dentro del sistema."]);
			exit;
		}

		// 7. Preparación y escape de datos para la Query
		$usr_ci        = (int)$usr_ci;
		$usr_edad      = (int)$usr_edad;
		$usr_telefono  = (int)$usr_telefono;
		$usr_name      = mysqli_real_escape_string($this->conn, $usr_name);
		$usr_apellido  = mysqli_real_escape_string($this->conn, $usr_apellido);
		$usr_email     = mysqli_real_escape_string($this->conn, $usr_email);
		$usr_rol       = mysqli_real_escape_string($this->conn, $usr_rol);

		try {
			$query = "UPDATE usuario SET 
						usr_name     = '$usr_name', 
						usr_apellido = '$usr_apellido', 
						usr_email    = '$usr_email', 
						usr_edad     = $usr_edad, 
						usr_rol      = '$usr_rol', 
						usr_telefono = $usr_telefono 
					WHERE usr_ci = $usr_ci";

			mysqli_query($this->conn, $query);

			if (mysqli_affected_rows($this->conn) > 0) {
				http_response_code(200);
				echo json_encode(["mensaje" => "Usuario actualizado con éxito."]);
				exit;
			} else {
				http_response_code(400);
				echo json_encode(["mensaje" => "⚠️ Error: No se realizaron cambios (Cédula no registrada o los datos ingresados son idénticos a los actuales)."]);
				exit;
			}

		} catch (mysqli_sql_exception $e) {
			$codigoErrorMySQL = $e->getCode();

			if ($codigoErrorMySQL === 1062) {
				http_response_code(400);
				echo json_encode(["mensaje" => "⚠️ Error: El correo electrónico ya se encuentra registrado por otro usuario."]);
			} else {
				http_response_code(500);
				echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
			}
			exit;
		}
	}

	// Iniciar sesión de usuario (método POST, endopoint: /login)
	public function loginUsuario($data) {

		if (empty($data['usr_email']) || empty($data['usr_password']) || empty($data['usr_rol'])) {
			http_response_code(400);
			echo json_encode([
				"mensaje" => "⚠️ Debe seleccionar su rol e ingresar e-mail y contraseña."
			]);
			exit;
		}

		$usr_email    = mysqli_real_escape_string($this->conn, trim($data['usr_email']));
		$usr_rol      = mysqli_real_escape_string($this->conn, trim($data['usr_rol']));
		$usr_password = trim($data['usr_password']);

		// Buscar usuario por email y rol
		$query = "SELECT * FROM usuario 
				WHERE usr_email = '$usr_email' 
				AND usr_rol = '$usr_rol'";

		$result = mysqli_query($this->conn, $query);

		if (mysqli_num_rows($result) > 0) {

			$usuario = mysqli_fetch_assoc($result);

			// Verificar contraseña
			if (password_verify($usr_password, $usuario['usr_password'])) {
				// Generar token para este usuario en este momento
				$usr_key = md5($usuario['usr_ci'] . time());

				// Verificar si el usuario ya tiene un token
				$query_acceso = "SELECT * FROM access_token 
								WHERE usr_ci = " . $usuario['usr_ci'];

				$result_acceso = mysqli_query($this->conn, $query_acceso);
				
				if (mysqli_num_rows($result_acceso) > 0) {

					// Si ya existe un token, lo actualizamos
					$query_acceso = "UPDATE access_token 
									SET token = '$usr_key',
										fecha_creado = NOW(),
										fecha_vencimiento = DATE_ADD(NOW(), INTERVAL 12 HOUR)
									WHERE usr_ci = " . $usuario['usr_ci'];

				} else {

					// Si no existe un token, lo insertamos
					$query_acceso = "INSERT INTO access_token 
									(usr_ci, token)
									VALUES (" . $usuario['usr_ci'] . ", '$usr_key')";
				}

				// Ejecutar INSERT o UPDATE
				$result_acceso = mysqli_query($this->conn, $query_acceso);

				if ($result_acceso) {

					echo json_encode([
						"mensaje" => "Inicio de sesión con éxito.",
						"rol"     => $usuario['usr_rol'],
						"token"   => $usr_key
					]);
					http_response_code(200);

					exit;

				} else {

					http_response_code(500);

					echo json_encode([
						"mensaje" => "⚠️ Error al generar el token."
					]);

					exit;
				}

			} else {

				// El usuario existe, pero la contraseña es incorrecta
				http_response_code(400);

				echo json_encode([
					"mensaje" => "⚠️ Contraseña o e-mail incorrecto."
				]);

				exit;
			}

		} else {

			// No existe un usuario con ese email y rol
			http_response_code(400);

			echo json_encode([
				"mensaje" => "⚠️ No existe un usuario con ese e-mail registrado como " . $usr_rol
			]);

			exit;
		}
	}

	public function logoutUsuario($data){
		if (!isset($data['usr_key']) || strlen($data['usr_key']) !== 32) {
			http_response_code(400);
			return json_encode(["error" => "Token inválido o inexistente"]);
		}

		$key = $data['usr_key'];

		// Eliminar el token de la base de datos
		$query = "DELETE FROM access_token WHERE token = '$key'";
		//this con= esta conexión
		$result = mysqli_query($this->conn, $query);

		if ($result) {

			// ¿Cuántas filas afectó la última consulta de ESTA conexión?(si se removió algo)
			if (mysqli_affected_rows($this->conn) > 0) {
				http_response_code(200);
				return json_encode(["success" => "Sesión cerrada correctamente"]);
			} else {
				http_response_code(404);
				return json_encode(["error" => "El token no existe"]);
			}

		} else {
			http_response_code(500);
			return json_encode(["error" => "Error al cerrar sesión"]);
		}
	}
	public function cambiarContraseña($usr_ci, $password_actual, $password_nueva) {
		// 1. Verificar que las contraseñas hayan sido enviadas
		if (
			empty($password_actual) ||
			empty($password_nueva)
		) {
			http_response_code(400);
			echo json_encode([
				"mensaje" => "⚠️ La contraseña actual y la nueva contraseña son obligatorias."
			]);
			exit;
		}

		// 2. Buscar al usuario
		$usr_ci = filter_var($usr_ci, FILTER_VALIDATE_INT);

		if ($usr_ci === false || $usr_ci <= 0) {
			http_response_code(400);
			echo json_encode([
				"mensaje" => "⚠️ El usuario no es válido."
			]);
			exit;
		}

		$usuario = $this->getUsuarioById($usr_ci);
		
		if (!$usuario) {
            http_response_code(404);
            echo json_encode([
                "mensaje" => "⚠️ El usuario no existe."
            ]);
            exit;
        }

		// 3. Verificar la contraseña actual
		if (!password_verify($password_actual, $usuario['usr_password'])) {
			http_response_code(400);
			echo json_encode([
				"mensaje" => "⚠️ La contraseña actual es incorrecta."
			]);
			exit;
		}


		// 4. Hashear la nueva contraseña
		$nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
		$nuevo_hash = mysqli_real_escape_string($this->conn, $nuevo_hash);

		// 5. Actualizar contraseña
		try {

			$query = "UPDATE usuario
					SET usr_password = '$nuevo_hash'
					WHERE usr_ci = $usr_ci";

			mysqli_query($this->conn, $query);

			http_response_code(200);
			echo json_encode([
				"mensaje" => "Contraseña modificada con éxito."
			]);
			exit;

		} catch (mysqli_sql_exception $e) {

			http_response_code(500);
			echo json_encode([
				"mensaje" => "⚠️ Error interno al modificar la contraseña."
			]);
			exit;
		}
	}
}