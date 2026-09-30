<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
//si sobra tiempo, añadir mapa al formulario de eliminación
class Cuadrilla
{
  private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getAllCuadrillas(){
        $query = "SELECT 
                    c.cuad_id,  
                    c.cuad_cam, 
                    c.cuad_activa,
                    u.usr_ci, 
                    u.usr_name, 
                    u.usr_apellido 
                FROM cuadrilla c
                INNER JOIN cuadrilla_recolector cr ON c.cuad_id = cr.cuad_id
                INNER JOIN usuario u ON cr.usr_ci = u.usr_ci
                ORDER BY c.cuad_id DESC";

        $result = mysqli_query($this->conn, $query);
    
        $cuadrillas = [];
    
        while ($row = mysqli_fetch_assoc($result)) {
            $cuadrillas[] = $row;
        }   
    
        return $cuadrillas;

    }

       public function getCuadrillaById($id){
        $query = "SELECT 
                    c.cuad_id,  
                    c.cuad_cam, 
                    c.cuad_activa,
                    u.usr_ci, 
                    u.usr_name, 
                    u.usr_apellido 
                FROM cuadrilla c
                INNER JOIN cuadrilla_recolector cr ON c.cuad_id = cr.cuad_id
                INNER JOIN usuario u ON cr.usr_ci = u.usr_ci
                where c.cuad_id = $id ";

        $result = mysqli_query($this->conn, $query);
   		$cuadrillas = mysqli_fetch_assoc($result);

        return $cuadrillas;

    }

  public function addCuadrilla($data) {
        // 1. Verificación de que los datos existan y no estén vacíos (TU ESTILO)
        if (
            empty($data['cuad_cam']) || trim($data['cuad_cam']) === "" ||
            empty($data['cuad_ci1']) || trim($data['cuad_ci1']) === "" ||
            empty($data['cuad_ci2']) || trim($data['cuad_ci2']) === ""
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: Faltan datos obligatorios (camión o recolectores)."]);
            exit;
        }

        // 2. Extracción y limpieza
        $cam_matricula = strtoupper(str_replace(' ', '', trim($data['cuad_cam'])));
        $ci1 = trim($data['cuad_ci1']);
        $ci2 = trim($data['cuad_ci2']);

        // 3. Validación de Matrícula (TU ESTILO)
        if (!preg_match('/^S[A-Z]{2}[0-9]{4}$/', $cam_matricula)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de la matrícula no es válido. Debe tener 3 letras (comenzando con 'S') y 4 números (ej: SAB1234)."]);
            exit;
        }

        // 4. Validación de Cédulas (TU ESTILO)
        if (!ctype_digit($ci1) || strlen($ci1) !== 8 || !ctype_digit($ci2) || strlen($ci2) !== 8) {
            http_response_code(400);
            echo json_encode(["mensaje" => " Error: Las Cédulas de Identidad deben contener únicamente 8 números, sin puntos ni guiones."]);
            exit;
        }

        // 5. Validación de negocio: Evitar Primary Key duplicada
        if ($ci1 === $ci2) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: Una cuadrilla no puede tener al mismo recolector dos veces."]);
            exit;
        }
        $query_roles = "SELECT COUNT(*) as total FROM usuario WHERE usr_ci IN (?, ?) AND usr_rol = 'recolector'";
        $stmt_roles = $this->conn->prepare($query_roles);
        
        // Vinculamos las dos cédulas como enteros ("ii")
        $stmt_roles->bind_param("ii", $ci1, $ci2);
        $stmt_roles->execute();
        $result_roles = $stmt_roles->get_result();
        $row_roles = $result_roles->fetch_assoc();

