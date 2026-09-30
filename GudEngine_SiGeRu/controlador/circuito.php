<?php

class Circuito
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getCircuitosByMunicipio($municipio)
    {
        $municipio = trim($municipio);

        if (empty($municipio)) {
            return [];
        }

        $municipio = mysqli_real_escape_string($this->conn, $municipio);

        $query = "SELECT 
                    c.circ_id,
                    c.circ_tipo,
                    z.zona_id,
                    z.zona_cod,
                    z.zona_municipio,
                    p.poli_id,
                    pv.pv_orden,
                    pv.pv_latitud,
                    pv.pv_longitud
                  FROM circuito c
                  INNER JOIN zona_circuito z ON c.zona_id = z.zona_id
                  INNER JOIN poligono p ON z.zona_id = p.zona_id
                  INNER JOIN poligono_vertice pv ON p.poli_id = pv.poli_id
                  WHERE z.zona_municipio = '$municipio'
                  AND c.circ_activo = true
                  AND z.zona_activa = true
                  ORDER BY c.circ_id, p.poli_id, pv.pv_orden";

        $result = mysqli_query($this->conn, $query);

        $circuitos = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }

        return $circuitos;
    }

    public function getAllCircuitos()
    {

        $query = "SELECT 
                    c.circ_id,
                    c.circ_tipo,
                    z.zona_id,
                    z.zona_cod,
                    z.zona_municipio,
                    p.poli_id,
                    pv.pv_orden,
                    pv.pv_latitud,
                    pv.pv_longitud
                  FROM circuito c
                  INNER JOIN zona_circuito z ON c.zona_id = z.zona_id
                  INNER JOIN poligono p ON z.zona_id = p.zona_id
                  INNER JOIN poligono_vertice pv ON p.poli_id = pv.poli_id
                  WHERE z.zona_activa = true
                  AND c.circ_activo = true 
                  ORDER BY c.circ_id, p.poli_id, pv.pv_orden";

        $result = mysqli_query($this->conn, $query);

        $circuitos = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }

        return $circuitos;
    }
}