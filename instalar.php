<?php
/**
 * INSTALADOR AUTOMÁTICO - SISTEMA GYM MA DB
 * Compatible con XAMPP, LAMP y LEMP
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$db_name = "gym_ma_db";
$admin_user = "admin";
$admin_pass = "admin123";

$user_db = "root";
$pass_db = "";

echo "<html><head><meta charset='UTF-8'><title>Instalador - GYM MA DB</title></head>";
echo "<body style='font-family:Arial; background:#1a1a2e; padding:30px; color:#eee;'>";
echo "<div style='max-width:700px; margin:auto; background:#16213e; padding:25px; border-radius:15px; box-shadow:0 8px 32px rgba(0,0,0,0.3);'>";
echo "<h2 style='color:#e94560; text-align:center;'>🏋️ INSTALADOR - GYM MA DB</h2><hr style='border-color:#0f3460;'>";

try {
    try {
        $pdo = new PDO("mysql:host=$host", $user_db, $pass_db);
    } catch (PDOException $e) {
        $pass_db = "root";
        $pdo = new PDO("mysql:host=$host", $user_db, $pass_db);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $pdo->exec("USE `$db_name`;");
    echo "<p style='color:#4ecca3;'>✅ Base de datos <b>$db_name</b> creada.</p>";

    $sql = "
    CREATE TABLE `configuracion` (
      `id` int(11) NOT NULL DEFAULT 1,
      `nombre_gym` varchar(100) DEFAULT 'GYM MA DB',
      `moneda_nombre` varchar(50) DEFAULT 'Córdoba Nicaragüense',
      `moneda_iso` varchar(3) DEFAULT 'NIO',
      `moneda_simbolo` varchar(5) DEFAULT 'C\$',
      `tipo_cambio_bcn` decimal(10,4) DEFAULT 36.6243,
      `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      `direccion_gym` text DEFAULT NULL,
      `telefono_gym` varchar(20) DEFAULT NULL,
      `logo_ruta` varchar(255) DEFAULT 'logo_default.png',
      `tema` varchar(20) DEFAULT 'default',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `usuarios` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `nombre` varchar(50) NOT NULL,
      `apellido` varchar(50) NOT NULL,
      `usuario` varchar(50) NOT NULL,
      `cedula` varchar(20) NOT NULL,
      `password` varchar(255) NOT NULL,
      `two_factor_secret` varchar(255) DEFAULT NULL,
      `two_factor_enabled` tinyint(1) DEFAULT 0,
      `two_factor_code` varchar(10) DEFAULT NULL,
      `two_factor_expires` datetime DEFAULT NULL,
      `telefono` varchar(20) DEFAULT NULL,
      `rol` enum('ADMIN','CAJA') NOT NULL DEFAULT 'CAJA',
      `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
      `two_factor_pin` varchar(10) DEFAULT NULL,
      UNIQUE KEY `cedula` (`cedula`),
      UNIQUE KEY `usuario` (`usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `planes` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `nombre_plan` varchar(100) NOT NULL,
      `duracion_dias` int(11) NOT NULL,
      `precio` decimal(10,2) NOT NULL,
      `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `socios` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `nombre` varchar(50) NOT NULL,
      `apellido` varchar(50) NOT NULL,
      `cedula` varchar(20) DEFAULT NULL,
      `edad` int(3) DEFAULT NULL,
      `telefono` varchar(20) DEFAULT NULL,
      `enfermedad` text DEFAULT NULL,
      `fecha_ingreso` date NOT NULL,
      `emergencia_contacto` varchar(100) DEFAULT NULL,
      `foto_ruta` varchar(255) DEFAULT 'default.png',
      `estado` enum('ACTIVO','INACTIVO','DEUDOR') DEFAULT 'ACTIVO',
      `fecha_vencimiento` date DEFAULT NULL,
      `fecha_renovacion` date DEFAULT NULL,
      `id_plan` int(11) DEFAULT NULL,
      KEY `fk_socio_plan` (`id_plan`),
      KEY `fk_socio_plan_gym` (`id_plan`),
      CONSTRAINT `fk_socio_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id`) ON DELETE SET NULL,
      CONSTRAINT `fk_socio_plan_gym` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `cajas` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id_usuario` int(11) NOT NULL,
      `fecha_apertura` datetime DEFAULT current_timestamp(),
      `fecha_cierre` datetime DEFAULT NULL,
      `monto_apertura` decimal(10,2) NOT NULL,
      `monto_apertura_usd` decimal(10,2) DEFAULT 0.00,
      `monto_cierre` decimal(10,2) DEFAULT 0.00,
      `monto_cierre_usd` decimal(10,2) DEFAULT 0.00,
      `monto_esperado` decimal(10,2) DEFAULT 0.00,
      `estado` enum('ABIERTA','CERRADA') DEFAULT 'ABIERTA',
      `tasa_apertura` decimal(10,4) DEFAULT 36.6243,
      `nota` text DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `ventas` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id_usuario` int(11) NOT NULL,
      `id_caja` int(11) DEFAULT NULL,
      `id_socio` int(11) DEFAULT NULL,
      `monto_total` decimal(10,2) NOT NULL,
      `tasa_cambio_momento` decimal(10,4) DEFAULT 36.6243,
      `moneda_original` varchar(3) DEFAULT 'NIO',
      `concepto` varchar(255) NOT NULL,
      `cantidad` int(11) DEFAULT 1,
      `tipo_item` enum('PLAN','PRODUCTO') NOT NULL,
      `id_item_referencia` int(11) DEFAULT NULL,
      `cantidad_item` int(11) DEFAULT 1,
      `metodo_pago` enum('EFECTIVO','TRANSFERENCIA') DEFAULT 'EFECTIVO',
      `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
      `estado` varchar(20) DEFAULT 'COMPLETADO'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `inventario` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `descripcion` varchar(150) NOT NULL,
      `precio` decimal(10,2) NOT NULL,
      `cantidad` int(11) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `caja_egresos` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `descripcion` varchar(255) NOT NULL,
      `monto_salida` decimal(10,2) NOT NULL,
      `fecha_egreso` datetime DEFAULT current_timestamp(),
      `id_usuario` int(11) NOT NULL,
      `categoria` varchar(50) DEFAULT 'GENERAL'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    $pdo->exec($sql);
    echo "<p style='color:#4ecca3;'>✅ Todas las tablas creadas correctamente.</p>";

    $pdo->exec("INSERT INTO configuracion (id, nombre_gym, moneda_nombre, moneda_iso, moneda_simbolo, tipo_cambio_bcn, direccion_gym, telefono_gym, tema) 
                VALUES (1, 'GIMNASIO SPARTANS', 'Córdoba Nicaragüense', 'NIO', 'C\$', 36.6243, 'Managua, Nicaragua', '88888888', 'default')");

    $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
    $pdo->exec("INSERT INTO usuarios (nombre, apellido, usuario, cedula, password, telefono, rol) 
                VALUES ('Administrador', 'General', '$admin_user', '001-000000-0000A', '$hash', '88888888', 'ADMIN')");

    $pdo->exec("INSERT INTO planes (nombre_plan, duracion_dias, precio, estado) VALUES ('Mensualidad Pesas', 30, 300.00, 'ACTIVO')");

    echo "<p style='color:#4ecca3;'>✅ Datos iniciales insertados.</p>";

    $db_class = '<?php
class Database {
    private $host = "localhost";
    private $db_name = "gym_ma_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>';
    file_put_contents("config/Database.php", $db_class);
    echo "<p style='color:#4ecca3;'>✅ Archivo Database.php actualizado.</p>";

    echo "<div style='background:#0f3460; padding:20px; margin-top:20px; border-radius:10px; text-align:center;'>";
    echo "<h3 style='color:#4ecca3;'>🎉 ¡INSTALACIÓN COMPLETA!</h3>";
    echo "<p>Usuario: <b style='color:#e94560;'>$admin_user</b></p>";
    echo "<p>Contraseña: <b style='color:#e94560;'>$admin_pass</b></p>";
    echo "<br><a href='login.php' style='color:#fff; background:#e94560; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold;'>IR AL SISTEMA</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='color:#e94560; padding:15px; background:#2d132c; border-radius:8px;'>";
    echo "<b>❌ ERROR:</b> " . $e->getMessage();
    echo "<p style='margin-top:10px; font-size:14px;'>En Linux (Debian), ejecuta:</p>";
    echo "<code style='background:#1a1a2e; padding:5px 10px; display:block;'>sudo mysql -u root -e \"ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY ''; FLUSH PRIVILEGES;\"</code>";
    echo "</div>";
}

echo "</div></body></html>";
?>
