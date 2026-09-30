<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
//si sobra tiempo, añadir mapa al formulario de eliminación
class Centro_acopio
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getCentroAcopioById($id){
        $query = "SELECT * FROM centro_acopio WHERE cent_a_id = $id";

        $result = mysqli_query($this->conn, $query);

        $centroAcopio = mysqli_fetch_assoc($result);

        return $centroAcopio;
    }

    public function getAllAcopios() {
    try {
        // 1. Armamos la consulta uniendo ambas tablas
        $query = "SELECT 
                    c.cent_a_id, 
                    e.hora_apertura, 
                    e.hora_cierre, 
                    e.calle, 
                    e.num_puerta, 
                    c.cent_a_capacidad, 
                    c.cent_a_llenado
                  FROM centro_acopio c
                  INNER JOIN establecimiento e ON c.cent_a_id = e.estab_id";
                 // WHERE e.estab_activo = true"; // Solo traemos los que están activos, añadir esto luego

        // 2. Ejecutamos la consulta
        $result = mysqli_query($this->conn, $query);

        // 3. Preparamos el array que devolveremos como JSON
        $acopios = [];

        // 4. Recorremos los resultados y los guardamos en el array
        while ($row = mysqli_fetch_assoc($result)) {
            $acopios[] = $row;
        }

        // 5. Devolvemos el array en formato JSON (código 200 = OK)
        http_response_code(200);
        echo json_encode($acopios);
        exit;

    } catch (mysqli_sql_exception $e) {
        // Falla de base de datos
        http_response_code(500); 
        echo json_encode(["mensaje" => "Error interno al obtener los centros de acopio: " . $e->getMessage()]);
        exit;
    }
}
    public function addCentroAcopio($data) {
        // 1. Existencia de los datos
        if (
            empty($data['cent_calle']) || trim($data['cent_calle']) === "" ||
            !isset($data['cent_num_puerta']) || 
            empty($data['cent_hora_apertura']) || trim($data['cent_hora_apertura']) === "" ||
            empty($data['cent_hora_cierre']) || trim($data['cent_hora_cierre']) === "" ||
            !isset($data['cent_capacidad']) || 
            !isset($data['cent_llenado'])
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: Todos los campos del centro de acopio son obligatorios."]);
            exit;
        }

        // 2. Limpieza de strings
        $calle = trim($data['cent_calle']);
        $hora_apertura = trim($data['cent_hora_apertura']);
        $hora_cierre = trim($data['cent_hora_cierre']);

        // 3. Validar límite de tamaño de calle para VARCHAR(29)
        if (mb_strlen($calle) > 29) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: La calle supera el límite máximo permitido de 29 caracteres."]);
            exit;
        }

        // 4. Validación numérica
        $num_puerta = filter_var($data['cent_num_puerta'], FILTER_VALIDATE_INT);
        $capacidad = filter_var($data['cent_capacidad'], FILTER_VALIDATE_INT);
        $llenado = filter_var($data['cent_llenado'], FILTER_VALIDATE_INT);

        if ($num_puerta === false || $num_puerta <= 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El número de puerta debe ser un entero mayor a cero."]);
            exit;
        }

        if ($capacidad === false || $capacidad <= 0 || $llenado === false || $llenado < 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: La capacidad y el llenado deben ser números válidos."]);
            exit;
        }

        if ($llenado > $capacidad) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El llenado inicial no puede ser mayor que la capacidad máxima."]);
            exit;
        }

        // 5. Validación de formato de Horas (HH:MM)
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $hora_apertura) || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $hora_cierre)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El formato de las horas no es válido."]);
            exit;
        }

        // Validación lógica: Cierre debe ser posterior a apertura
        if ($hora_cierre <= $hora_apertura) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: La hora de cierre debe ser posterior a la de apertura."]);
            exit;
        }

        // 6. Sanitizado de strings para la consulta SQL
        $calle = mysqli_real_escape_string($this->conn, $calle);
        $hora_apertura = mysqli_real_escape_string($this->conn, $hora_apertura);
        $hora_cierre = mysqli_real_escape_string($this->conn, $hora_cierre);

        try {
            // 7. INICIAMOS LA TRANSACCIÓN (Si algo falla, no se guarda nada)
            mysqli_begin_transaction($this->conn);

            // A. Insertamos en la tabla padre (establecimiento)
            $queryEstablecimiento = "INSERT INTO establecimiento (hora_apertura, hora_cierre, calle, num_puerta) 
                                    VALUES ('$hora_apertura', '$hora_cierre', '$calle', $num_puerta)";
            
            mysqli_query($this->conn, $queryEstablecimiento);
            
            // B. Capturamos el ID autogenerado
            $Id = mysqli_insert_id($this->conn);

            // C. Insertamos en la tabla hija (centro_acopio) usando el ID capturado
            $queryAcopio = "INSERT INTO centro_acopio (cent_a_id, cent_a_capacidad, cent_a_llenado) 
                            VALUES ($Id, $capacidad, $llenado)";
            
            mysqli_query($this->conn, $queryAcopio);

            // 8. CONFIRMAMOS LA TRANSACCIÓN (Se guardan los cambios en ambas tablas)
            mysqli_commit($this->conn);

            http_response_code(201); // 201 = Creado
            echo json_encode([
                "mensaje" => "Centro de Acopio registrado con éxito en '$calle $num_puerta'.",
                "cent_a_id" => $Id
            ]);
            exit;

        } catch (mysqli_sql_exception $e) {
            // 9. REVERTIMOS LA TRANSACCIÓN si hubo algún error en cualquier INSERT
            mysqli_rollback($this->conn);
            
            // 10. Interceptar error de duplicado (Restricción UNIQUE calle + num_puerta)
            // 1062 es el código de error estándar de MySQL para 'Duplicate entry'
            if ($e->getCode() == 1062) {
                http_response_code(409); // 409 = Conflicto
                echo json_encode(["mensaje" => "⚠️ Error: Ya existe un establecimiento registrado en esa misma calle y número de puerta."]);
                exit;
            }

            http_response_code(500); 
            echo json_encode(["mensaje" => "Error interno al guardar en el servidor: " . $e->getMessage()]);
            exit;
        }
    }

    public function modificarAcopio($data){
        // 1. Existencia de los datos
        if (
            !isset($data['cent_a_id']) ||
            empty($data['cent_calle']) || trim($data['cent_calle']) === "" ||
            !isset($data['cent_num_puerta']) ||
            empty($data['cent_hora_apertura']) || trim($data['cent_hora_apertura']) === "" ||
            empty($data['cent_hora_cierre']) || trim($data['cent_hora_cierre']) === "" ||
            !isset($data['cent_capacidad']) ||
            !isset($data['cent_llenado'])
        ) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: Todos los campos del centro de acopio son obligatorios."
            ]);
            exit;
        }

        // 2. Limpieza de strings
        $calle = trim($data['cent_calle']);
        $hora_apertura = trim($data['cent_hora_apertura']);
        $hora_cierre = trim($data['cent_hora_cierre']);

        // 3. Validar ID
        $id = filter_var($data['cent_a_id'], FILTER_VALIDATE_INT);
        
        if (!$this->getCentroAcopioById($id)) {
            http_response_code(404);
            echo json_encode([
                "mensaje" => "⚠️ Error: El centro de acopio con ID $id no existe en el sistema."
            ]);
            exit;
        }
        if ($id === false || $id <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: El ID del centro de acopio debe ser un entero mayor a cero."
            ]);
            exit;
        }

        

        // 4. Validar límite de calle
        if (mb_strlen($calle) > 29) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: La calle supera el límite máximo permitido de 29 caracteres."
            ]);
            exit;
        }

        // 5. Validación numérica
        $num_puerta = filter_var($data['cent_num_puerta'], FILTER_VALIDATE_INT);
        $capacidad = filter_var($data['cent_capacidad'], FILTER_VALIDATE_INT);
        $llenado = filter_var($data['cent_llenado'], FILTER_VALIDATE_INT);

        if ($num_puerta === false || $num_puerta <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: El número de puerta debe ser un entero mayor a cero."
            ]);
            exit;
        }

        if ($capacidad === false || $capacidad <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: La capacidad debe ser un número válido mayor a cero."
            ]);
            exit;
        }

        if ($llenado === false || $llenado < 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: El llenado debe ser un número válido igual o mayor a cero."
            ]);
            exit;
        }

        if ($llenado > $capacidad) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: El llenado no puede ser mayor que la capacidad máxima."
            ]);
            exit;
        }

        // 6. Validación de formato de horas
        if (
            !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $hora_cierre)
        ) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: El formato de las horas no es válido."
            ]);
            exit;
        }

        // Validación lógica: cierre posterior a apertura
        if ($hora_cierre <= $hora_apertura) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Error: La hora de cierre debe ser posterior a la de apertura."
            ]);
            exit;
        }

        // 7. Sanitizado de strings
        $calle = mysqli_real_escape_string($this->conn, $calle);
        $hora_apertura = mysqli_real_escape_string($this->conn, $hora_apertura);
        $hora_cierre = mysqli_real_escape_string($this->conn, $hora_cierre);

        try {

            // 8. Iniciamos la transacción
            mysqli_begin_transaction($this->conn);

            // A. Actualizamos establecimiento
            $queryEstablecimiento = "
                UPDATE establecimiento
                SET hora_apertura = '$hora_apertura',
                    hora_cierre = '$hora_cierre',
                    calle = '$calle',
                    num_puerta = $num_puerta
                WHERE estab_id = $id
            ";

            mysqli_query($this->conn, $queryEstablecimiento);

            // B. Actualizamos centro_acopio
            $queryAcopio = "
                UPDATE centro_acopio
                SET cent_a_capacidad = $capacidad,
                    cent_a_llenado = $llenado
                WHERE cent_a_id = $id
            ";

            mysqli_query($this->conn, $queryAcopio);

            // 9. Confirmamos la transacción
            mysqli_commit($this->conn);

            http_response_code(200);

            echo json_encode([
                "mensaje" => "Centro de Acopio modificado con éxito."
            ]);

            exit;

        } catch (mysqli_sql_exception $e) {

            // 10. Revertimos la transacción
            mysqli_rollback($this->conn);

            // Error por calle + número de puerta duplicados
            if ($e->getCode() == 1062) {
                http_response_code(409);
                echo json_encode([
                    "mensaje" => " Error: Ya existe un establecimiento registrado en esa misma calle y número de puerta."
                ]);
                exit;
            }

            http_response_code(500);

            echo json_encode([
                "mensaje" => "Error interno al modificar en el servidor: " . $e->getMessage()
            ]);

            exit;
        }
    }
    public function deleteCentroAcopioById($cent_a_id){
        // 1. Validación del ID
        $cent_a_id = filter_var($cent_a_id, FILTER_VALIDATE_INT);

        if ($cent_a_id === false || $cent_a_id <= 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El ID del centro de acopio no es válido."]);
            exit;
        }
        if (!$this->getCentroAcopioById($cent_a_id)) {
            http_response_code(404);
            echo json_encode(["mensaje" => "⚠️ Error: El centro de acopio número $cent_a_id no existe en el sistema."]);
            exit;
        }

        try {
            // 2. Baja lógica del centro de acopio
            $query = "UPDATE establecimiento 
                    SET estab_activo = false 
                    WHERE estab_id = $cent_a_id";

            mysqli_query($this->conn, $query);

            // 3. Verificamos si se realizó el cambio
            if (mysqli_affected_rows($this->conn) > 0) {
                http_response_code(200);
                echo json_encode([
                    "mensaje" => "Centro de Acopio #$cent_a_id eliminado con éxito."
                ]);
                exit;
            } else {
                http_response_code(404);
                echo json_encode([
                    "mensaje" => "⚠️ Error: El centro de acopio no existe o ya fue eliminado."
                ]);
                exit;
            }

        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            echo json_encode([
                "mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()
            ]);
            exit;
        }
    }
}