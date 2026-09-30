<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
//si sobra tiempo, añadir mapa al formulario de eliminación
class Contenedor
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }//nueva uncion con filtrado
    public function getAllContenedores()
    {
        $query = "SELECT * FROM contenedor";
        $result = mysqli_query($this->conn, $query);
        $contenedores = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $contenedores[] = $row;
        }
        return $contenedores;
    }

    // Obtener un contenedor por su ID (método GET, endpoint: /contenedores/<id>)
    public function getContenedorById($id)
    {
        // Limpiamos espacios
        $id = trim($id);

        // Validación de seguridad básica: que sea numérico
        if (empty($id) || !ctype_digit($id)) {
            return null;
        }

        $id = mysqli_real_escape_string($this->conn, $id);

        // Al ser un INT, en la query puede ir sin comillas, igual que la cédula
        $query = "SELECT * FROM contenedor WHERE cont_id = $id";
        $result = mysqli_query($this->conn, $query);
        $contenedor = mysqli_fetch_assoc($result);

        return $contenedor;
    }
    // Método para registrar un nuevo contenedor (método POST, endpoint: /contenedores)
    public function addContenedor($data, $municipio)
    {
        // 1.existencia, por lo tanto pensamiento
        if (
            empty($data['cont_calle']) || trim($data['cont_calle']) === "" ||
            empty($data['cont_tipo']) || trim($data['cont_tipo']) === "" ||
            empty($data['cont_estado']) || trim($data['cont_estado']) === "" ||
            !isset($data['cont_latitud']) || !isset($data['cont_longitud'])
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => " Error: Todos los campos (calle, tipo, estado y ubicación en mapa) son obligatorios."]);
            exit;
        }

        // 2. Limpieza de strings
        $cont_calle = trim($data['cont_calle']);
        $cont_tipo = trim($data['cont_tipo']);
        $cont_estado = trim($data['cont_estado']);

        // 3. Validar límite de tamaño de calle para VARCHAR(29), mb_strlengt>>>strlen ya que un tilde no suma
        if (mb_strlen($cont_calle) > 29) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: La ubicación de la calle supera el límite máximo permitido de 29 caracteres."]);
            exit;
        }

        // 4. Lista  para cont_tipo
        $tipos_validos = ['mezclados', 'reciclaje', 'volqueta'];
        if (!in_array($cont_tipo, $tipos_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El tipo de contenedor seleccionado no es válido."]);
            exit;
        }

        // 5. Lista  para cont_estado
        $estadosValidos = ['funcional', 'roto', 'desbordado', 'reserva'];
        if (!in_array($cont_estado, $estadosValidos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El estado ingresado no es válido para el sistema de gestión."]);
            exit;
        }

        // 6. Validación numérica y flotante de coordenadas
        $cont_latitud = filter_var($data['cont_latitud'], FILTER_VALIDATE_FLOAT);
        $cont_longitud = filter_var($data['cont_longitud'], FILTER_VALIDATE_FLOAT);

        if ($cont_latitud === false || $cont_longitud === false || $cont_latitud == 0 || $cont_longitud == 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: Debe seleccionar una ubicación válida en el mapa."]);
            exit;
        }

        //6.5 VALIDAR QUE LAS COORDENADAS ESTEN EN la jurisdiccion de pepe
        if (!$this->validarMunicipio($cont_latitud, $cont_longitud, $municipio)) {
            http_response_code(403);
            echo json_encode([
                "mensaje" => "⚠️ No puede registrar contenedores fuera de su municipio."
            ]);
            exit;
        }

        // 7. Sanitizado de strings para la consulta SQL
        $cont_calle = mysqli_real_escape_string($this->conn, $cont_calle);
        $cont_tipo = mysqli_real_escape_string($this->conn, $cont_tipo);
        $cont_estado = mysqli_real_escape_string($this->conn, $cont_estado);

        try {
            // 8. INSERT sin cont_id (MySQL genera la PK automáticamente)
            $query = "INSERT INTO contenedor (cont_calle, cont_tipo, cont_estado, cont_latitud, cont_longitud) 
                    VALUES ('$cont_calle', '$cont_tipo', '$cont_estado', $cont_latitud, $cont_longitud)";
            //flecha simple -> llama a un método
            mysqli_query($this->conn, $query);

            $nuevoId = mysqli_insert_id($this->conn);

            http_response_code(201); // 201 = Creado con éxito
            echo json_encode([
                //flecha doble => asigna un valor a la clave(objetos clave, valor)
                "mensaje" => "Contenedor #$nuevoId registrado con éxito.",
                "cont_id" => $nuevoId
            ]);
            exit;

        } catch (mysqli_sql_exception $e) {
            http_response_code(500); // Falla de base de datos
            echo json_encode(["mensaje" => "Error interno al guardar en el servidor: " . $e->getMessage()]);
            exit;
        }
    }

    public function modificarContenedor($data, $municipio)
    {
        // 1. Existencia, por lo tanto pensamiento
        if (
            empty($data['cont_estado']) || trim($data['cont_estado']) === "" ||
            empty($data['cont_tipo']) || trim($data['cont_tipo']) === "" ||
            empty($data['cont_id'])
        ) {
            http_response_code(400);
            echo json_encode(["mensaje" => " ID y estado son campos obligatorios."]);
            exit;
        }

        // 2. Limpieza 
        $cont_id = filter_var($data['cont_id'], FILTER_VALIDATE_INT);
        $cont_estado = trim($data['cont_estado']);
        $cont_tipo = trim($data['cont_tipo']);



        $contenedor = $this->getContenedorById($cont_id);

        if (!$contenedor) {
            http_response_code(404);
            echo json_encode(["mensaje" => "⚠️ Error: El contenedor #$cont_id no existe en el sistema."]);
            exit;
        }

        if (
            !$this->validarMunicipio(
                $contenedor['cont_latitud'],
                $contenedor['cont_longitud'],
                $municipio
            )
        ) {
            http_response_code(403);
            echo json_encode(["mensaje" => "⚠️ No puede modificar contenedores fuera de su municipio."]);
            exit;
        }
        // 3. Validar ID
        if ($cont_id === false || $cont_id <= 0) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El ID del contenedor no es válido."]);
            exit;
        }

        $tipos_validos = ['mezclados', 'reciclaje', 'volqueta'];
        if (!in_array($cont_tipo, $tipos_validos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El tipo de contenedor seleccionado no es válido."]);
            exit;
        }

        // 4. Lista blanca para cont_estado
        $estadosValidos = ['funcional', 'roto', 'desbordado', 'reserva'];
        if (!in_array($cont_estado, $estadosValidos, true)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "⚠️ Error: El estado ingresado no es válido para el sistema de gestión."]);
            exit;
        }

        // 5. Sanitizado de strings para la consulta SQL (Estado)
        $cont_estado = mysqli_real_escape_string($this->conn, $cont_estado);
        $cont_tipo = mysqli_real_escape_string($this->conn, $cont_tipo);


        // 6. Validaciones extra SOLO si se activó el switch de ubicación
        // Usamos ?? false para evitar el warning de "Undefined array key" si no viene la clave
        $modifica_ubicacion = $data['modifica_ubicacion'] ?? false;

        if ($modifica_ubicacion === true) {

            if (
                empty($data['cont_calle']) || trim($data['cont_calle']) === "" ||
                !isset($data['cont_latitud']) || !isset($data['cont_longitud'])
            ) {
                http_response_code(400);
                echo json_encode(["mensaje" => " Error: Todos los campos de ubicación son obligatorios."]);
                exit;
            }

            $cont_calle = trim($data['cont_calle']);

            // Validar límite de tamaño de calle para VARCHAR(29)
            if (mb_strlen($cont_calle) > 29) {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Error: La ubicación de la calle supera el límite máximo permitido de 29 caracteres."]);
                exit;
            }

            $cont_latitud = filter_var($data['cont_latitud'], FILTER_VALIDATE_FLOAT);
            $cont_longitud = filter_var($data['cont_longitud'], FILTER_VALIDATE_FLOAT);

            if (!$this->validarMunicipio($cont_latitud, $cont_longitud, $municipio)) {
                http_response_code(403);
                echo json_encode([
                    "mensaje" => "⚠️ La nueva ubicación se encuentra fuera de su municipio."
                ]);
                exit;
            }

            if ($cont_latitud === false || $cont_longitud === false || $cont_latitud == 0 || $cont_longitud == 0) {
                http_response_code(400);
                echo json_encode(["mensaje" => "⚠️ Error: Debe seleccionar una ubicación válida en el mapa."]);
                exit;
            }

            // Sanitizar calle para SQL
            $cont_calle = mysqli_real_escape_string($this->conn, $cont_calle);
        }

        // 7. Ejecución de la consulta SQL
        try {
            if ($modifica_ubicacion === true) {
                // Se actualiza el estado Y la ubicación
                $query = "UPDATE contenedor 
                        SET cont_estado = '$cont_estado', 
                            cont_tipo = '$cont_tipo',
                            cont_calle = '$cont_calle', 
                            cont_latitud = $cont_latitud, 
                            cont_longitud = $cont_longitud 
                        WHERE cont_id = $cont_id";
            } else {
                // ÚNICAMENTE se actualiza el estado (calle y coordenadas quedan intactas)
                $query = "UPDATE contenedor 
                        SET cont_estado = '$cont_estado' ,
                        cont_tipo = '$cont_tipo'
                        WHERE cont_id = $cont_id";
            }

            mysqli_query($this->conn, $query);

            http_response_code(200);
            echo json_encode([
                "mensaje" => "Contenedor #$cont_id actualizado con éxito."
            ]);
            exit;

        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error interno al guardar en el servidor: " . $e->getMessage()]);
            exit;
        }
    }
    public function deleteContenedorById($cont_id, $municipio)
    {
        // 1. Limpieza y validación para asegurarnos de que sea un número entero positivo
        $cont_id = filter_var($cont_id, FILTER_VALIDATE_INT);

        $contenedor = $this->getContenedorById($cont_id);

        if (!$contenedor) {
            http_response_code(404);
            echo json_encode([
                "mensaje" => "⚠️ Error: El contenedor no existe o ya fue eliminado."
            ]);
            exit;
        }

        if (!$this->validarMunicipio($contenedor['cont_latitud'],$contenedor['cont_longitud'],$municipio)) {
            http_response_code(403);
            echo json_encode([
                "mensaje" => "⚠️ No puede eliminar contenedores fuera de su municipio."
            ]);
            exit;
        }


        try {
            // Query para eliminar el contenedor de la base de datos
            $query = "UPDATE contenedor SET cont_activo = false WHERE cont_id = $cont_id";

            mysqli_query($this->conn, $query);

            // 2. Tu técnica de affected_rows entra en acción
            if (mysqli_affected_rows($this->conn) > 0) {
                http_response_code(200); // 200 significa É.X.I.T.O
                echo json_encode(["mensaje" => "Contenedor #$cont_id eliminado con éxito."]);
                exit;
            } else {
                // El query funcionó perfectamente, pero no borró nada porque el ID no estaba
                http_response_code(404); // Usamos 404 porque no se encontró el recurso
                echo json_encode(["mensaje" => "⚠️ Error: El contenedor no existe o ya fue eliminado."]);
                exit;
            }

        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error interno en el servidor municipal: " . $e->getMessage()]);
            exit;
        }
    }

    private function validarMunicipio($latitud, $longitud, $municipio)
    {
        $municipio = mysqli_real_escape_string($this->conn, $municipio);

        $query = "SELECT 
                p.poli_id,
                pv.pv_orden,
                pv.pv_latitud,
                pv.pv_longitud
              FROM poligono p
              INNER JOIN zona_circuito z ON p.zona_id = z.zona_id
              INNER JOIN poligono_vertice pv ON p.poli_id = pv.poli_id
              WHERE z.zona_municipio = '$municipio'
              AND z.zona_activa = true
              ORDER BY p.poli_id, pv.pv_orden";

        $result = mysqli_query($this->conn, $query);

        $poligonos = [];

        while ($row = mysqli_fetch_assoc($result)) {
            //checa si existe ya una clave [poli_id] en el diccionario $poligonos y sino le crea una
            if (!isset($poligonos[$row['poli_id']])) {
                $poligonos[$row['poli_id']] = [];
            }
            //mete en el poligono las x latitudes y x longitudes de sus x vertices 
            $poligonos[$row['poli_id']][] = [
                $row['pv_latitud'],
                $row['pv_longitud']
            ];
        }

        foreach ($poligonos as $coordenadas) {

            if ($this->puntoEstaEnPoligono($latitud, $longitud, $coordenadas)) {
                return true;
            }
        }

        return false;
    }

    private function puntoEstaEnPoligono($latitud, $longitud, $coordenadas)
    {
        $dentro = false;

        $cantidad = count($coordenadas);
        //ponele que haya 4 vertices, i es el primero(0) y anterior el último(3), anterior va siguiendo a i
//en un cuadrado de 4 vertices y 4 lados del A al D arrancamos con el  lado D-A
/*imaginad $coordenadas = [
    [-34.88, -56.15], vertice 0
    [-34.88, -56.10], vertice 1
    [-34.92, -56.10], vertice 2
    [-34.92, -56.15] vertice 3
];*/                                                    //anterior=actual, luego actual++
        for ($actual = 0, $anterior = $cantidad - 1; $actual < $cantidad; $anterior = $actual++) {
            //coordenadas del vertice A(0) traeme el valor en la posición 0(latitud)
            $lat_actual = $coordenadas[$actual][0];
            $lng_actual = $coordenadas[$actual][1];
            //coordenadas del vertice D(3), traeme el valor en la posición 0(latitud)
            $lat_anterior = $coordenadas[$anterior][0];
            $lng_anterior = $coordenadas[$anterior][1];

            if (
                    //el vertice A está arriba(mayor latitud) que el punto que ingresamos?
                    //¿el vertice B está arriba que el punto que ingresamos?
                    //queremos que esté entre ellos(dentro), si ambos están por arriba o ambos están por abajo
                    //el != nos dará false, que es lo que no queremos
                (($lat_actual > $latitud) != ($lat_anterior > $latitud)) &&
                ($longitud < ($lng_anterior - $lng_actual) * ($latitud - $lat_actual)
                             / ($lat_anterior - $lat_actual) + $lng_actual)
//la división(delta lng/delta lat) nos da la pendiente del lado(cuanto cambia la latitud al cambiar la longitud)
//queremos llegar a latitud desde latitud actual, por eso la resta, multiplicamos para saber cuanto cambiamos de latitud
//sumamos longitud actual porque desde ahí se arranca, con esto sabemos si el lado está a la derecha
//o a la izquierda del punto,
// la formula calcula dónde está la intersección del lado del polígono con la latitud del punto
            ) {
                $dentro = !$dentro;
            }
        }

        return $dentro;
    }


}