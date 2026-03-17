<?php
// Configuración de conexión inicial (sin DB seleccionada aún)
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "gym_db";

try {
    // 1. Conectar al servidor MySQL
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>🛠️ Proceso de Instalación GYM MA DB</h2>";

    // 2. Crear la Base de Datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$db_name`;");
    echo "✅ Base de datos '$db_name' lista.<br>";

    // 3. Crear Tabla de Usuarios
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

    // 4. Crear Tabla de Socios
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

    // 5. Crear Tabla de Membresías
    $sql_membresias = "CREATE TABLE IF NOT EXISTS membresias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_plan VARCHAR(50),
        precio_cor DECIMAL(10,2),
        precio_usd DECIMAL(10,2),
        duracion_dias INT
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_membresias);
    echo "✅ Tabla 'membresias' creada.<br>";

    // 6. Crear Tabla de Sesiones de Caja
    $sql_caja = "CREATE TABLE IF NOT EXISTS caja_sesiones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT,
        fecha_apertura DATETIME,
        monto_inicial DECIMAL(10,2),
        fecha_cierre DATETIME NULL,
        monto_final_real DECIMAL(10,2) NULL,
        estado ENUM('ABIERTA', 'CERRADA') DEFAULT 'ABIERTA',
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_caja);
    echo "✅ Tabla 'caja_sesiones' creada.<br>";

    // 7. Insertar Usuario Administrador por defecto
    // Usuario: admin | Clave: admin123
    $checkUser = $pdo->query("SELECT id FROM usuarios WHERE usuario = 'admin'");
    if ($checkUser->rowCount() == 0) {
        $pass_admin = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, usuario, password, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Super', 'Admin', 'admin', $pass_admin, 'ADMIN']);
        echo "<b>🚀 Usuario Administrador creado con éxito!</b><br>";
        echo "👤 Usuario: <b>admin</b><br>🔑 Clave: <b>admin123</b><br>";
    }

    echo "<br><div style='color: green; font-weight: bold;'>¡INSTALACIÓN COMPLETADA!</div>";
    echo "<p>Por seguridad, elimine este archivo (instalar.php) de su servidor.</p>";
    echo "<a href='views/login.php'>Ir al Login</a>";

} catch (PDOException $e) {
    die("❌ ERROR EN LA INSTALACIÓN: " . $e->getMessage());
}
?>