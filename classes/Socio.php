<?php
class Socio {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listarSocios() {
        // Obtenemos todos los datos y calculamos días restantes para el semáforo
        $query = "SELECT *, DATEDIFF(fecha_vencimiento, CURDATE()) as dias_restantes FROM socios ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function registrar($datos, $foto_ruta) {
        try {
            // Si hay edad manual se usa, si no, se calcula por cédula
            $edadFinal = (!empty($datos['edad'])) ? intval($datos['edad']) : $this->calcularEdadDesdeCedula($datos['cedula'] ?? '');

            $query = "INSERT INTO socios (nombre, apellido, cedula, edad, telefono, enfermedad, emergencia_contacto, foto_ruta, fecha_ingreso) 
                      VALUES (:nom, :ape, :ced, :edad, :tel, :enf, :eme, :foto, NOW())";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':nom'  => $datos['nombre'],
                ':ape'  => $datos['apellido'],
                ':ced'  => $datos['cedula'],
                ':edad' => $edadFinal,
                ':tel'  => $datos['telefono'],
                ':enf'  => $datos['enfermedad'],
                ':eme'  => $datos['emergencia_contacto'],
                ':foto' => $foto_ruta
            ]);
        } catch (PDOException $e) { 
            return false; 
        }
    }

    public function actualizar($datos) {
        try {
            $query = "UPDATE socios SET 
                        nombre=:nom, 
                        apellido=:ape, 
                        cedula=:ced, 
                        edad=:edad,
                        telefono=:tel, 
                        enfermedad=:enf,
                        emergencia_contacto=:eme 
                      WHERE id=:id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':nom'  => $datos['nombre'],
                ':ape'  => $datos['apellido'],
                ':ced'  => $datos['cedula'],
                ':edad' => $datos['edad'],
                ':tel'  => $datos['telefono'],
                ':enf'  => $datos['enfermedad'],
                ':eme'  => $datos['emergencia_contacto'],
                ':id'   => $datos['id']
            ]);
        } catch (PDOException $e) { 
            return false; 
        }
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM socios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function calcularEdadDesdeCedula($cedula) {
        $cedula = str_replace('-', '', trim($cedula));
        if (strlen($cedula) < 14) return 0;
        $fechaCod = substr($cedula, 3, 6);
        $d = substr($fechaCod, 0, 2); 
        $m = substr($fechaCod, 2, 2); 
        $a = substr($fechaCod, 4, 2);
        $anioFull = ($a > date('y')) ? "19".$a : "20".$a;
        try {
            $nacimiento = new DateTime("$anioFull-$m-$d");
            $hoy = new DateTime();
            return $hoy->diff($nacimiento)->y;
        } catch (Exception $e) { 
            return 0; 
        }
    }
}