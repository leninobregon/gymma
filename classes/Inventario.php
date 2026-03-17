<?php
class Inventario {
    private $conn;
    private $table_name = "inventario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // [CREATE] Crear Producto
    public function crear($descripcion, $precio, $cantidad) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET descripcion = :desc, precio = :pre, cantidad = :cant";
        
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':desc' => $descripcion,
            ':pre'  => $precio,
            ':cant' => $cantidad
        ]);
    }

    // [READ] Leer Inventario Completo
    public function leerTodo() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY descripcion ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // --- NUEVO: Obtener un solo producto por ID ---
    // Esto es lo que usa editar_producto.php para mostrar los datos actuales
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- MEJORADO: Actualizar Producto Completo ---
    // Ahora permite editar nombre, precio y cantidad al mismo tiempo
    public function actualizar($id, $descripcion, $precio, $cantidad) {
        $query = "UPDATE " . $this->table_name . " 
                  SET descripcion = :desc, precio = :pre, cantidad = :cant 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':desc' => $descripcion,
            ':pre'  => $precio,
            ':cant' => $cantidad,
            ':id'   => $id
        ]);
    }

    // [DELETE] Eliminar producto
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Mantener por compatibilidad si lo usas en otros lados
    public function actualizarStock($id, $nueva_cantidad) {
        $query = "UPDATE " . $this->table_name . " SET cantidad = :cant WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':cant' => $nueva_cantidad, ':id' => $id]);
    }
}