        if ($row_roles['total'] != 2) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: Uno o ambos usuarios no existen o no tienen el rol de 'recolector'."]);
            $stmt_roles->close();
            exit;
        }
        $stmt_roles->close();

        // 6. Inserción con Transacciones
        try {
            $this->conn->begin_transaction();

            // --- INSERT 1: Crear la cuadrilla ---
            $query_cuad = "INSERT INTO cuadrilla (cuad_cam) VALUES (?)";
            $stmt_cuad = $this->conn->prepare($query_cuad);
            $stmt_cuad->bind_param("s", $cam_matricula);
            
            if (!$stmt_cuad->execute()) {
                throw new Exception("Error al insertar en tabla cuadrilla.");
            }
            
            $cuad_id = $this->conn->insert_id;
            $stmt_cuad->close();

            // --- INSERT 2 y 3: Asignar los recolectores ---
            $query_rec = "INSERT INTO cuadrilla_recolector (usr_ci, cuad_id) VALUES (?, ?)";
            $stmt_rec = $this->conn->prepare($query_rec);

            $stmt_rec->bind_param("ii", $ci1, $cuad_id);
            if (!$stmt_rec->execute()) {
                throw new Exception("Error al asignar el primer recolector.");
            }

            $stmt_rec->bind_param("ii", $ci2, $cuad_id);
            if (!$stmt_rec->execute()) {
                throw new Exception("Error al asignar el segundo recolector.");
            }
            
            $stmt_rec->close();

            $this->conn->commit();

            http_response_code(201);
            echo json_encode(["status" => "success", "mensaje" => " Cuadrilla registrada con éxito."]);

        } catch (Exception $e) {
            $this->conn->rollback();
            http_response_code(500);
            
            $error_msg = "Error interno del servidor al crear la cuadrilla.";
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                $error_msg = "Error de integridad: El camión o los recolectores seleccionados no existen.";
            }

            echo json_encode(["mensaje" => $error_msg]);
        }
    }
    //dar de baja cuadrilla y crear una nueva con un camión un nuevo.
    /*
    public function modificarCuadrilla($data){
        // 1. Verificación de que los datos obligatorios existan y no estén vacíos
        if (
            empty($data['cuad_id']) || trim($data['cuad_id']) === "" ||
            empty($data['cuad_cam']) || trim($data['cuad_cam']) === ""
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: Faltan datos obligatorios (ID de cuadrilla o camión)."]);
            exit;
        }

        // 2. Extracción y limpieza
        $cuad_id = trim($data['cuad_id']);
        $cam_matricula = strtoupper(str_replace(' ', '', trim($data['cuad_cam'])));

        // 3. Validación de ID (debe ser numérico)
        if (!ctype_digit((string)$cuad_id)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El ID de la cuadrilla debe ser un número entero válido."]);
            exit;
        }

        // 4. Validación del formato de Matrícula
        if (!preg_match('/^S[A-Z]{2}[0-9]{4}$/', $cam_matricula)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de la matrícula no es válido. Debe tener 3 letras (comenzando con 'S') y 4 números (ej: SAB1234)."]);
            exit;
        }

        // 5. Ejecución de la actualización
       $query = "UPDATE cuadrilla SET cuad_cam = ? WHERE cuad_id = ?";
        $stmt = $this->conn->prepare($query);

        // "s" para la matrícula (string), "i" para el cuad_id (integer)
        $stmt->bind_param("si", $cam_matricula, $cuad_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "mensaje" => "✅ Camión de la cuadrilla modificado con éxito."]);
            } else {
                // Se ejecuta si la cuadrilla no existe o si se mandó exactamente la misma matrícula que ya tenía
                http_response_code(200);
                echo json_encode(["mensaje" => "⚠️ No se realizaron cambios: La cuadrilla no existe o ya tenía asignado ese mismo camión."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["mensaje" => "⚠️ Error interno del servidor al intentar modificar la cuadrilla."]);
        }

        $stmt->close();

    }*/
    public function deleteCuadrillaById($cuad_id) {
        // 1. Validar que sea un número entero positivo
        $cuad_id = filter_var($cuad_id, FILTER_VALIDATE_INT);

        if ($cuad_id === false || $cuad_id <= 0) {
            http_response_code(400); 
            echo json_encode(["mensaje" => "⚠️ Error: El ID de la cuadrilla no es válido."]);
            exit;
        }

        try {
            $query = "UPDATE cuadrilla SET  cuad_activa = false WHERE cuad_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $cuad_id);
            $stmt->execute();

            // 3. Verificación con affected_rows
            if ($stmt->affected_rows > 0) {
                http_response_code(200);
                echo json_encode(["mensaje" => "Cuadrilla #$cuad_id eliminada con éxito."]);
            } else {
                http_response_code(404);
                echo json_encode(["mensaje" => "⚠️ Error: La cuadrilla no existe o ya fue eliminada."]);
            }

            $stmt->close();
            exit;

        } catch (mysqli_sql_exception $e) {
            
            http_response_code(500); 
            echo json_encode(["mensaje" => "Error interno en el servidor: " . $e->getMessage()]);
            exit;
        }
    }

}