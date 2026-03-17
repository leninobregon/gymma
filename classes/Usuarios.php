<?php
class Usuario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lógica para extraer edad de la cédula (Formato: 001-250890-1001U)
    public function calcularEdad($cedula) {
        if (empty($cedula) || strlen($cedula) < 14) return "N/A";

        $partes = explode('-', $cedula);
        if (count($partes) < 2) return "N/A";

        $fechaCod = substr($partes[1], 0, 6); // Extrae 250890
        $dia = substr($fechaCod, 0, 2);
        $mes = substr($fechaCod, 2, 2);
        $anioCorto = substr($fechaCod, 4, 2);

        // Ajuste de siglo (2026 actual)
        $anioCompleto = ($anioCorto > date('y')) ? "19".$anioCorto : "20".$anioCorto;
        
        $fechaNacimiento = new DateTime("$anioCompleto-$mes-$dia");
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNacimiento);
        
        return $edad->y;
    }

    // CRUD: Crear Usuario (Solo ADMIN)
    public function crear($nombre, $apellido, $cedula, $pass, $tel, $rol) {
        $sql = "INSERT INTO usuarios (nombre, apellido, cedula, password, telefono, rol) 
                VALUES (:n, :a, :c, :p, :t, :r)";
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':n' => $nombre,
            ':a' => $apellido,
            ':c' => $cedula,
            ':p' => password_hash($pass, PASSWORD_DEFAULT),
            ':t' => $tel,
            ':r' => $rol
        ]);
    }
}