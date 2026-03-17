<?php
/**
 * ARCHIVO: instalar.php
 * PROYECTO: GYM MA DB
 * DESCRIPCIÓN: Instalador automático de base de datos y tablas.
 */

// 1. Configuración de conexión
$host    = "localhost";
$user    = "root";
$pass    = "";
$db_name = "gym_ma_db"; // Nombre corregido solicitado

try {
    // Conectar al servidor MySQL (sin DB seleccionada aún)
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; border: 1px solid #ccc; padding: 20px; border-radius: 8px;'>";
    echo "<h2 style='color: #2c3e50; text-align: center;'>🛠️ Instalación GYM MA DB</h2><hr>";

    // 2. Crear la Base de Datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$db_name`;");
    echo "✅ Base de datos <b>'$db_name'</b> lista.<br>";

    // 3. Tabla de Usuarios (Administradores y Cajeros)
    $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        apellido VARCHAR(50) NOT NULL,
        usuario VARCHAR(30) NOT NULL UNIQUE,
        cedula VARCHAR(20) UNIQUE,
        telefono VARCHAR(15),
        password VARCHAR(255) NOT NULL,
        rol ENUM('ADMIN', 'CAJA') DEFAULT 'CAJA',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_usuarios);
    echo "✅ Tabla 'usuarios' creada.<br>";

    // 4. Tabla de Socios (Clientes del gimnasio)
    $sql_socios = "CREATE TABLE IF NOT EXISTS socios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(60) NOT NULL,
        apellido VARCHAR(60) NOT NULL,
        cedula VARCHAR(20) UNIQUE,
        telefono VARCHAR(15),
        fecha_registro DATE,
        estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO'
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_socios);
    echo "✅ Tabla 'socios' creada.<br>";

    // 5. Tabla de Membresías (Catálogo de planes)
    $sql_membresias = "CREATE TABLE IF NOT EXISTS membresias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_plan VARCHAR(50) NOT NULL,
        precio_cor DECIMAL(10,2) NOT NULL,
        precio_usd DECIMAL(10,2) NOT NULL,
        duracion_dias INT NOT NULL
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_membresias);
    echo "✅ Tabla 'membresias' creada.<br>";

    // 6. Tabla de Suscripciones (Relaciona socios con membresías y vencimientos)
    $sql_suscripciones = "CREATE TABLE IF NOT EXISTS suscripciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        socio_id INT NOT NULL,
        membresia_id INT NOT NULL,
        usuario_id INT NOT NULL, -- Quién realizó la venta
        fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_vencimiento DATE NOT NULL,
        monto_pagado DECIMAL(10,2) NOT NULL,
        moneda ENUM('COR', 'USD') DEFAULT 'COR',
        FOREIGN KEY (socio_id) REFERENCES socios(id) ON DELETE CASCADE,
        FOREIGN KEY (membresia_id) REFERENCES membresias(id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_suscripciones);
    echo "✅ Tabla 'suscripciones' creada.<br>";

    // 7. Tabla de Sesiones de Caja
    $sql_caja = "CREATE TABLE IF NOT EXISTS caja_sesiones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        fecha_apertura DATETIME NOT NULL,
        monto_inicial DECIMAL(10,2) NOT NULL,
        fecha_cierre DATETIME NULL,
        monto_final_real DECIMAL(10,2) NULL,
        estado ENUM('ABIERTA', 'CERRADA') DEFAULT 'ABIERTA',
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_caja);
    echo "✅ Tabla 'caja_sesiones' creada.<br>";

    // 8. Insertar Usuario Administrador por defecto
    // Usuario: admin | Clave: admin123
    $checkUser = $pdo->query("SELECT id FROM usuarios WHERE usuario = 'admin'");
    if ($checkUser->rowCount() == 0) {
        $pass_admin = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, usuario, password, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Super', 'Admin', 'admin', $pass_admin, 'ADMIN']);
        
        echo "<div style='background: #e8f5e9; border-left: 5px solid #4caf50; padding: 10px; margin-top: 15px;'>";
        echo "🚀 <b>Usuario Administrador creado:</b><br>";
        echo "👤 Usuario: <code>admin</code><br>🔑 Clave: <code>admin123</code>";
        echo "</div>";
    }

    echo "<br><div style='color: #2e7d32; font-weight: bold; text-align: center; font-size: 1.2em;'>¡INSTALACIÓN COMPLETADA EXITOSAMENTE!</div>";
    echo "<p style='color: #c62828; font-size: 0.9em; text-align: center;'>⚠️ <b>IMPORTANTE:</b> Elimine este archivo (<code>instalar.php</code>) por seguridad.</p>";
    echo "<div style='text-align: center; margin-top: 20px;'><a href='views/login.php' style='background: #2c3e50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Login</a></div>";
    echo "</div>";

} catch (PDOException $e) {
    die("<div style='color: red; padding: 20px;'>❌ ERROR EN LA INSTALACIÓN: " . $e->getMessage() . "</div>");
}
?>
