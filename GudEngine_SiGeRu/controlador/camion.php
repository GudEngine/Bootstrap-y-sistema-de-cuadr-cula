<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class Camion
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Obtener todos los camiones (método GET, endpoint: /camiones)
    public function getAllCamiones()
    {
        $query = "SELECT * FROM camion";
        $result = mysqli_query($this->conn, $query);
        $camiones = [];
        
        while($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        return $camiones;
    }

    // Obtener un camión por su matrícula (método GET, endpoint: /camiones/<matricula>)
    public function getCamionByMatricula($matricula)
    {
        // Limpiamos espacios y pasamos a mayúsculas
        $matricula = strtoupper(str_replace(' ', '', trim($matricula)));

        if (empty($matricula)) {
            return null;
        }

        $matricula = mysqli_real_escape_string($this->conn, $matricula);
        
        // como es un CHAR, en la query va con comillas simples
        $query = "SELECT * FROM camion WHERE cam_matricula = '$matricula'";
        $result = mysqli_query($this->conn, $query);
        $camion = mysqli_fetch_assoc($result);
        
        return $camion;
    }

    // Método para registrar un nuevo camión (método POST, endpoint: /camiones)
    public function addCamion($data)
    {
        // 1. Validar campos obligatorios
        if (
            empty($data['cam_matricula']) || trim($data['cam_matricula']) === "" || 
            empty($data['cam_tipo']) || trim($data['cam_tipo']) === "" || 
            empty($data['cam_modelo']) || trim($data['cam_modelo']) === "" || 
            empty($data['cam_estado']) || trim($data['cam_estado']) === "" ||
            empty($data['cam_capacidad']) || trim($data['cam_capacidad']) === ""
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => "🙅 Error: Todos los campos son obligatorios y no pueden estar vacíos."]);
            exit;
        }

        // 2. Limpieza de datos y normalización de la matrícula
        // Quitamos espacios y la pasamos a mayúsculas (ej: "sab 1234" -> "SAB1234")
        $cam_matricula = strtoupper(str_replace(' ', '', trim($data['cam_matricula'])));
        $cam_tipo      = trim($data['cam_tipo']);
        $cam_modelo    = trim($data['cam_modelo']);
        $cam_estado    = trim($data['cam_estado']);
        $cam_capacidad = filter_var($data['cam_capacidad'], FILTER_VALIDATE_INT);


        // 3. Validar el formato de la matrícula Uruguaya (3 letras, 4 números) 
        // e imponer que empiece estrictamente con la letra 'S' de Montevideo.
        // Regex: ^S (empieza con S), [A-Z]{2} (2 letras más), [0-9]{4}$ (4 números finales)
        if (!preg_match('/^S[A-Z]{2}[0-9]{4}$/', $cam_matricula)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de la matrícula no es válido. Debe tener 3 letras (comenzando con 'S') y 4 números (ej: SAB1234)."]);
            exit;
        }
        if (!$cam_capacidad || $cam_capacidad <= 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: La capacidad debe ser un número entero mayor a 0."]);
            exit;
        }

        // 4. Validar el tipo de camión
        $tipos_validos = ['volqueta', 'reciclaje', 'mezclados'];
        if (!in_array($cam_tipo, $tipos_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El tipo de camión seleccionado no es válido."]);
            exit;
        }

        // 5. Validar el modelo del camión
        $modelos_validos = ['mercedes-benz', 'caterpillar'];
        if (!in_array($cam_modelo, $modelos_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El modelo de vehículo seleccionado no es válido."]);
            exit;
        }

        // 6. Validar el estado del vehículo
        $estados_validos = ['funcional', 'roto'];
        if (!in_array($cam_estado, $estados_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El estado del vehículo ingresado no es válido."]);
            exit;
        }

        // 7. Escapamos los datos antes de guardarlos
        $cam_matricula = mysqli_real_escape_string($this->conn, $cam_matricula);
        $cam_tipo      = mysqli_real_escape_string($this->conn, $cam_tipo);
        $cam_modelo    = mysqli_real_escape_string($this->conn, $cam_modelo);
        $cam_estado    = mysqli_real_escape_string($this->conn, $cam_estado);
        $cam_capacidad    = mysqli_real_escape_string($this->conn, $cam_capacidad);

        try {
            // Insertamos en la tabla correspondiente
            $query = "INSERT INTO camion (cam_matricula, cam_tipo, cam_capacidad, cam_modelo, cam_estado) 
                      VALUES ('$cam_matricula', '$cam_tipo', '$cam_capacidad', '$cam_modelo', '$cam_estado')";
            
            mysqli_query($this->conn, $query);

            http_response_code(201); // Creado con éxito
            echo json_encode(["mensaje" => "Camión registrado con éxito."]);
            exit;

        } catch (mysqli_sql_exception $e) {
            $codigoErrorMySQL = $e->getCode();
            
            // Error 1062 = Entrada duplicada para la matrícula (Primary Key)
            if ($codigoErrorMySQL === 1062) {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Error: La matrícula ingresada ya se encuentra registrada en el sistema."]);
            } else {
                http_response_code(500);
                echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
            }
            exit;
        }
    }
    public function modificarCamion($data){
        // 1. Validar campos obligatorios
        if (
            empty($data['cam_matricula']) || trim($data['cam_matricula']) === "" || 
            empty($data['cam_tipo']) || trim($data['cam_tipo']) === "" || 
            empty($data['cam_modelo']) || trim($data['cam_modelo']) === "" || 
            empty($data['cam_estado']) || trim($data['cam_estado']) === "" ||
            empty($data['cam_capacidad']) || trim($data['cam_capacidad']) === ""
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => "🙅 Error: Todos los campos son obligatorios y no pueden estar vacíos."]);
            exit;
        }

        // 2. Limpieza de datos y normalización de la matrícula
        $cam_matricula = strtoupper(str_replace(' ', '', trim($data['cam_matricula'])));
        $cam_tipo      = trim($data['cam_tipo']);
        $cam_modelo    = trim($data['cam_modelo']);
        $cam_estado    = trim($data['cam_estado']);
        $cam_capacidad = filter_var($data['cam_capacidad'], FILTER_VALIDATE_INT);

        // 3. Validar el formato de la matrícula
        if (!preg_match('/^S[A-Z]{2}[0-9]{4}$/', $cam_matricula)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de la matrícula no es válido. Debe tener 3 letras (comenzando con 'S') y 4 números (ej: SAB1234)."]);
            exit;
        }

        if (!$cam_capacidad || $cam_capacidad <= 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: La capacidad debe ser un número entero mayor a 0."]);
            exit;
        }

        // 4. Validar el tipo de camión
        $tipos_validos = ['volqueta', 'reciclaje', 'mezclados'];
        if (!in_array($cam_tipo, $tipos_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El tipo de camión seleccionado no es válido."]);
            exit;
        }

        // 5. Validar el modelo del camión
        $modelos_validos = ['mercedes-benz', 'caterpillar'];
        if (!in_array($cam_modelo, $modelos_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El modelo de vehículo seleccionado no es válido."]);
            exit;
        }

        // 6. Validar el estado del vehículo
        $estados_validos = ['funcional', 'roto'];
        if (!in_array($cam_estado, $estados_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El estado del vehículo ingresado no es válido."]);
            exit;
        }

        // 7. VERIFICAR QUE EL CAMIÓN EXISTA EN LA BASE DE DATOS
        if (!$this->getCamionByMatricula($cam_matricula)) {
            http_response_code(404);
            echo json_encode(["mensaje" => "⚠️ Error: El camión con matrícula $cam_matricula no existe en el sistema."]);
            exit;
        }

        // 8. Escapamos los datos antes de guardarlos
        $cam_matricula = mysqli_real_escape_string($this->conn, $cam_matricula);
        $cam_tipo      = mysqli_real_escape_string($this->conn, $cam_tipo);
        $cam_modelo    = mysqli_real_escape_string($this->conn, $cam_modelo);
        $cam_estado    = mysqli_real_escape_string($this->conn, $cam_estado);
        $cam_capacidad = mysqli_real_escape_string($this->conn, $cam_capacidad);

        try {
            // 9. Actualizamos en la tabla correspondiente
            $query = "UPDATE camion 
                      SET cam_tipo = '$cam_tipo', 
                          cam_capacidad = '$cam_capacidad', 
                          cam_modelo = '$cam_modelo', 
                          cam_estado = '$cam_estado'
                      WHERE cam_matricula = '$cam_matricula'";
            
            mysqli_query($this->conn, $query);

            http_response_code(200); // 200 OK es el ideal para actualizaciones exitosas
            echo json_encode(["mensaje" => "Camión actualizado con éxito."]);
            exit;

        } catch (mysqli_sql_exception $e) {
            // Como quitamos el 1062, solo capturamos errores críticos
            http_response_code(500);
            echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
            exit;
        }
    }
    public function deleteCamionByMatricula($cam_matricula){
        // 1. Limpieza y validación para asegurarnos de que sea un número entero positivo
        if (
            empty($cam_matricula) || trim($cam_matricula) === "" 
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => "🙅 Error: El campo no puede estar vacío."]);
            exit;
        }
        
        $cam_matricula = strtoupper(str_replace(' ', '', trim($cam_matricula)));
        
        if (!preg_match('/^S[A-Z]{2}[0-9]{4}$/', $cam_matricula)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de la matrícula no es válido. Debe tener 3 letras (comenzando con 'S') y 4 números (ej: SAB1234)."]);
            exit;
        }

         if (!$this->getCamionByMatricula($cam_matricula)) {
            http_response_code(404);
            echo json_encode(["mensaje" => "⚠️ Error: El camión con matrícula $cam_matricula no existe en el sistema."]);
            exit;
        }
        try {
            // Query para bajar el camion en la base de datos
            $query = "UPDATE camion SET cam_activo = false WHERE cam_matricula = '$cam_matricula'";
            
            mysqli_query($this->conn, $query);

            // 2. Tu técnica de affected_rows entra en acción
            if (mysqli_affected_rows($this->conn) > 0) {
                http_response_code(200); // 200 significa É.X.I.T.O
                echo json_encode(["mensaje" => "Camión #$cam_matricula eliminado con éxito."]);
                exit;
            } else {
                // El query funcionó perfectamente, pero no borró nada porque el ID no estaba
                http_response_code(404); // Usamos 404 porque no se encontró el recurso
                echo json_encode(["mensaje" => "⚠️ Error: El camión no existe o ya fue eliminado."]);
                exit;
            }

        } catch (mysqli_sql_exception $e) {
            http_response_code(500); 
            echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
            exit;
        }
    
    }
}