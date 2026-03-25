<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($usuario, $password) {
        $query = "SELECT id, nombre, apellido, usuario, cedula, password, telefono, rol, fecha_creacion, 
                  IFNULL(two_factor_enabled, 0) as two_factor_enabled, two_factor_secret 
                  FROM " . $this->table_name . " WHERE usuario = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function listarUsuarios() {
        $query = "SELECT id, nombre, apellido, usuario, cedula, telefono, rol, fecha_creacion FROM " . $this->table_name . " ORDER BY id DESC";
        return $this->conn->query($query);
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrar($datos) {
        $query = "INSERT INTO " . $this->table_name . " (nombre, apellido, usuario, cedula, password, telefono, rol) 
                  VALUES (:nom, :ape, :usu, :ced, :pass, :tel, :rol)";
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($datos['password'], PASSWORD_BCRYPT);
        
        return $stmt->execute([
            ':nom'  => $datos['nombre'],
            ':ape'  => $datos['apellido'],
            ':usu'  => $datos['usuario'],
            ':ced'  => $datos['cedula'],
            ':pass' => $password_hash,
            ':tel'  => $datos['telefono'],
            ':rol'  => $datos['rol']
        ]);
    }

    public function actualizar($datos) {
        if (!empty($datos['password'])) {
            $query = "UPDATE " . $this->table_name . " 
                      SET nombre=:nom, apellido=:ape, usuario=:usu, cedula=:ced, telefono=:tel, rol=:rol, password=:pass 
                      WHERE id=:id";
            $password_hash = password_hash($datos['password'], PASSWORD_BCRYPT);
            $params = [
                ':nom'  => $datos['nombre'], ':ape' => $datos['apellido'], ':usu' => $datos['usuario'],
                ':ced'  => $datos['cedula'], ':tel' => $datos['telefono'], ':rol' => $datos['rol'],
                ':pass' => $password_hash, ':id' => $datos['id']
            ];
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET nombre=:nom, apellido=:ape, usuario=:usu, cedula=:ced, telefono=:tel, rol=:rol 
                      WHERE id=:id";
            $params = [
                ':nom'  => $datos['nombre'], ':ape' => $datos['apellido'], ':usu' => $datos['usuario'],
                ':ced'  => $datos['cedula'], ':tel' => $datos['telefono'], ':rol' => $datos['rol'],
                ':id' => $datos['id']
            ];
        }
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}