<?php
/**
 * INSTALADOR MAESTRO AUTO-CONFIGURABLE
 * Compatible con XAMPP y Debian (LAMPP)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- 1. CONFIGURACIÓN DE ACCESO ---
$host       = "localhost";
$db_name    = "gym_ma_db"; //
$admin_user = "root";     // Usuario para entrar al sistema root o el que tengas hablitado
$admin_pass = "pass";  // Contraseña para entrar al sistema 

// Credenciales para el motor de base de datos
$user_db = "root"; 
$pass_db = ""; 

echo "<html><body style='font-family:Arial; background:#f0f2f5; padding:30px;'>";
echo "<div style='max-width:600px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1);'>";
echo "<h2 style='color:#1a73e8; text-align:center;'>🏋️ GIMNASIO SPARTAN - Instalador</h2><hr>";

try {
    // 2. CONEXIÓN INICIAL A MYSQL
    try {
        $pdo = new PDO("mysql:host=$host", $user_db, $pass_db);
    } catch (PDOException $e) {
        $pass_db = "root"; // Reintento común en configuraciones Linux
        $pdo = new PDO("mysql:host=$host", $user_db, $pass_db);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. CREAR BASE DE DATOS
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $pdo->exec("USE `$db_name`;");
    echo "✅ Base de datos <b>$db_name</b> lista.<br>";

    // 4. ESTRUCTURA COMPLETA (Basada en tu archivo SQL)
    $sql_estructura = "
    CREATE TABLE IF NOT EXISTS `configuracion` (
      `id` int(11) NOT NULL DEFAULT 1 PRIMARY KEY,
      `nombre_gym` varchar(100) DEFAULT 'GIMNASIO SPARTAN',
      `moneda_simbolo` varchar(5) DEFAULT 'C$',
      `tipo_cambio_bcn` decimal(10,4) DEFAULT 36.6243,
      `direccion_gym` text
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `usuarios` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `nombre` varchar(50) NOT NULL,
      `apellido` varchar(50) NOT NULL,
      `usuario` varchar(50) NOT NULL UNIQUE,
      `cedula` varchar(20) NOT NULL UNIQUE,
      `password` varchar(255) NOT NULL,
      `rol` enum('ADMIN','CAJA') NOT NULL DEFAULT 'CAJA'
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `planes` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `nombre_plan` varchar(100) NOT NULL,
      `duracion_dias` int(11) NOT NULL,
      `precio` decimal(10,2) NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `inventario` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `descripcion` varchar(150) NOT NULL,
      `precio` decimal(10,2) NOT NULL,
      `cantidad` int(11) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB;
    ";

    $pdo->exec($sql_estructura);
    echo "✅ Tablas de sistema e inventario creadas.<br>";

    // 5. INSERTAR DATOS INICIALES (Spartan Gym Nicaragua)
    $pdo->exec("INSERT IGNORE INTO `configuracion` (id, nombre_gym, moneda_simbolo, tipo_cambio_bcn, direccion_gym) 
                VALUES (1, 'GIMNASIO SPARTAN', 'C$', 36.6243, 'Managua, Nicaragua');"); //

    $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios (nombre, apellido, usuario, cedula, password, rol) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'Spartan', $admin_user, '001-000000-0000A', $hash, 'ADMIN']);
    echo "✅ Usuario administrador configurado correctamente.<br>";

    // 6. ACTUALIZAR AUTOMÁTICAMENTE TU CLASE DATABASE.PHP
    // Esto evita el Error 500 al sincronizar las credenciales
    $db_class_file = "Database.php"; 
    $db_content = "<?php
class Database {
    private \$host = '$host';
    private \$db_name = '$db_name';
    private \$username = '$user_db';
    private \$password = '$pass_db';
    public \$conn;

    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, \$this->username, \$this->password);
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->exec(\"set names utf8\");
        } catch(PDOException \$exception) {
            echo \"Error de conexión: \" . \$exception->getMessage();
        }
        return \$this->conn;
    }
}
?>";

    file_put_contents($db_class_file, $db_content);
    echo "✅ Archivo <b>Database.php</b> actualizado con éxito.<br>";

    echo "<div style='background:#d4edda; color:#155724; padding:15px; margin-top:20px; border-radius:5px;'>";
    echo "<b>¡TODO LISTO PARA SPARTAN GYM!</b><br>";
    echo "Acceso al sistema: <b>$admin_user</b> / <b>$admin_pass</b>";
    echo "<br><br><a href='views/login.php' style='color:white; background:#1a73e8; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block;'>Ir al Login</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='color:red; font-weight:bold; margin-top:20px;'>❌ ERROR CRÍTICO:</div>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Si estás en Debian, ejecuta esto en tu terminal para habilitar el acceso:</p>";
    echo "<code>sudo mysql -u root -e \"ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY ''; FLUSH PRIVILEGES;\"</code>";
}

echo "</div></body></html>";
?>














