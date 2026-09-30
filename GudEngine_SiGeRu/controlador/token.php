<?php
/* Clase Token para gestionar los tokens de sesión
 * Requiere conexión a una base de datos MySQL
 */

// Configuración del reporte de errores
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class Token
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Verifica si un token existe y no está vencido
    public function validarToken($token)
    {
        $query = "SELECT * FROM access_token WHERE token = '$token' AND fecha_vencimiento > NOW()";

        $result = mysqli_query($this->conn, $query);

        if (mysqli_num_rows($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function obtenerUsuarioPorToken($token){
        $query = "SELECT usuario.usr_ci, usuario.usr_rol, usuario.usr_name, usuario.usr_municipio
                FROM access_token
                INNER JOIN usuario ON access_token.usr_ci = usuario.usr_ci
                WHERE access_token.token = '$token'
                AND access_token.fecha_vencimiento > NOW()";

        $result = mysqli_query($this->conn, $query);
        $usuario = mysqli_fetch_assoc($result);

        return $usuario;
    }
}