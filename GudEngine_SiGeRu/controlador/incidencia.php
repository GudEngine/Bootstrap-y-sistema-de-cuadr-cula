<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
//si sobra tiempo, añadir mapa al formulario de eliminación
class Incidencia
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getAllIncidencias()
    {
        //nadie tiene interés en saber quién registró una incidencia, por privacidad quité usr_ci
        $query = "SELECT inc_id, inc_municipio, inc_tema, inc_tipo_residuo, inc_calle, inc_puerta, inc_descripcion, inc_fecha_hora, inc_fecha_hora_cierre, inc_estado, inc_cuadrilla
        FROM incidencia";
        $result = mysqli_query($this->conn, $query);
        $incidencias = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $incidencias[] = $row;
        }
        return $incidencias;
    }
    public function getMisIncidencias($usr_ci)
    {
        //nadie tiene interés en saber quién registró una incidencia, por privacidad quité usr_ci
        $query = "SELECT inc_id, inc_tema, inc_estado, inc_descripcion, inc_fecha_hora, inc_fecha_hora_cierre
        FROM incidencia where usr_ci = $usr_ci";
        $result = mysqli_query($this->conn, $query);
        $incidencias = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $incidencias[] = $row;
        }
        return $incidencias;
    }

    public function getIncidenciaById($id)
    {
        // Limpiamos espacios
        $id = trim($id);

        // Validación de seguridad básica: que sea numérico
        if (empty($id) || !ctype_digit($id)) {
            return null;
        }

        $id = mysqli_real_escape_string($this->conn, $id);

        // Al ser un INT, en la query puede ir sin comillas, igual que la cédula, con esto ahorro 2 bytes
        $query = "SELECT * FROM incidencia WHERE inc_id = $id";
        $result = mysqli_query($this->conn, $query);
        $incidencia = mysqli_fetch_assoc($result);

        return $incidencia;
    }

    public function addIncidencia($data, $usr_ci)
    {
        if (
            !isset($data['inc_tema']) || empty(trim($data['inc_tema']))
            || !isset($data['inc_tipo_residuo']) || empty(trim($data['inc_tipo_residuo']))
            || !isset($data['inc_descripcion']) || empty(trim($data['inc_descripcion']))
            || !isset($data['inc_municipio']) || empty(trim($data['inc_municipio']))
            || !isset($data['inc_calle']) || empty(trim($data['inc_calle']))
            || !isset($data['inc_puerta']) || empty(trim($data['inc_puerta']))
        ) {

            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Todos los campos son obligatorios."
            ]);
            exit;
        }

        $inc_tema = trim($data['inc_tema']);
        $inc_tipo_residuo = trim($data['inc_tipo_residuo']);
        $inc_descripcion = trim($data['inc_descripcion']);
        $inc_municipio = trim($data['inc_municipio']);
        $inc_calle = trim($data['inc_calle']);
        $inc_puerta = trim($data['inc_puerta']);

        if (strlen($inc_tema) > 21) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El tema de la incidencia no es válido."
            ]);
            exit;
        }

        $temas_permitidos = ['Basura suelta', 'Mover contenedor', 'Contenedor desbordado'];

        if (!in_array($inc_tema, $temas_permitidos)) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El tema seleccionado no es válido."
            ]);
            exit;
        }

        $tipos_permitidos = ['mezclados', 'reciclaje', 'volqueta'];

        if (!in_array($inc_tipo_residuo, $tipos_permitidos)) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El tipo de contenedor/basura seleccionado no es válido."
            ]);
            exit;
        }

        if (strlen($inc_descripcion) > 500) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ La descripción no puede superar los 500 caracteres."
            ]);
            exit;
        }

        $municipios_permitidos = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'CH'];

        if (!in_array($inc_municipio, $municipios_permitidos)) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El municipio seleccionado no es válido."
            ]);
            exit;
        }

        if (strlen($inc_calle) > 29) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El nombre de la calle no puede superar los 29 caracteres."
            ]);
            exit;
        }

        if (empty($inc_puerta) || !ctype_digit($inc_puerta) || (int) $inc_puerta <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El número de puerta debe ser un número entero mayor a 0."
            ]);
            exit;
        }

        $inc_tema = mysqli_real_escape_string($this->conn, $inc_tema);
        $inc_tipo_residuo = mysqli_real_escape_string($this->conn, $inc_tipo_residuo);
        $inc_descripcion = mysqli_real_escape_string($this->conn, $inc_descripcion);
        $inc_municipio = mysqli_real_escape_string($this->conn, $inc_municipio);
        $inc_calle = mysqli_real_escape_string($this->conn, $inc_calle);

        $usr_ci = trim($usr_ci);

        if (empty($usr_ci) || !ctype_digit($usr_ci)) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Usuario inválido."
            ]);
            exit;
        }

        $query = "INSERT INTO incidencia (
                inc_tema, inc_tipo_residuo, inc_descripcion, inc_municipio, inc_calle, inc_puerta, usr_ci)
              VALUES ('$inc_tema', '$inc_tipo_residuo', '$inc_descripcion',
                '$inc_municipio', '$inc_calle', $inc_puerta, $usr_ci)";

        try {
            mysqli_query($this->conn, $query);
            http_response_code(201);
            echo json_encode(["mensaje" => "Incidencia registrada con éxito."]);

        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "⚠️ Error al registrar la incidencia."]);
        }
    }

    public function asignarCuadrilla($data, $camion)
    {
        if (
            !isset($data['inc_id']) || empty(trim($data['inc_id']))
            || !isset($data['inc_cuadrilla']) || empty(trim($data['inc_cuadrilla']))
        ) {

            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ Todos los campos son obligatorios."
            ]);
            exit;
        }

        $inc_id = trim($data['inc_id']);
        $inc_cuadrilla = trim($data['inc_cuadrilla']);

        if (!ctype_digit($inc_id) || (int) $inc_id <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El número de incidencia no es válido."
            ]);
            exit;
        }

        if (!ctype_digit($inc_cuadrilla) || (int) $inc_cuadrilla <= 0) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El número de cuadrilla no es válido."
            ]);
            exit;
        }

        if (!$camion) {
            http_response_code(404);
            echo json_encode([
                "mensaje" => "⚠️ No se encontró el camión de la cuadrilla."
            ]);
            exit;
        }

        /* $query = "SELECT * FROM incidencia WHERE inc_id = $inc_id";
         $result = mysqli_query($this->conn, $query);
         $incidencia = mysqli_fetch_assoc($result);

         if (!$incidencia) {
             http_response_code(404);
             echo json_encode([
                 "mensaje" => "⚠️ La incidencia seleccionada no existe."
             ]);
             exit;
         }*/

        $incidencia = $this->getIncidenciaById($inc_id);


        if (!$incidencia) {

            http_response_code(404);
            echo json_encode(["mensaje" => "⚠️ Error: La incidencia #$inc_id no existe en el sistema."]);
            exit;
        }

        if ($incidencia['inc_estado'] !== 'abierta') {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ La incidencia seleccionada no está abierta."
            ]);
            exit;
        }

        if ($incidencia['inc_tipo_residuo'] !== $camion['cam_tipo']) {
            http_response_code(400);
            echo json_encode([
                "mensaje" => "⚠️ El tipo de residuo de la incidencia no corresponde al camión de la cuadrilla."
            ]);
            exit;
        }

        $query = "UPDATE incidencia
              SET inc_cuadrilla = $inc_cuadrilla,
                  inc_estado = 'espera'
              WHERE inc_id = $inc_id";

        try {

            mysqli_query($this->conn, $query);

            http_response_code(200);
            echo json_encode([
                "mensaje" => "Incidencia asignada con éxito."
            ]);

        } catch (mysqli_sql_exception $e) {

            http_response_code(500);
            echo json_encode([
                "mensaje" => "⚠️ Error al asignar la cuadrilla."
            ]);
        }
    }

    public function rechazarIncidencia($data)
    {
        if (!isset($data['inc_id']) || !isset($data['inc_descripcion'])) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Faltan datos obligatorios."]);
            exit;
        }

        $inc_id = trim($data['inc_id']);
        $inc_descripcion = trim($data['inc_descripcion']);

        if ($inc_id === '' || !ctype_digit($inc_id)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ El ID de la incidencia no es válido."]);
            exit;
        }

        if ($inc_descripcion === '') {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Debe ingresar un motivo para rechazar la incidencia."]);
            exit;
        }

        $inc_id = mysqli_real_escape_string($this->conn, $inc_id);
        $inc_descripcion = mysqli_real_escape_string($this->conn, $inc_descripcion);

        $query = "SELECT inc_estado FROM incidencia WHERE inc_id = $inc_id";

        try {
            $result = mysqli_query($this->conn, $query);

            if (mysqli_num_rows($result) === 0) {
                http_response_code(404);
                echo json_encode(["mensaje" => "⚠️ La incidencia no existe."]);
                exit;
            }

            $incidencia = mysqli_fetch_assoc($result);

            if ($incidencia['inc_estado'] !== 'abierta') {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Solo se pueden rechazar incidencias abiertas."]);
                exit;
            }

            $query = "UPDATE incidencia 
                  SET inc_estado = 'cerrada',
                      inc_descripcion = '$inc_descripcion'
                  WHERE inc_id = $inc_id";

            mysqli_query($this->conn, $query);

            http_response_code(200);
            echo json_encode(["mensaje" => "Incidencia rechazada correctamente."]);

        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "⚠️ Error al rechazar la incidencia."]);
            exit;
        }
    }

}