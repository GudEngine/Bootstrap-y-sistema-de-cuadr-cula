<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
//si sobra tiempo, añadir mapa al formulario de eliminación
class Herramienta
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getAllHerramientas()
    {
        $query = "SELECT * FROM herramienta";
        $result = mysqli_query($this->conn, $query);
        $herramientas = [];
        
        while($row = mysqli_fetch_assoc($result)) {
            $herramientas[] = $row;
        }
        return $herramientas;
    }

    public function getHerramientaById($id){
        // Limpiamos espacios
        $id = trim($id);

        // Validación de seguridad básica: que sea numérico
        if (empty($id) || !ctype_digit($id)) {
            return null;
        }

        $id = mysqli_real_escape_string($this->conn, $id);
        
        // Al ser un INT, en la query puede ir sin comillas, igual que la cédula
        $query = "SELECT * FROM herramienta WHERE herram_id = $id";
        $result = mysqli_query($this->conn, $query);
        $herramienta = mysqli_fetch_assoc($result);
        
        return $herramienta;
    }

    public function addHerramienta($data)
    {
        // 1. Verificar que estén los datos obligatorios
        if (
            empty($data['herram_tipo']) ||
            !isset($data['herram_estab'])
        ) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El tipo de herramienta y el establecimiento son obligatorios."
            ]);
            exit;
        }

        // 2. Limpiar los datos
        $herram_tipo  = trim($data['herram_tipo']);
        $herram_estab = filter_var($data['herram_estab'], FILTER_VALIDATE_INT);

        // 3. Validar que el establecimiento sea un número válido
        if (!$herram_estab || $herram_estab <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El establecimiento seleccionado no es válido."
            ]);
            exit;
        }

        // 4. Validar el tamaño del tipo
        if (mb_strlen($herram_tipo) > 40) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El tipo de herramienta supera los 40 caracteres permitidos."
            ]);
            exit;
        }

        // 5. Sanitizar el texto para SQL
        $herram_tipo = mysqli_real_escape_string($this->conn,$herram_tipo);

        try {

            // 6. Registrar la herramienta
            $query = "INSERT INTO herramienta (herram_tipo, herram_estab)
                     VALUES ('$herram_tipo', $herram_estab)";

            mysqli_query($this->conn, $query);

            // 7. Obtener el ID generado por MySQL
            $nuevoId = mysqli_insert_id($this->conn);

            http_response_code(201);

            echo json_encode([
                "mensaje" => "Herramienta #$nuevoId registrada con éxito.",
                "herram_id" => $nuevoId
            ]);

            exit;

        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1452) {

                http_response_code(400);

                echo json_encode([
                    "mensaje" => " El establecimiento seleccionado no existe."
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    "mensaje" => "Error interno al registrar la herramienta."
                ]);
            }

            exit;
        }
    }

    public function modificarHerramienta($data) {
        // 1. Verificar que estén los datos obligatorios
        if (
            empty($data['herram_id']) ||
            empty($data['herram_tipo']) || trim($data['herram_tipo']) === "" ||
            !isset($data['herram_estab'])
        ) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => " El ID, tipo de herramienta y establecimiento son obligatorios."
            ]);
            exit;
        }

        // 2. Limpiar los datos
        $herram_id = filter_var($data['herram_id'], FILTER_VALIDATE_INT);
        $herram_tipo = trim($data['herram_tipo']);
        $herram_estab = filter_var($data['herram_estab'], FILTER_VALIDATE_INT);

        // 3. Validar ID de la herramienta
        if ($herram_id === false || $herram_id <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El ID de la herramienta no es válido."
            ]);
            exit;
        }

        // 4. Verificar que la herramienta exista
        if (!$this->getHerramientaById($herram_id)) {
            http_response_code(404);
            echo json_encode([
                "mensaje" => "⚠️ La herramienta #$herram_id no existe en el sistema."
            ]);
            exit;
        }

        // 5. Validar establecimiento
        if ($herram_estab === false || $herram_estab <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El establecimiento seleccionado no es válido."
            ]);
            exit;
        }

        // 6. Validar tamaño del tipo
        if (mb_strlen($herram_tipo) > 40) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El tipo de herramienta supera los 40 caracteres permitidos."
            ]);
            exit;
        }

        // 7. Sanitizar el texto para SQL
        $herram_tipo = mysqli_real_escape_string($this->conn,$herram_tipo);

        try {
            // 8. Actualizar la herramienta
            $query = "UPDATE herramienta 
                    SET herram_tipo = '$herram_tipo',
                        herram_estab = $herram_estab
                    WHERE herram_id = $herram_id";

            mysqli_query($this->conn, $query);

            http_response_code(200);
            echo json_encode([
                "mensaje" => "Herramienta #$herram_id modificada con éxito."
            ]);
            exit;

        } catch (mysqli_sql_exception $e) {

            // Error 1452 = establecimiento inexistente
            if ($e->getCode() == 1452) {
                http_response_code(400);
                echo json_encode([
                    "mensaje" => "⚠️ El establecimiento seleccionado no existe."
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "mensaje" => "Error interno al modificar la herramienta."
                ]);
            }

            exit;
        }
    }
    public function deleteHerramientaById($herram_id){
        // 1. Limpieza y validación para asegurarnos de que sea un número entero positivo
        $herram_id = filter_var($herram_id, FILTER_VALIDATE_INT);

        if ($herram_id === false || $herram_id <= 0) {
            http_response_code(400); 
            echo json_encode(["mensaje" => "⚠️ Error: El ID proporcionado no es válido."]);
            exit;
        }

        try {
            // Query para eliminar  de la base de datos
            $query = "UPDATE herramienta SET herram_activo = false WHERE herram_id = $herram_id";
            
            mysqli_query($this->conn, $query);

            // 2. Tu técnica de affected_rows entra en acción
            if (mysqli_affected_rows($this->conn) > 0) {
                http_response_code(200); // 200 significa É.X.I.T.O
                echo json_encode(["mensaje" => "Herramienta #$herram_id eliminado con éxito."]);
                exit;
            } else {
                // El query funcionó perfectamente, pero no borró nada porque el ID no estaba
                http_response_code(404); // Usamos 404 porque no se encontró el recurso
                echo json_encode(["mensaje" => "⚠️ Error: La herramienta no existe o ya fue eliminada."]);
                exit;
            }

        } catch (mysqli_sql_exception $e) {
            http_response_code(500); 
            echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
            exit;
        }
    }
}