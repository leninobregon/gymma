<?php
class Plan {
    private $conn;
    private $table = "planes";

    public function __construct($db) { $this->conn = $db; }

    public function crear($nombre, $dias, $precio) {
        $query = "INSERT INTO " . $this->table . " (nombre_plan, duracion_dias, precio) VALUES (?, ?, ?)";
        return $this->conn->prepare($query)->execute([$nombre, $dias, $precio]);
    }

    public function listarTodo() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY precio ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $nombre, $dias, $precio) {
        $query = "UPDATE " . $this->table . " SET nombre_plan = ?, duracion_dias = ?, precio = ? WHERE id = ?";
        return $this->conn->prepare($query)->execute([$nombre, $dias, $precio, $id]);
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        return $this->conn->prepare($query)->execute([$id]);
    }
}